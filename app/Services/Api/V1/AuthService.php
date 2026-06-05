<?php

namespace App\Services\Api\V1;

use App\Http\Requests\Api\V1\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Auth\CompletePlayerProfileRequest;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterClubRequest;
use App\Http\Requests\Api\V1\Auth\RegisterPlayerRequest;
use App\Http\Requests\Api\V1\Auth\ResendOtpRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Http\Requests\Api\V1\Auth\VerifyForgotPasswordOtpRequest;
use App\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Mail\OtpMail;
use App\Models\User;
use App\Support\ApiErrorCode;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

class AuthService
{
    public function registerPlayer(RegisterPlayerRequest $request): array
    {
        $user = User::create([
            'name' => $request->string('full_name')->toString(),
            'email' => strtolower($request->string('email')->toString()),
            'phone' => $request->string('phone')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
            'role' => 'player',
            'status' => 'otp_pending',
            'otp_verified' => false,
        ]);

        ['access_token' => $accessToken, 'refresh_token' => $refreshToken] = $this->issueTokens($user);
        $this->assignRoleIfPresent($user, 'player');
        $this->createOtp($user->email, 'registration');

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    public function registerClub(RegisterClubRequest $request): array
    {
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

        return compact('user');
    }

    public function verifyOtp(VerifyOtpRequest $request): array
    {
        $email = strtolower($request->string('email')->toString());
        $otpRecord = DB::table('auth_otps')
            ->where('email', $email)
            ->where('purpose', $request->string('purpose')->toString())
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otpRecord || ! Hash::check($request->string('otp')->toString(), $otpRecord->otp_hash)) {
            $this->throwApiError('Invalid or expired OTP.', ApiErrorCode::INVALID_OTP, 422);
        }

        DB::table('auth_otps')->where('id', $otpRecord->id)->update(['verified_at' => now()]);
        $user = User::where('email', $email)->firstOrFail();

        if ($request->string('purpose')->toString() === 'registration') {
            $user->otp_verified = true;

            if ($user->role === 'player') {
                $user->status = 'profile_incomplete';
            } else {
                $user->status = 'pending';
            }

            $user->save();
        }

        return compact('user');
    }

    public function resendOtp(ResendOtpRequest $request): void
    {
        $email = strtolower($request->string('email')->toString());

        if (! User::where('email', $email)->exists()) {
            $this->throwApiError('User not found with this email.', ApiErrorCode::USER_NOT_FOUND, 404);
        }

        $this->createOtp($email, $request->string('purpose')->toString());
    }

    public function completePlayerProfile(User $user, CompletePlayerProfileRequest $request): User
    {
        if ($user->role !== 'player') {
            $this->throwApiError('Only players can complete this profile.', ApiErrorCode::FORBIDDEN, 403);
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

        return $user;
    }

    public function login(LoginRequest $request): array
    {
        $user = User::where('email', strtolower($request->string('email')->toString()))
            ->where('role', $request->string('role')->toString())
            ->first();

        if (! $user || ! Hash::check($request->string('password')->toString(), $user->password)) {
            $this->throwApiError('Invalid credentials.', ApiErrorCode::INVALID_CREDENTIALS, 401);
        }

        if (! $user->otp_verified) {
            $this->throwApiError('Please verify your email OTP before login.', ApiErrorCode::OTP_NOT_VERIFIED, 403);
        }

        if ($user->status === 'suspended') {
            $this->throwApiError('Your account has been suspended. Please contact support.', ApiErrorCode::ACCOUNT_SUSPENDED, 403);
        }

        if ($user->role === 'club') {
            if ($user->status === 'pending') {
                $this->throwApiError('Your club profile is pending admin approval.', ApiErrorCode::CLUB_PENDING_APPROVAL, 403);
            }

            if ($user->status === 'rejected') {
                $this->throwApiError('Your club profile has been rejected by admin.', ApiErrorCode::CLUB_REJECTED, 403);
            }
        }

        ['access_token' => $accessToken, 'refresh_token' => $refreshToken] = $this->issueTokens($user);

        return [
            'user' => $user,
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
        ];
    }

    public function forgotPassword(ForgotPasswordRequest $request): array
    {
        $email = strtolower($request->string('email')->toString());
        if (! User::where('email', $email)->exists()) {
            $this->throwApiError('User not found with this email.', ApiErrorCode::USER_NOT_FOUND, 404);
        }
        if(! User::where('email', $email)->where('otp_verified', true)->exists()) {
            $this->throwApiError('Email not verified. Please verify your email before requesting password reset.', ApiErrorCode::OTP_NOT_VERIFIED, 403);
        }

        $this->createOtp($email, 'forgot_password');

        return ['email' => $email];
    }

    public function verifyForgotPasswordOtp(VerifyForgotPasswordOtpRequest $request): array
    {
        $email = strtolower($request->string('email')->toString());
        $otpRecord = DB::table('auth_otps')
            ->where('email', $email)
            ->where('purpose', 'forgot_password')
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $otpRecord || ! Hash::check($request->string('otp')->toString(), $otpRecord->otp_hash)) {
            $this->throwApiError('Invalid or expired OTP.', ApiErrorCode::INVALID_OTP, 422);
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

        return ['reset_token' => $plainResetToken];
    }

    public function resetPassword(ResetPasswordRequest $request): void
    {
        $tokenHash = hash('sha256', $request->string('reset_token')->toString());
        $tokenRecord = DB::table('password_reset_otp_tokens')
            ->where('token_hash', $tokenHash)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $tokenRecord) {
            $this->throwApiError('Invalid or expired reset token.', ApiErrorCode::RESET_TOKEN_INVALID, 422);
        }

        $user = User::where('email', $tokenRecord->email)->firstOrFail();
        $user->password = Hash::make($request->string('new_password')->toString());
        $user->save();

        DB::table('password_reset_otp_tokens')->where('id', $tokenRecord->id)->update(['used_at' => now()]);
    }

    public function changePassword(User $user, ChangePasswordRequest $request): void
    {
        if (! Hash::check($request->string('current_password')->toString(), $user->password)) {
            $this->throwApiError('Current password is incorrect.', ApiErrorCode::INVALID_CREDENTIALS, 422);
        }

        $user->password = Hash::make($request->string('new_password')->toString());
        $user->save();
    }

    public function logout(User $user): void
    {
        $user->api_access_token = null;
        $user->api_refresh_token = null;
        $user->save();
    }

    public function deleteAccount(User $user): void
    {
        $filesToDelete = array_values(array_filter([
            $user->profile_image,
            $user->club_logo,
        ]));

        DB::transaction(function () use ($user) {
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
    }

    private function createOtp(string $email, string $purpose): void
    {
        $otp = (string) random_int(100000, 999999);

        DB::table('auth_otps')->insert([
            'email' => $email,
            'purpose' => $purpose,
            'otp_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::where('email', $email)->first();
        $userName = $user ? $user->name : 'User';

        try {
            Mail::to($email)->send(new OtpMail($otp, $purpose, $userName));
        } catch (\Throwable) {
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

    private function issueTokens(User $user): array
    {
        $plainAccessToken = bin2hex(random_bytes(32));
        $plainRefreshToken = bin2hex(random_bytes(32));

        $user->api_access_token = hash('sha256', $plainAccessToken);
        $user->api_refresh_token = hash('sha256', $plainRefreshToken);
        $user->save();

        return [
            'access_token' => $plainAccessToken,
            'refresh_token' => $plainRefreshToken,
        ];
    }

    private function throwApiError(string $message, string $errorCode, int $status): never
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
            'errors' => new \stdClass,
        ], $status));
    }
}
