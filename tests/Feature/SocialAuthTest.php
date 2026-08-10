<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SocialAccount;
use App\Models\SocialPage;
use App\Models\InstagramAccount;
use App\Services\GoogleAuthService;
use App\Services\MetaGraphService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Tests\TestCase;

class SocialAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (class_exists(\Spatie\Permission\Models\Role::class)) {
            \Spatie\Permission\Models\Role::create(['name' => 'player']);
        }
    }

    /**
     * Test configuration credentials retrieval.
     */
    public function test_config_endpoints()
    {
        config(['services.google.client_id' => 'mock-google-client-id']);
        config(['services.facebook.client_id' => 'mock-facebook-client-id']);
        config(['services.facebook.redirect' => 'http://redirect.uri']);
        config(['meta.graph_version' => 'v20.0']);
        config(['meta.base_url' => 'https://graph.facebook.com']);

        $this->getJson('/api/v1/config/google')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'client_id' => 'mock-google-client-id',
            ]);

        $this->getJson('/api/v1/config/meta')
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'client_id' => 'mock-facebook-client-id',
                'redirect_uri' => 'http://redirect.uri',
                'graph_version' => 'v20.0',
            ]);
    }

    /**
     * Test successful login with Google.
     */
    public function test_google_login_success()
    {
        // Mock Google Verification Service
        $this->mock(GoogleAuthService::class, function ($mock) {
            $mock->shouldReceive('verifyToken')
                ->once()
                ->with('valid_token')
                ->andReturn([
                    'provider_user_id' => 'google-uid-123',
                    'name' => 'Adnan Malik',
                    'email' => 'adnan@example.com',
                    'profile_picture' => 'http://example.com/avatar.jpg',
                ]);
        });

        $response = $this->postJson('/api/v1/auth/google/login', [
            'id_token' => 'valid_token',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'token',
                'user' => ['id', 'email', 'name'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'adnan@example.com',
        ]);

        $this->assertDatabaseHas('social_accounts', [
            'provider' => 'google',
            'provider_user_id' => 'google-uid-123',
        ]);
    }

    /**
     * Test Facebook connection and callback redirect.
     */
    public function test_facebook_connect_redirect()
    {
        $user = User::factory()->create();
        $plainToken = bin2hex(random_bytes(32));
        $user->api_access_token = hash('sha256', $plainToken);
        $user->save();

        // Check if redirect contains facebook Oauth components
        $response = $this->withHeader('Authorization', 'Bearer ' . $plainToken)
            ->get('/api/v1/social/facebook/connect');

        $response->assertStatus(302);
        $this->assertStringContainsString('facebook.com', $response->headers->get('Location'));
    }

    /**
     * Test Facebook Callback with stateless response, pages sync, and Instagram auto-connect.
     */
    public function test_facebook_callback_and_sync()
    {
        $user = User::factory()->create();
        $state = Crypt::encryptString(json_encode(['user_id' => $user->id]));

        // Mock Socialite User Object
        $socialiteUser = \Mockery::mock('Laravel\Socialite\Two\User');
        $socialiteUser->shouldReceive('getId')->andReturn('fb-uid-555');
        $socialiteUser->shouldReceive('getName')->andReturn('Zaid Malik');
        $socialiteUser->shouldReceive('getEmail')->andReturn('zaid@example.com');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('http://facebook.com/avatar.jpg');
        $socialiteUser->token = 'fb-access-token-xyz';
        $socialiteUser->refreshToken = 'fb-refresh-token';
        $socialiteUser->expiresIn = 3600;

        // Mock Socialite Driver
        Socialite::shouldReceive('driver')
            ->with('facebook')
            ->andReturn(self::mockDriver($socialiteUser));

        // Mock Meta Graph Service
        $this->mock(MetaGraphService::class, function ($mock) {
            $mock->shouldReceive('fetchPages')
                ->once()
                ->with('fb-access-token-xyz')
                ->andReturn([
                    [
                        'page_id' => 'page-id-999',
                        'page_name' => 'Burger King Pakistan',
                        'page_access_token' => 'page-token-abc',
                        'category' => 'Restaurant',
                    ]
                ]);

            $mock->shouldReceive('fetchLinkedInstagramAccount')
                ->once()
                ->with('page-id-999', 'page-token-abc')
                ->andReturn([
                    'page_id' => 'page-id-999',
                    'instagram_business_id' => 'ig-id-888',
                    'username' => 'burgerking_pk',
                    'profile_picture' => 'http://instagram.com/bk.jpg',
                ]);
        });

        // Trigger callback route
        $response = $this->get('/api/v1/social/facebook/callback?state=' . urlencode($state));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Facebook connected successfully.',
            ]);

        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_user_id' => 'fb-uid-555',
        ]);

        $this->assertDatabaseHas('social_pages', [
            'social_account_id' => SocialAccount::first()->id,
            'page_id' => 'page-id-999',
            'page_name' => 'Burger King Pakistan',
        ]);
    }

    /**
     * Test Instagram Callback and sync.
     */
    public function test_instagram_callback_and_sync()
    {
        $user = User::factory()->create();
        $state = Crypt::encryptString(json_encode(['user_id' => $user->id, 'platform' => 'instagram']));

        // Mock Socialite User Object
        $socialiteUser = \Mockery::mock('Laravel\Socialite\Two\User');
        $socialiteUser->shouldReceive('getId')->andReturn('fb-uid-777');
        $socialiteUser->shouldReceive('getName')->andReturn('IG User');
        $socialiteUser->shouldReceive('getEmail')->andReturn('ig@example.com');
        $socialiteUser->shouldReceive('getAvatar')->andReturn('http://facebook.com/avatar.jpg');
        $socialiteUser->token = 'fb-access-token-ig';
        $socialiteUser->refreshToken = 'fb-refresh-token';
        $socialiteUser->expiresIn = 3600;

        // Mock Socialite Driver
        Socialite::shouldReceive('driver')
            ->with('facebook')
            ->andReturn(self::mockDriver($socialiteUser));

        // Mock Meta Graph Service
        $this->mock(MetaGraphService::class, function ($mock) {
            $mock->shouldReceive('fetchPages')
                ->once()
                ->with('fb-access-token-ig')
                ->andReturn([
                    [
                        'page_id' => 'page-id-777',
                        'page_name' => 'Burger King Pakistan',
                        'page_access_token' => 'page-token-abc',
                        'category' => 'Restaurant',
                    ]
                ]);

            $mock->shouldReceive('fetchLinkedInstagramAccount')
                ->once()
                ->with('page-id-777', 'page-token-abc')
                ->andReturn([
                    'page_id' => 'page-id-777',
                    'instagram_business_id' => 'ig-id-888',
                    'username' => 'burgerking_pk',
                    'profile_picture' => 'http://instagram.com/bk.jpg',
                ]);
        });

        // Trigger Instagram callback route
        $response = $this->get('/api/v1/social/instagram/callback?state=' . urlencode($state));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Instagram connected successfully.',
            ]);

        $this->assertDatabaseHas('instagram_accounts', [
            'instagram_business_id' => 'ig-id-888',
            'username' => 'burgerking_pk',
        ]);
    }

    /**
     * Test connection status API.
     */
    public function test_social_accounts_status()
    {
        $user = User::factory()->create();
        $plainToken = bin2hex(random_bytes(32));
        $user->api_access_token = hash('sha256', $plainToken);
        $user->save();

        // Create a Facebook Social Account
        SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_user_id' => 'fb-123',
            'name' => 'Test FB',
            'access_token' => 'token',
            'connected_at' => now(),
        ]);

        // Create an Instagram Account
        InstagramAccount::create([
            'user_id' => $user->id,
            'social_account_id' => SocialAccount::first()->id,
            'page_id' => 'page-1',
            'instagram_business_id' => 'ig-1',
            'username' => 'testig',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer ' . $plainToken)
            ->getJson('/api/v1/social/accounts');

        $response->assertStatus(200)
            ->assertJson([
                'google' => false,
                'facebook' => true,
                'instagram' => true,
            ]);
    }

    /**
     * Test disconnect Facebook and Instagram accounts.
     */
    public function test_disconnect_social_accounts()
    {
        $user = User::factory()->create();
        $plainToken = bin2hex(random_bytes(32));
        $user->api_access_token = hash('sha256', $plainToken);
        $user->save();

        $account = SocialAccount::create([
            'user_id' => $user->id,
            'provider' => 'facebook',
            'provider_user_id' => 'fb-123',
            'name' => 'Test FB',
            'access_token' => 'token',
            'connected_at' => now(),
        ]);

        SocialPage::create([
            'user_id' => $user->id,
            'social_account_id' => $account->id,
            'page_id' => 'page-1',
            'page_name' => 'Page 1',
            'page_access_token' => 'token',
        ]);

        InstagramAccount::create([
            'user_id' => $user->id,
            'social_account_id' => $account->id,
            'page_id' => 'page-1',
            'instagram_business_id' => 'ig-1',
            'username' => 'testig',
        ]);

        // First disconnect Instagram
        $response = $this->withHeader('Authorization', 'Bearer ' . $plainToken)
            ->deleteJson('/api/v1/social/instagram/disconnect');

        $response->assertStatus(200);
        $this->assertDatabaseMissing('instagram_accounts', ['username' => 'testig']);
        $this->assertDatabaseHas('social_accounts', ['id' => $account->id]); // Facebook account should remain

        // Disconnect Facebook
        $response = $this->withHeader('Authorization', 'Bearer ' . $plainToken)
            ->deleteJson('/api/v1/social/facebook/disconnect');

        $response->assertStatus(200);
        $this->assertDatabaseMissing('social_accounts', ['id' => $account->id]);
        $this->assertDatabaseMissing('social_pages', ['page_id' => 'page-1']);
    }

    /**
     * Mock Socialite Driver.
     */
    private static function mockDriver($user)
    {
        $driver = \Mockery::mock('Laravel\Socialite\Two\AbstractProvider');
        $driver->shouldReceive('stateless')->andReturn($driver);
        $driver->shouldReceive('user')->andReturn($user);
        $driver->shouldReceive('scopes')->andReturn($driver);
        $driver->shouldReceive('with')->andReturn($driver);
        $driver->shouldReceive('redirect')->andReturn(redirect('http://facebook.com/oauth'));
        return $driver;
    }
}
