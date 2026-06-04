<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use App\Support\ApiErrorCode;
use App\Support\ApiValidationRules;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function registerPlayer(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => ['required', 'string', 'max:255'],
            'email' => [...ApiValidationRules::email(), 'max:255'],
            'phone' => ApiValidationRules::phone(),
            'password' => ['required', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $user = User::create([
            'name' => $request->string('full_name')->toString(),
            'email' => strtolower($request->string('email')->toString()),
            'phone' => $request->string('phone')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'role' => 'player',
            'status' => 'otp_pending',
            'otp_verified' => false,
        ]);

        $plainAccessToken = bin2hex(random_bytes(32));
        $plainRefreshToken = bin2hex(random_bytes(32));

        $user->api_access_token = hash('sha256', $plainAccessToken);
        $user->api_refresh_token = hash('sha256', $plainRefreshToken);
        $user->save();

        $this->assignRoleIfPresent($user, 'player');
        $this->createOtp($user->email, 'registration');

        return response()->json([
            'success' => true,
            'message' => 'Player registered successfully. OTP sent to email.',
            'data' => [
                'access_token' => $plainAccessToken,
                'refresh_token' => $plainRefreshToken,
                'user' => [
                    'id' => $user->id,
                    'role' => $user->role,
                    'email' => $user->email,
                    'status' => $user->status,
                    'otp_verified' => (bool) $user->otp_verified,
                    'profile_completed' => $this->isPlayerProfileCompleted($user),
                ],
            ],
        ], 201);
    }

    public function registerClub(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'club_name' => ['required', 'string', 'max:255'],
            'owner_manager_name' => ['required', 'string', 'max:255'],
            'email' => [...ApiValidationRules::email(), 'max:255'],
            'phone' => ApiValidationRules::phone(),
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'number_of_courts' => ApiValidationRules::numberOfCourts(),
            'working_hours' => ['required', 'string', 'max:100'],
            'password' => ['required', Password::min(8)],
            'club_logo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $logoPath = null;
        if ($request->hasFile('club_logo')) {
            $logoPath = $request->file('club_logo')->store('club-logos', 'public');
        }

        $user = User::create([
            'name' => $request->string('owner_manager_name')->toString(),
            'email' => strtolower($request->string('email')->toString()),
            'phone' => $request->string('phone')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'role' => 'club',
            'status' => 'otp_pending',
            'otp_verified' => false,
            'club_name' => $request->string('club_name')->toString(),
            'owner_manager_name' => $request->string('owner_manager_name')->toString(),
            'address' => $request->string('address')->toString(),
            'city' => $request->string('city')->toString(),
            'number_of_courts' => $request->integer('number_of_courts'),
            'working_hours' => $request->string('working_hours')->toString(),
            'club_logo' => $logoPath,
            'facilities' => $request->input('facilities', []),
        ]);

        $this->assignRoleIfPresent($user, 'club');
        $this->createOtp($user->email, 'registration');

        return response()->json([
            'success' => true,
            'message' => 'Club registered successfully. OTP sent to email.',
            'data' => [
                'user_id' => $user->id,
                'role' => 'club',
                'email' => $user->email,
                'status' => $user->status,
                'otp_verified' => false,
            ],
        ], 201);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'otp' => ApiValidationRules::otp(),
            'purpose' => ['required', 'in:registration,forgot_password'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $email = strtolower($request->string('email')->toString());
        $otpRecord = DB::table('auth_otps')
            ->where('email', $email)
            ->where('purpose', $request->string('purpose')->toString())
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otpRecord || ! Hash::check($request->string('otp')->toString(), $otpRecord->otp_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
                'error_code' => 'INVALID_OTP',
            ], 422);
        }

        DB::table('auth_otps')->where('id', $otpRecord->id)->update(['verified_at' => now()]);
        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found with this email.',
                'error_code' => 'USER_NOT_FOUND',
            ], 404);
        }

        if ($request->string('purpose')->toString() === 'registration') {
            $user->otp_verified = true;

            if ($user->role === 'player') {
                $user->status = 'profile_incomplete';
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' => 'OTP verified successfully.',
                    'data' => [
                        'user_id' => $user->id,
                        'role' => 'player',
                        'status' => $user->status,
                        'otp_verified' => true,
                    ],
                ]);
            }

            $user->status = 'pending';
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'OTP verified successfully. Club profile is pending admin approval.',
                'data' => [
                    'user_id' => $user->id,
                    'role' => 'club',
                    'status' => $user->status,
                    'otp_verified' => true,
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully.',
        ]);
    }

    public function resendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'purpose' => ['required', 'in:registration,forgot_password'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $email = strtolower($request->string('email')->toString());
        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found with this email.',
                'error_code' => 'USER_NOT_FOUND',
            ], 404);
        }

        $this->createOtp($email, $request->string('purpose')->toString());

        return response()->json([
            'success' => true,
            'message' => 'OTP resent successfully. Please check your email.',
        ]);
    }

    public function completePlayerProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        if ($user->role !== 'player') {
            return response()->json([
                'success' => false,
                'message' => 'Only players can complete this profile.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'profile_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp'],
            'dob' => ApiValidationRules::dob(),
            'gender' => ApiValidationRules::gender(),
            'city' => ['required', 'string', 'max:100'],
            'playing_level' => ApiValidationRules::playingLevel(),
            'primary_hand' => ApiValidationRules::primaryHand(),
            'bio' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        if ($request->hasFile('profile_image')) {
            $user->profile_image = $request->file('profile_image')->store('player-profiles', 'public');
        }

        $user->dob = $request->date('dob');
        $user->gender = $request->string('gender')->toString();
        $user->city = $request->string('city')->toString();
        $user->playing_level = $request->string('playing_level')->toString();
        $user->primary_hand = $request->string('primary_hand')->toString();
        $user->bio = $request->string('bio')->toString();
        $user->status = 'active';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profile completed successfully.',
            'data' => [
                'user_id' => $user->id,
                'role' => 'player',
                'status' => 'active',
                'profile_completed' => true,
            ],
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'role' => ['required', 'in:player,club'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $user = User::where('email', strtolower($request->string('email')->toString()))
            ->where('role', $request->string('role')->toString())
            ->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials.',
                'error_code' => 'INVALID_CREDENTIALS',
            ], 401);
        }

        if (! $user->otp_verified) {
            return response()->json([
                'success' => false,
                'message' => 'Please verify your email OTP before login.',
                'error_code' => 'OTP_NOT_VERIFIED',
            ], 403);
        }

        if ($user->status === 'suspended') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been suspended. Please contact support.',
                'error_code' => 'ACCOUNT_SUSPENDED',
            ], 403);
        }

        if ($user->role === 'club') {
            if ($user->status === 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your club profile is pending admin approval.',
                    'error_code' => 'CLUB_PENDING_APPROVAL',
                ], 403);
            }

            if ($user->status === 'rejected') {
                return response()->json([
                    'success' => false,
                    'message' => 'Your club profile has been rejected by admin.',
                    'error_code' => 'CLUB_REJECTED',
                ], 403);
            }
        }

        $plainAccessToken = bin2hex(random_bytes(32));
        $plainRefreshToken = bin2hex(random_bytes(32));

        $user->api_access_token = hash('sha256', $plainAccessToken);
        $user->api_refresh_token = hash('sha256', $plainRefreshToken);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data' => [
                'access_token' => $plainAccessToken,
                'refresh_token' => $plainRefreshToken,
                'user' => [
                    'id' => $user->id,
                    'role' => $user->role,
                    'email' => $user->email,
                    'status' => $user->status,
                    'otp_verified' => (bool) $user->otp_verified,
                    'profile_completed' => $this->isPlayerProfileCompleted($user),
                ],
            ],
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $email = strtolower($request->string('email')->toString());
        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found with this email.',
                'error_code' => 'USER_NOT_FOUND',
            ], 404);
        }

        $this->createOtp($email, 'forgot_password');

        return response()->json([
            'success' => true,
            'message' => 'OTP sent to your email for password reset.',
            'data' => [
                'email' => $email,
            ],
        ]);
    }

    public function verifyForgotPasswordOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'otp' => ApiValidationRules::otp(),
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $email = strtolower($request->string('email')->toString());
        $otpRecord = DB::table('auth_otps')
            ->where('email', $email)
            ->where('purpose', 'forgot_password')
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otpRecord || ! Hash::check($request->string('otp')->toString(), $otpRecord->otp_hash)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
                'error_code' => 'INVALID_OTP',
            ], 422);
        }

        DB::table('auth_otps')->where('id', $otpRecord->id)->update(['verified_at' => now()]);
        $plainResetToken = bin2hex(random_bytes(24));

        DB::table('password_reset_otp_tokens')->insert([
            'email' => $email,
            'token_hash' => hash('sha256', $plainResetToken),
            'expires_at' => now()->addMinutes(15),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully. You can now reset your password.',
            'data' => [
                'reset_token' => $plainResetToken,
            ],
        ]);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'reset_token' => ['required', 'string'],
            'new_password' => ['required', Password::min(8)],
            'confirm_password' => ['required', 'same:new_password'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        $tokenHash = hash('sha256', $request->string('reset_token')->toString());
        $tokenRecord = DB::table('password_reset_otp_tokens')
            ->where('token_hash', $tokenHash)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $tokenRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset token.',
                'error_code' => 'INVALID_RESET_TOKEN',
            ], 422);
        }

        $user = User::where('email', $tokenRecord->email)->first();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found with this email.',
                'error_code' => 'USER_NOT_FOUND',
            ], 404);
        }

        $user->password = Hash::make($request->string('new_password')->toString());
        $user->save();

        DB::table('password_reset_otp_tokens')->where('id', $tokenRecord->id)->update(['used_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully. Please login with your new password.',
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Token is missing or invalid.',
                'error_code' => ApiErrorCode::UNAUTHORIZED,
                'errors' => new \stdClass(),
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'current_password' => ['required', 'string'],
            'new_password' => ['required', Password::min(8), 'different:current_password'],
            'confirm_password' => ['required', 'same:new_password'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors()->toArray());
        }

        if (! Hash::check($request->string('current_password')->toString(), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect.',
                'error_code' => ApiErrorCode::INVALID_CREDENTIALS,
            ], 422);
        }

        $user->password = Hash::make($request->string('new_password')->toString());
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully.',
        ]);
    }

    public function deleteAccount(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Token is missing or invalid.',
                'error_code' => ApiErrorCode::UNAUTHORIZED,
                'errors' => new \stdClass(),
            ], 401);
        }

        $filesToDelete = array_values(array_filter([
            $user->profile_image,
            $user->club_logo,
        ]));

        DB::transaction(function () use ($user) {
            DB::table('court_time_slots')
                ->whereIn('booking_id', function ($query) use ($user) {
                    $query->select('id')
                        ->from('bookings')
                        ->where('player_id', $user->id);
                })
                ->update([
                    'status' => 'available',
                    'booking_id' => null,
                    'updated_at' => now(),
                ]);

            DB::table('auth_otps')->where('email', $user->email)->delete();
            DB::table('password_reset_otp_tokens')->where('email', $user->email)->delete();

            $user->roles()->detach();
            $user->delete();
        });

        foreach ($filesToDelete as $path) {
            if (! str_starts_with($path, 'http://') && ! str_starts_with($path, 'https://')) {
                Storage::disk('public')->delete($path);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Account deleted successfully.',
        ]);
    }

    private function createOtp(string $email, string $purpose): void
    {
        $otp = (string) random_int(100000, 999999);

        // Store OTP in database
        DB::table('auth_otps')->insert([
            'email' => $email,
            'purpose' => $purpose,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get user name if exists
        $user = User::where('email', $email)->first();
        $userName = $user ? $user->name : 'User';

        // Send OTP email
        try {
            Mail::to($email)->send(
                new OtpMail($otp, $purpose, $userName)
            );

            logger()->info('Squash Pro OTP sent successfully', [
                'email' => $email,
                'purpose' => $purpose,
            ]);
        } catch (\Exception $e) {
            logger()->error('Failed to send OTP email', [
                'email' => $email,
                'purpose' => $purpose,
                'error' => $e->getMessage(),
            ]);

            // Log OTP for development (remove in production)
            if (config('app.env') !== 'production') {
                logger()->info('OTP generated (email failed)', [
                    'email' => $email,
                    'otp' => $otp,
                ]);
            }
        }
    }

    private function assignRoleIfPresent(User $user, string $role): void
    {
        if (Role::where('name', $role)->exists()) {
            $user->assignRole($role);
        }
    }

    private function isPlayerProfileCompleted(User $user): bool
    {
        if ($user->role !== 'player') {
            return false;
        }

        return $user->status === 'active'
            && filled($user->dob)
            && filled($user->gender)
            && filled($user->city)
            && filled($user->playing_level)
            && filled($user->primary_hand);
    }

    private function validationError(array $errors): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'error_code' => ApiErrorCode::VALIDATION_ERROR,
            'errors' => $errors,
        ], 422);
    }
}
