<?php

namespace Tests\Feature;

use App\Models\Otp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtpVerificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test mobile number is required to send OTP.
     */
    public function test_send_otp_validation_fails_without_mobile_number(): void
    {
        $response = $this->postJson('/api/v1/auth/send-otp', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ])
            ->assertJsonValidationErrors(['mobile_number']);
    }

    /**
     * Test successful send OTP.
     */
    public function test_send_otp_succeeds_with_mobile_number(): void
    {
        $response = $this->postJson('/api/v1/auth/send-otp', [
            'mobile_number' => '+923001234567',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'expires_in',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'OTP sent successfully.',
                'expires_in' => 60,
            ])
            ->assertJsonMissing(['otp']);

        $otpRecord = Otp::where('mobile_number', '+923001234567')->first();
        $this->assertNotNull($otpRecord);
        $this->assertEquals(4, strlen($otpRecord->otp));
        $this->assertFalse($otpRecord->verified);
    }

    /**
     * Test replacing unexpired OTP when sending a new one.
     */
    public function test_send_otp_replaces_unexpired_otp_for_same_mobile_number(): void
    {
        // First OTP request
        $this->postJson('/api/v1/auth/send-otp', [
            'mobile_number' => '+923001234567',
        ]);
        $otp1 = Otp::where('mobile_number', '+923001234567')->value('otp');

        // Check DB count (should be 1)
        $this->assertEquals(1, Otp::count());

        // Second OTP request
        $this->postJson('/api/v1/auth/send-otp', [
            'mobile_number' => '+923001234567',
        ]);
        $otp2 = Otp::where('mobile_number', '+923001234567')->value('otp');

        // DB count should still be 1 (replaced/updated in-place)
        $this->assertEquals(1, Otp::count());

        $this->assertDatabaseHas('otps', [
            'mobile_number' => '+923001234567',
            'otp' => $otp2,
        ]);
    }

    /**
     * Test validation fails for verification without mobile number or otp.
     */
    public function test_verify_otp_validation_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/verify-otp', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed.',
            ])
            ->assertJsonValidationErrors(['mobile_number', 'otp']);
    }

    /**
     * Test verifying correct active OTP succeeds.
     */
    public function test_verify_otp_succeeds_with_correct_otp(): void
    {
        $this->postJson('/api/v1/auth/send-otp', [
            'mobile_number' => '+923001234567',
        ]);
        $otp = Otp::where('mobile_number', '+923001234567')->value('otp');

        $verifyResponse = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '+923001234567',
            'otp' => $otp,
        ]);

        $verifyResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'OTP verified successfully.',
            ]);

        $this->assertDatabaseHas('otps', [
            'mobile_number' => '+923001234567',
            'otp' => $otp,
            'verified' => true,
        ]);
    }

    /**
     * Test verifying incorrect OTP fails.
     */
    public function test_verify_otp_fails_with_incorrect_otp(): void
    {
        $this->postJson('/api/v1/auth/send-otp', [
            'mobile_number' => '+923001234567',
        ]);

        $verifyResponse = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '+923001234567',
            'otp' => '9999', // Incorrect OTP
        ]);

        $verifyResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ]);
    }

    /**
     * Test verification fails for expired OTP.
     */
    public function test_verify_otp_fails_if_otp_is_expired(): void
    {
        $this->postJson('/api/v1/auth/send-otp', [
            'mobile_number' => '+923001234567',
        ]);
        $otp = Otp::where('mobile_number', '+923001234567')->value('otp');

        // Manually update the expires_at timestamp to past in database
        Otp::where('mobile_number', '+923001234567')->update([
            'expires_at' => now()->subSecond(),
        ]);

        $verifyResponse = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '+923001234567',
            'otp' => $otp,
        ]);

        $verifyResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ]);
    }

    /**
     * Test OTP cannot be verified twice.
     */
    public function test_otp_cannot_be_reused(): void
    {
        $this->postJson('/api/v1/auth/send-otp', [
            'mobile_number' => '+923001234567',
        ]);
        $otp = Otp::where('mobile_number', '+923001234567')->value('otp');

        // First verification (success)
        $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '+923001234567',
            'otp' => $otp,
        ])->assertStatus(200);

        // Second verification (should fail since it is already verified)
        $verifyResponse = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '+923001234567',
            'otp' => $otp,
        ]);

        $verifyResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid or expired OTP.',
            ]);
    }

    /**
     * Test resending OTP invalidates previous OTP.
     */
    public function test_resend_otp_invalidates_previous_otp_and_sends_new_one(): void
    {
        // First OTP request
        $this->postJson('/api/v1/auth/send-otp', [
            'mobile_number' => '+923001234567',
        ]);
        $otp1 = Otp::where('mobile_number', '+923001234567')->value('otp');

        // Resend OTP request
        $response2 = $this->postJson('/api/v1/auth/resend-otp', [
            'mobile_number' => '+923001234567',
        ]);
        $otp2 = Otp::where('mobile_number', '+923001234567')->value('otp');

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'OTP resent successfully.',
                'expires_in' => 60,
            ])
            ->assertJsonMissing(['otp']);

        $this->assertNotEquals($otp1, $otp2);

        // Try verifying with the old OTP (should fail)
        $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '+923001234567',
            'otp' => $otp1,
        ])->assertStatus(422);

        // Try verifying with the new OTP (should succeed)
        $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '+923001234567',
            'otp' => $otp2,
        ])->assertStatus(200);
    }

    /**
     * Test verification fails if user is suspended.
     */
    public function test_verify_otp_fails_if_user_is_suspended(): void
    {
        $user = \App\Models\User::create([
            'phone' => '+923009998888',
            'status' => \App\Models\User::STATUS_SUSPENDED,
            'role' => 'user',
        ]);

        $this->postJson('/api/v1/auth/send-otp', [
            'mobile_number' => '+923009998888',
        ]);
        $otp = Otp::where('mobile_number', '+923009998888')->value('otp');

        $verifyResponse = $this->postJson('/api/v1/auth/verify-otp', [
            'mobile_number' => '+923009998888',
            'otp' => $otp,
        ]);

        $verifyResponse->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Your account has been suspended. Please contact support.',
            ]);
    }
}
