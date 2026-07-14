<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
     * Redirect the user to the Facebook authentication page.
     * GET /api/social/facebook/connect
     */
    public function facebookConnect(Request $request)
    {
        $user = $request->user();

        // Securely pass user_id inside state payload to mapping callback
        $state = Crypt::encryptString(json_encode([
            'user_id' => $user->id,
        ]));

        return Socialite::driver('facebook')
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
            ->with(['state' => $state])
            ->stateless()
            ->redirect();
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
                'message' => 'Facebook and linked Instagram connected successfully.',
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

            return response()->json([
                'success' => true,
                'message' => 'Facebook and linked Instagram connected successfully.',
                'account_id' => $socialAccount->id,
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
     * Get social account connection status.
     * GET /api/social/accounts
     */
    public function status(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'google' => $user->socialAccounts()->where('provider', 'google')->exists(),
            'facebook' => $user->socialAccounts()->where('provider', 'facebook')->exists(),
            'instagram' => $user->instagramAccounts()->exists(),
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
        $userId = $request->user()->id;
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

    public function facebookRedirectUrl()
    {
        $url = Socialite::driver('facebook')
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
            ->stateless()
            ->redirect()
            ->getTargetUrl();

        return response()->json([
            'success' => true,
            'redirect_url' => $url,
        ]);
    }
}
