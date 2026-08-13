<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ConnectFacebookPageRequest;
use App\Http\Resources\Api\V1\InstagramAccountResource;
use App\Http\Resources\Api\V1\SocialPageResource;
use App\Services\FacebookService;
use App\Services\InstagramService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

class SocialConnectionController extends Controller
{
    public function __construct(
        protected FacebookService $facebookService,
        protected InstagramService $instagramService
    ) {}

    /**
     * Redirect the user or return redirect URL for Facebook authentication.
     * GET /api/social/facebook/connect
     */
    public function facebookConnect(Request $request)
    {
        $user = $request->user();

        // Securely pass user_id inside state payload to mapping callback
        $state = Crypt::encryptString(json_encode([
            'user_id' => $user->id,
            'platform' => 'facebook',
        ]));

        $targetUrl = Socialite::driver('facebook')
            ->scopes([
                'email',
                'public_profile',
                'pages_show_list',
                'pages_read_engagement',
                'pages_manage_posts',
                'business_management',
            ])
            ->with(['state' => $state])
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        if ($request->wantsJson() || $request->is('api/*') || $request->expectsJson() || $request->header('Accept') === 'application/json' || $request->header('User-Agent')) {
            return response()->json([
                'success' => true,
                'redirect_url' => $targetUrl,
            ]);
        }

        return redirect()->away($targetUrl);
    }

    /**
     * Handle the Facebook authentication callback.
     * GET /api/social/facebook/callback
     */
    public function facebookCallback(Request $request)
    {
        $state = $request->query('state');
        if (! $state) {
            return response()->json([
                'success' => false,
                'message' => 'OAuth state parameter missing.',
            ], 400);
        }

        try {
            $payload = json_decode(Crypt::decryptString($state), true);
            $userId = $payload['user_id'] ?? null;
            if (! $userId) {
                throw new \Exception('Invalid user ID inside state.');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'State decryption failed: '.$e->getMessage(),
            ], 400);
        }

        $user = \App\Models\User::find($userId);
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => "User with ID {$userId} does not exist in the database. Please ensure you are logged in with a valid user.",
            ], 404);
        }

        if ($request->has('error')) {
            Log::warning('Facebook Connect Cancelled or Denied: '.$request->query('error_description'));

            return response()->json([
                'success' => false,
                'message' => 'Connection cancelled: '.$request->query('error_description'),
            ], 400);
        }

        try {
            $fbUser = Socialite::driver('facebook')->stateless()->user();

            $facebookUser = [
                'id' => $fbUser->getId(),
                'name' => $fbUser->getName(),
                'email' => $fbUser->getEmail(),
                'avatar' => $fbUser->getAvatar(),
                'token' => $fbUser->token,
                'refreshToken' => $fbUser->refreshToken,
                'expiresIn' => $fbUser->expiresIn,
            ];

            $socialAccount = $this->facebookService->connectAccount($userId, $facebookUser);

            return response()->json([
                'success' => true,
                'message' => 'Facebook connected successfully.',
                'user' => [
                    'id' => $socialAccount->user_id,
                    'facebook_connected' => true,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Facebook Callback Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Connect Facebook directly using access token (highly used in Flutter mobile SDK logins).
     * POST /api/social/facebook/connect-token
     */
    public function facebookConnectToken(Request $request): JsonResponse
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        $token = $request->input('access_token');
        $userId = $request->user()->id;

        try {
            // Validate and fetch user details from graph API
            $version = config('meta.graph_version', 'v20.0');
            $baseUrl = config('meta.base_url', 'https://graph.facebook.com');
            $url = "{$baseUrl}/{$version}/me?fields=id,name,email,picture&access_token={$token}";

            $response = Http::get($url);
            if (! $response->successful()) {
                Log::error('Direct Facebook token connection error: '.$response->body());

                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired Facebook access token.',
                ], 401);
            }

            $data = $response->json();

            $facebookUser = [
                'id' => $data['id'],
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'avatar' => $data['picture']['data']['url'] ?? null,
                'token' => $token,
                'refreshToken' => null,
                'expiresIn' => null,
            ];

            $socialAccount = $this->facebookService->connectAccount($userId, $facebookUser);
            $pages = $this->facebookService->getPages($userId);

            return response()->json([
                'success' => true,
                'message' => 'Facebook and linked Instagram connected successfully.',
                'account_id' => $socialAccount->id,
                'pages' => SocialPageResource::collection($pages),
            ]);

        } catch (\Exception $e) {
            Log::error('Facebook Token Connection Exception: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Connection failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Connect Google account directly for the authenticated user.
     * POST /api/social/google/connect-token
     */
    public function googleConnectToken(Request $request): JsonResponse
    {
        $user = $request->user();
        $bearerToken = $request->bearerToken();

        $authService = app(\App\Services\AuthService::class);
        $result = $authService->loginWithGoogle(
            $request->input('id_token'),
            $user,
            $bearerToken,
            $request->input('access_token'),
            $request->input('refresh_token'),
            $request->input('expires_in'),
            $request->input('code'),
            $request->input('expires_at') ?? $request->input('token_expires_at')
        );

        if (! $result) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to verify and connect Google account.',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Google account connected successfully.',
            'user' => $result['user'],
            'social_account' => $user->socialAccounts()->where('provider', 'google')->first(),
        ]);
    }

    /**
     * Disconnect Google connection for the authenticated user.
     * DELETE /api/social/google/disconnect
     */
    public function disconnectGoogle(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $deleted = \App\Models\SocialAccount::where('user_id', $userId)->where('provider', 'google')->delete();

        if (! $deleted) {
            return response()->json([
                'success' => false,
                'message' => 'No active Google connection found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Google connection successfully disconnected.',
        ]);
    }

    /**
     * Get social account connection status.
     * GET /api/social/accounts
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'google' => $user->socialAccounts()->where('provider', 'google')->exists(),
            'facebook' => $user->socialAccounts()->where('provider', 'facebook')->exists(),
            'instagram' => $user->socialAccounts()->where('provider', 'instagram')->exists(),
        ]);
    }

    /**
     * Disconnect Facebook connection.
     * DELETE /api/social/facebook/disconnect
     */
    public function disconnectFacebook(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $disconnected = $this->facebookService->disconnect($userId);

        if (! $disconnected) {
            return response()->json([
                'success' => false,
                'message' => 'No active Facebook connection found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Facebook connection successfully disconnected.',
        ]);
    }

    /**
     * Disconnect Instagram connection.
     * DELETE /api/social/instagram/disconnect
     */
    public function disconnectInstagram(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;
        if (! $userId && $request->bearerToken()) {
            $hashed = hash('sha256', $request->bearerToken());
            $userId = \App\Models\User::where('api_access_token', $hashed)->value('id');
        }

        if (! $userId) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 401);
        }

        $disconnected = $this->instagramService->disconnect($userId);

        if (! $disconnected) {
            return response()->json([
                'success' => false,
                'message' => 'No active Instagram connection found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Instagram connection successfully disconnected.',
        ]);
    }

    /**
     * Get Facebook OAuth redirect URL.
     * GET /api/social/facebook/redirect-url
     */
    public function facebookRedirectUrl(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;
        if (! $userId && $request->bearerToken()) {
            $hashed = hash('sha256', $request->bearerToken());
            $userId = \App\Models\User::where('api_access_token', $hashed)->value('id');
        }
        if (! $userId && $request->has('user_id')) {
            $userId = (int) $request->input('user_id');
        }

        $driver = Socialite::driver('facebook')
            ->scopes([
                'email',
                'public_profile',
                'pages_show_list',
                'pages_read_engagement',
                'pages_manage_posts',
                'business_management',
                'instagram_basic',
                'instagram_content_publish',
            ])
            ->stateless();

        if ($userId) {
            $state = Crypt::encryptString(json_encode([
                'user_id' => $userId,
                'platform' => 'facebook',
            ]));
            $driver->with(['state' => $state]);
        }

        $url = $driver->redirect()->getTargetUrl();

        return response()->json([
            'success' => true,
            'redirect_url' => $url,
        ]);
    }

    /**
     * Get Instagram OAuth redirect URL.
     * GET /api/social/instagram/redirect-url
     */
    /**
     * Build Instagram Login OAuth URL using official Instagram Business Login.
     */
    protected function getInstagramDirectUrl(?int $userId = null): string
    {
        $clientId = config('services.instagram.client_id') ?: env('Instagram_app_ID', '1010962951719509');
        $redirectUri = config('services.instagram.redirect') ?: 'https://darkviolet-wallaby-198670.hostingersite.com/public/api/social/instagram/callback';

        $state = Crypt::encryptString(json_encode([
            'user_id' => $userId,
            'platform' => 'instagram',
        ]));

        $params = http_build_query([
            'force_reauth' => 'true',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'instagram_business_basic,instagram_business_manage_messages,instagram_business_manage_comments',
            'state' => $state,
        ]);

        return "https://www.instagram.com/oauth/authorize?{$params}";
    }

    /**
     * Get Instagram OAuth redirect URL.
     * GET /api/social/instagram/redirect-url
     */
    public function instagramRedirectUrl(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;
        if (! $userId && $request->bearerToken()) {
            $hashed = hash('sha256', $request->bearerToken());
            $userId = \App\Models\User::where('api_access_token', $hashed)->value('id');
        }
        if (! $userId && $request->has('user_id')) {
            $userId = (int) $request->input('user_id');
        }

        $url = $this->getInstagramDirectUrl($userId);

        return response()->json([
            'success' => true,
            'redirect_url' => $url,
        ]);
    }

    /**
     * Redirect the user or return redirect URL for Instagram authentication.
     * GET /api/social/instagram/connect
     */
    public function instagramConnect(Request $request)
    {
        $user = $request->user();
        $targetUrl = $this->getInstagramDirectUrl($user?->id);

        if ($request->wantsJson() || $request->is('api/*') || $request->expectsJson() || $request->header('Accept') === 'application/json' || $request->header('User-Agent')) {
            return response()->json([
                'success' => true,
                'redirect_url' => $targetUrl,
            ]);
        }

        return redirect()->away($targetUrl);
    }

    /**
     * Handle the Instagram authentication callback.
     * GET /api/social/instagram/callback
     */
    public function instagramCallback(Request $request): JsonResponse
    {
        $state = $request->query('state');
        if (! $state) {
            return response()->json([
                'success' => false,
                'message' => 'OAuth state parameter missing.',
            ], 400);
        }

        try {
            $payload = json_decode(Crypt::decryptString($state), true);
            $userId = $payload['user_id'] ?? null;
            if (! $userId) {
                throw new \Exception('Invalid user ID inside state.');
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'State decryption failed: '.$e->getMessage(),
            ], 400);
        }

        $user = \App\Models\User::find($userId);
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => "User with ID {$userId} does not exist in the database. Please make sure you are logged in with a valid user.",
            ], 404);
        }

        if ($request->has('error')) {
            Log::warning('Instagram Connect Cancelled or Denied: '.$request->query('error_description'));

            return response()->json([
                'success' => false,
                'message' => 'Connection cancelled: '.$request->query('error_description'),
            ], 400);
        }

        $code = $request->query('code');
        if ($code) {
            $code = rtrim(str_replace('#_', '', $code), '#_');
        }

        try {
            $clientId = config('services.instagram.client_id') ?: config('services.facebook.client_id');
            $clientSecret = config('services.instagram.client_secret') ?: config('services.facebook.client_secret');
            $redirectUri = config('services.instagram.redirect') ?: 'https://darkviolet-wallaby-198670.hostingersite.com/public/api/social/instagram/callback';

            Log::info("Instagram Callback: Processing code for User {$userId}", [
                'clientId' => $clientId,
                'redirectUri' => $redirectUri,
            ]);

            // 1. Direct Instagram OAuth Token Exchange
            $tokenRes = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
                'code' => $code,
            ]);

            // If not successful, retry with the exact current callback URL
            if (! $tokenRes->successful() && $request->url() !== $redirectUri) {
                Log::warning('Instagram Token Exchange first attempt failed, retrying with request URL: '.$request->url());
                $tokenRes = Http::asForm()->post('https://api.instagram.com/oauth/access_token', [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $request->url(),
                    'code' => $code,
                ]);
            }

            Log::info('Instagram Token Exchange Final Response: ', [
                'status' => $tokenRes->status(),
                'body' => $tokenRes->body(),
            ]);

            if ($tokenRes->successful() && ! empty($tokenRes->json('access_token'))) {
                $tokenData = $tokenRes->json();
                $accessToken = $tokenData['access_token'];
                $igUserId = $tokenData['user_id'] ?? null;

                // Long-lived access token exchange (60 days)
                $longLivedRes = Http::get('https://graph.instagram.com/access_token', [
                    'grant_type' => 'ig_exchange_token',
                    'client_secret' => $clientSecret,
                    'access_token' => $accessToken,
                ]);
                if ($longLivedRes->successful() && ! empty($longLivedRes->json('access_token'))) {
                    $accessToken = $longLivedRes->json('access_token');
                }

                // Profile fetch
                $profileRes = Http::get('https://graph.instagram.com/v20.0/me', [
                    'fields' => 'id,username,account_type,media_count,profile_picture_url',
                    'access_token' => $accessToken,
                ]);
                if (! $profileRes->successful()) {
                    $profileRes = Http::get('https://graph.instagram.com/me', [
                        'fields' => 'id,username,account_type,media_count',
                        'access_token' => $accessToken,
                    ]);
                }
                $profile = $profileRes->successful() ? $profileRes->json() : [];

                $instagramUser = [
                    'id' => (string) ($profile['id'] ?? $igUserId),
                    'name' => $profile['username'] ?? 'Instagram User',
                    'email' => null,
                    'avatar' => $profile['profile_picture_url'] ?? null,
                    'token' => $accessToken,
                    'refreshToken' => null,
                    'expiresIn' => 5184000,
                ];

                $result = $this->instagramService->connectAccount($userId, $instagramUser, 'instagram');
                Log::info("Instagram Direct Account Connected for User {$userId}", $result);

                return response()->json([
                    'success' => true,
                    'message' => 'Instagram connected successfully via Instagram Login.',
                    'user_id' => $userId,
                    'account_id' => $result['social_account']->id,
                    'instagram_accounts' => $result['instagram_accounts'],
                ]);
            }

            // If token exchange failed, log error details
            Log::error('Instagram Direct Token Exchange failed: '.$tokenRes->body());

            // 2. Fallback to Meta Facebook Driver
            $fbUser = Socialite::driver('facebook')->stateless()->user();
            $facebookUser = [
                'id' => $fbUser->getId(),
                'name' => $fbUser->getName(),
                'email' => $fbUser->getEmail(),
                'avatar' => $fbUser->getAvatar(),
                'token' => $fbUser->token,
                'refreshToken' => $fbUser->refreshToken,
                'expiresIn' => $fbUser->expiresIn,
            ];

            $result = $this->instagramService->connectAccount($userId, $facebookUser, 'facebook');
            Log::info("Instagram Meta Account Connected for User {$userId}", $result);

            return response()->json([
                'success' => true,
                'message' => 'Instagram connected successfully.',
                'user_id' => $userId,
                'account_id' => $result['social_account']->id,
                'instagram_accounts' => $result['instagram_accounts'],
            ]);

        } catch (\Exception $e) {
            Log::error('Instagram Callback Error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Connect Instagram directly using access token (e.g. from Mobile SDK or Graph API).
     * POST /api/social/instagram/connect-token
     */
    public function instagramConnectToken(Request $request): JsonResponse
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        $token = $request->input('access_token');
        $userId = $request->user()->id;

        try {
            $profileRes = Http::get('https://graph.instagram.com/v20.0/me', [
                'fields' => 'id,username,account_type,media_count,profile_picture_url',
                'access_token' => $token,
            ]);
            if (! $profileRes->successful()) {
                $profileRes = Http::get('https://graph.instagram.com/me', [
                    'fields' => 'id,username,account_type,media_count',
                    'access_token' => $token,
                ]);
            }
            $profile = $profileRes->successful() ? $profileRes->json() : [];

            $instagramUser = [
                'id' => (string) ($profile['id'] ?? $request->input('user_id', 'instagram_'.time())),
                'name' => $profile['username'] ?? $request->input('username', 'Instagram User'),
                'email' => null,
                'avatar' => $profile['profile_picture_url'] ?? null,
                'token' => $token,
                'refreshToken' => null,
                'expiresIn' => 5184000,
            ];

            $result = $this->instagramService->connectAccount($userId, $instagramUser, 'instagram');

            return response()->json([
                'success' => true,
                'message' => 'Instagram connected successfully via access token.',
                'account_id' => $result['social_account']->id,
                'instagram_accounts' => $result['instagram_accounts'],
            ]);

        } catch (\Exception $e) {
            Log::error('Direct Instagram token connection error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Connection failed: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get connected Instagram account details.
     * GET /api/social/instagram/connected
     */
    public function getConnectedInstagram(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $account = $this->instagramService->getConnectedAccount($userId);

        return response()->json([
            'success' => true,
            'data' => $account ? new InstagramAccountResource($account) : null,
        ]);
    }

    /**
     * Fetch Facebook Pages associated with the user's connected social account.
     * GET /api/social/facebook/pages
     */
    public function listPages(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $pages = $this->facebookService->getPages($userId);

        return response()->json([
            'success' => true,
            'data' => SocialPageResource::collection($pages),
        ]);
    }

    /**
     * Connect selected Facebook Page.
     * POST /api/social/facebook/pages/connect
     */
    public function connectPage(ConnectFacebookPageRequest $request): JsonResponse
    {
        $userId = $request->user()->id;
        $pageId = $request->input('page_id');

        $page = $this->facebookService->connectPage($userId, $pageId);

        if (! $page) {
            return response()->json([
                'success' => false,
                'message' => 'The selected Facebook Page was not found under your connected account.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Facebook Page connected successfully.',
            'data' => new SocialPageResource($page),
        ]);
    }

    /**
     * Get the currently connected Facebook Page.
     * GET /api/social/facebook/pages/connected
     */
    public function getConnectedPage(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $page = $this->facebookService->getConnectedPage($userId);

        return response()->json([
            'success' => true,
            'data' => $page ? new SocialPageResource($page) : null,
        ]);
    }

    /**
     * Disconnect the currently connected page.
     * POST /api/social/facebook/pages/disconnect
     */
    public function disconnectPage(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        $disconnected = $this->facebookService->disconnectPage($userId);

        if (! $disconnected) {
            return response()->json([
                'success' => false,
                'message' => 'No Facebook Page is currently connected.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Facebook Page disconnected successfully.',
        ]);
    }
}
