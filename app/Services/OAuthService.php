<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\SpotifyProvider;

class OAuthService
{
    /**
     * Supported OAuth providers
     */
    private const SUPPORTED_PROVIDERS = [
        'spotify' => 'spotify_id',
        'google' => 'google_id',
        // Add more providers here in the future
        // 'github' => 'github_id',
    ];

    public function __construct(
        private LoggerService $logger,
        private OAuthCredentialService $credentialService
    ) {}

    /**
     * Redirect the user to the OAuth provider
     */
    public function redirectToProvider(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        return match ($provider) {
            'spotify' => $this->redirectToSpotify(),
            'google' => $this->redirectToGoogle(),
            // Add more providers here in the future
            // 'github' => $this->redirectToGithub(),
            default => Socialite::driver($provider)->redirect(),
        };
    }

    /**
     * Handle the OAuth callback from the provider
     */
    public function handleCallback(string $provider): RedirectResponse
    {
        $this->validateProvider($provider);

        return match ($provider) {
            'spotify' => $this->handleSpotifyCallback(),
            'google' => $this->handleGoogleCallback(),
            // Add more providers here in the future
            // 'github' => $this->handleGithubCallback(),
            default => $this->handleGenericCallback($provider),
        };
    }

    /**
     * Redirect to Spotify OAuth with specific scopes
     */
    private function redirectToSpotify(): RedirectResponse
    {
        /** @var SpotifyProvider $spotifyDriver */
        $spotifyDriver = Socialite::driver('spotify');

        return $spotifyDriver->scopes([
            // General Web API scopes
            'ugc-image-upload',
            'user-read-playback-state',
            'user-modify-playback-state',
            'user-read-currently-playing',
            'app-remote-control',
            'streaming',
            'playlist-read-private',
            'playlist-read-collaborative',
            'playlist-modify-private',
            'playlist-modify-public',
            'user-follow-modify',
            'user-follow-read',
            'user-read-playback-position',
            'user-top-read',
            'user-read-recently-played',
            'user-library-modify',
            'user-library-read',
            'user-read-email',
            'user-read-private',
        /**
         * @todo: Ask Spotify for access to these scopes
         */
            // 'user-personalized',
            // Open Access (special program) scopes
            // 'user-soa-link',
            // 'user-soa-unlink',
            // 'soa-manage-entitlements',
            // 'soa-manage-partner',
            // 'soa-create-partner',
        ])->with([
            'show_dialog' => 'true', // Always show permission consent screen
        ])->redirect();
    }

    /**
     * Handle Spotify OAuth callback
     */
    private function handleSpotifyCallback(): RedirectResponse
    {
        try {
            $providerUser = Socialite::driver('spotify')->user();
        } catch (Exception $e) {
            $this->logger->oauth()->error('Spotify OAuth callback error', [
                'provider' => 'spotify',
                'error' => $e->getMessage(),
                'action' => 'spotify_callback_failed',
            ]);

            return redirect()->route('login')
                ->withErrors(['oauth' => 'Spotify authentication failed. Please try again.']);
        }

        // Store OAuth credentials
        $scopes = [
            'ugc-image-upload',
            'user-read-playback-state',
            'user-modify-playback-state',
            'user-read-currently-playing',
            'app-remote-control',
            'streaming',
            'playlist-read-private',
            'playlist-read-collaborative',
            'playlist-modify-private',
            'playlist-modify-public',
            'user-follow-modify',
            'user-follow-read',
            'user-read-playback-position',
            'user-top-read',
            'user-read-recently-played',
            'user-library-modify',
            'user-library-read',
            'user-read-email',
            'user-read-private',
        ];

        return $this->processOAuthUser('spotify', $providerUser, $scopes);
    }

    /**
     * Redirect to Google OAuth with specific scopes
     */
    private function redirectToGoogle(): RedirectResponse
    {
        /** @var GoogleProvider $googleDriver */
        $googleDriver = Socialite::driver('google');
        
        return $googleDriver->scopes([
            'openid',
            'profile',
            'email',
        ])->with([
            'prompt' => 'consent', // Always show consent screen
            'access_type' => 'offline', // Request refresh token
        ])->redirect();
    }

    /**
     * Handle Google OAuth callback
     */
    private function handleGoogleCallback(): RedirectResponse
    {
        try {
            $providerUser = Socialite::driver('google')->user();
        } catch (Exception $e) {
            $this->logger->oauth()->error('Google OAuth callback error', [
                'provider' => 'google',
                'error' => $e->getMessage(),
                'action' => 'google_callback_failed',
            ]);

            return redirect()->route('login')
                ->withErrors(['oauth' => 'Google authentication failed. Please try again.']);
        }

        // Store OAuth credentials
        $scopes = [
            'openid',
            'profile',
            'email',
        ];

        return $this->processOAuthUser('google', $providerUser, $scopes);
    }

    /**
     * Handle generic OAuth callback (for providers without specific logic)
     */
    private function handleGenericCallback(string $provider): RedirectResponse
    {
        try {
            $providerUser = Socialite::driver($provider)->user();
        } catch (Exception $e) {
            $this->logger->oauth()->error('OAuth callback error', [
                'provider' => $provider,
                'error' => $e->getMessage(),
                'action' => 'oauth_callback_failed',
            ]);

            return redirect()->route('login')
                ->withErrors(['oauth' => 'Authentication failed. Please try again.']);
        }

        return $this->processOAuthUser($provider, $providerUser, []);
    }

    /**
     * Process OAuth user (common logic for all providers)
     */
    private function processOAuthUser(string $provider, $providerUser, array $scopes = []): RedirectResponse
    {
        // Find user by email
        $user = User::where('email', $providerUser->getEmail())->first();

        if (! $user) {
            $this->logger->oauth()->warning('OAuth login attempted with non-existent user', [
                'provider' => $provider,
                'email' => $providerUser->getEmail(),
                'action' => 'oauth_user_not_found',
            ]);

            return redirect()->route('login')
                ->withErrors(['oauth' => 'No account found with this email address. Please contact an administrator.']);
        }

        // Update the provider ID if it's null
        $providerIdField = self::SUPPORTED_PROVIDERS[$provider];

        if (empty($user->$providerIdField)) {
            $user->update([$providerIdField => $providerUser->getId()]);

            $this->logger->oauth()->info('OAuth provider linked to user', [
                'user_id' => $user->id,
                'provider' => $provider,
                'provider_id' => $providerUser->getId(),
                'action' => 'oauth_provider_linked',
            ]);
        }

        // Store OAuth credentials
        $this->credentialService->storeCredentials($user, $provider, $providerUser, $scopes);

        // Auto-verify email if not already verified
        if (empty($user->email_verified_at)) {
            $user->update(['email_verified_at' => now()]);

            $this->logger->oauth()->info('User email auto-verified via OAuth', [
                'user_id' => $user->id,
                'provider' => $provider,
                'action' => 'oauth_email_auto_verified',
            ]);
        }

        Auth::login($user);

        $this->logger->oauth()->info('User logged in via OAuth', [
            'user_id' => $user->id,
            'provider' => $provider,
            'action' => 'oauth_login_success',
        ]);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Validate that the provider is supported
     */
    private function validateProvider(string $provider): void
    {
        if (! array_key_exists($provider, self::SUPPORTED_PROVIDERS)) {
            $this->logger->oauth()->error('Unsupported OAuth provider attempted', [
                'provider' => $provider,
                'action' => 'oauth_unsupported_provider',
            ]);

            throw ValidationException::withMessages([
                'provider' => 'Unsupported OAuth provider.',
            ]);
        }
    }
}
