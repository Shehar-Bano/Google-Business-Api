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
        $response = $this->postJson('/api/send-otp', []);

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
        $response = $this->postJson('/api/send-otp', [
            'mobile_number' => '+923001234567',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'otp',
                'expires_in',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'OTP sent successfully.',
                'expires_in' => 30,
            ]);

        $otpCode = $response->json('otp');
        $this->assertEquals(4, strlen($otpCode));

        $this->assertDatabaseHas('otps', [
            'mobile_number' => '+923001234567',
            'otp' => $otpCode,
            'verified' => false,
        ]);
    }

    /**
     * Test replacing unexpired OTP when sending a new one.
     */
    public function test_send_otp_replaces_unexpired_otp_for_same_mobile_number(): void
    {
        // First OTP request
        $response1 = $this->postJson('/api/send-otp', [
            'mobile_number' => '+923001234567',
        ]);
        $otp1 = $response1->json('otp');

        // Check DB count (should be 1)
        $this->assertEquals(1, Otp::count());

        // Second OTP request within 30 seconds
        $response2 = $this->postJson('/api/send-otp', [
            'mobile_number' => '+923001234567',
        ]);
        $otp2 = $response2->json('otp');

        // DB count should still be 1 (replaced/updated in-place)
        $this->assertEquals(1, Otp::count());

        $this->assertDatabaseHas('otps', [
            'mobile_number' => '+923001234567',
            'otp' => $otp2,
        ]);

        $this->assertDatabaseMissing('otps', [
            'mobile_number' => '+923001234567',
            'otp' => $otp1,
        ]);
    }

    /**
     * Test validation fails for verification without mobile number or otp.
     */
    public function test_verify_otp_validation_fails_without_required_fields(): void
    {
        $response = $this->postJson('/api/verify-otp', []);

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
        $sendResponse = $this->postJson('/api/send-otp', [
            'mobile_number' => '+923001234567',
        ]);
        $otp = $sendResponse->json('otp');

        $verifyResponse = $this->postJson('/api/verify-otp', [
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
        $this->postJson('/api/send-otp', [
            'mobile_number' => '+923001234567',
        ]);

        $verifyResponse = $this->postJson('/api/verify-otp', [
            'mobile_number' => '+923001234567',
            'otp' => '9999', // Incorrect OTP
        ]);

        $verifyResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid OTP.',
            ]);
    }

    /**
     * Test verification fails for expired OTP.
     */
    public function test_verify_otp_fails_if_otp_is_expired(): void
    {
        $sendResponse = $this->postJson('/api/send-otp', [
            'mobile_number' => '+923001234567',
        ]);
        $otp = $sendResponse->json('otp');

        // Manually update the expires_at timestamp to past in database
        Otp::where('mobile_number', '+923001234567')->update([
            'expires_at' => now()->subSecond(),
        ]);

        $verifyResponse = $this->postJson('/api/verify-otp', [
            'mobile_number' => '+923001234567',
            'otp' => $otp,
        ]);

        $verifyResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'OTP has expired.',
            ]);
    }

    /**
     * Test OTP cannot be verified twice.
     */
    public function test_otp_cannot_be_reused(): void
    {
        $sendResponse = $this->postJson('/api/send-otp', [
            'mobile_number' => '+923001234567',
        ]);
        $otp = $sendResponse->json('otp');

        // First verification (success)
        $this->postJson('/api/verify-otp', [
            'mobile_number' => '+923001234567',
            'otp' => $otp,
        ])->assertStatus(200);

        // Second verification (should fail since it is already verified)
        $verifyResponse = $this->postJson('/api/verify-otp', [
            'mobile_number' => '+923001234567',
            'otp' => $otp,
        ]);

        $verifyResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid OTP.',
            ]);
    }

    /**
     * Test resending OTP invalidates previous OTP.
     */
    public function test_resend_otp_invalidates_previous_otp_and_sends_new_one(): void
    {
        // First OTP request
        $response1 = $this->postJson('/api/send-otp', [
            'mobile_number' => '+923001234567',
        ]);
        $otp1 = $response1->json('otp');

        // Resend OTP request
        $response2 = $this->postJson('/api/resend-otp', [
            'mobile_number' => '+923001234567',
        ]);
        $otp2 = $response2->json('otp');

        $response2->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'OTP resent successfully.',
                'expires_in' => 30,
            ]);

        $this->assertNotEquals($otp1, $otp2);

        // Try verifying with the old OTP (should fail)
        $this->postJson('/api/verify-otp', [
            'mobile_number' => '+923001234567',
            'otp' => $otp1,
        ])->assertStatus(422);

        // Try verifying with the new OTP (should succeed)
        $this->postJson('/api/verify-otp', [
            'mobile_number' => '+923001234567',
            'otp' => $otp2,
        ])->assertStatus(200);
    }
}
