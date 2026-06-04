<?php

namespace Tests\Feature\Api\V1;

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_player_registration_and_login_flow(): void
    {
        $registerResponse = $this->postJson('/api/v1/auth/register/player', [
            'full_name' => 'Ali Khan',
            'email' => 'ali@example.com',
            'phone' => '+923001234567',
            'password' => 'Password@123',
        ]);

        $registerResponse->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'otp_pending');

        $loginBeforeOtp = $this->postJson('/api/v1/auth/login', [
            'email' => 'ali@example.com',
            'password' => 'Password@123',
            'role' => 'player',
        ]);

        $loginBeforeOtp->assertStatus(403)
            ->assertJsonPath('error_code', 'OTP_NOT_VERIFIED');

        DB::table('auth_otps')
            ->where('email', 'ali@example.com')
            ->update(['otp_hash' => Hash::make('123456')]);

        $verifyOtpResponse = $this->postJson('/api/v1/auth/verify-otp', [
            'email' => 'ali@example.com',
            'otp' => '123456',
            'purpose' => 'registration',
        ]);

        $verifyOtpResponse->assertOk()
            ->assertJsonPath('data.status', 'profile_incomplete');
    }

    public function test_forgot_password_reset_flow(): void
    {
        $this->postJson('/api/v1/auth/register/player', [
            'full_name' => 'Ali Khan',
            'email' => 'ali@example.com',
            'phone' => '+923001234567',
            'password' => 'Password@123',
        ])->assertCreated();

        $forgot = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'ali@example.com',
        ]);

        $forgot->assertOk()->assertJsonPath('success', true);

        DB::table('auth_otps')
            ->where('email', 'ali@example.com')
            ->where('purpose', 'forgot_password')
            ->update(['otp_hash' => Hash::make('654321')]);

        $verify = $this->postJson('/api/v1/auth/forgot-password/verify-otp', [
            'email' => 'ali@example.com',
            'otp' => '654321',
        ]);

        $verify->assertOk();
        $resetToken = $verify->json('data.reset_token');

        $reset = $this->postJson('/api/v1/auth/reset-password', [
            'reset_token' => $resetToken,
            'new_password' => 'NewPassword@123',
            'confirm_password' => 'NewPassword@123',
        ]);

        $reset->assertOk()->assertJsonPath('success', true);
    }
}
