<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\OAuthToken;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Http;

class OAuthCredentialService
{
    public function __construct(
        private LoggerService $logger
    ) {}

    /**
     * Store OAuth credentials after successful authentication
     */
    public function storeCredentials(User|Admin $user, string $provider, $providerUser, array $scopes = []): OAuthToken
    {
        $tokenData = [
            'tokenable_id' => $user->id,
            'tokenable_type' => get_class($user),
            'provider' => $provider,
            'provider_user_id' => $providerUser->getId(),
            'access_token' => $providerUser->token,
            'refresh_token' => $providerUser->refreshToken,
            'expires_at' => $providerUser->expiresIn ? now()->addSeconds($providerUser->expiresIn) : null,
            'scopes' => $scopes,
            'provider_data' => [
                'name' => $providerUser->getName(),
                'email' => $providerUser->getEmail(),
                'avatar' => $providerUser->getAvatar(),
                'nickname' => $providerUser->getNickname(),
            ],
            'is_active' => true,
        ];

        // Update or create the token
        $token = OAuthToken::updateOrCreate(
            ['tokenable_id' => $user->id, 'tokenable_type' => get_class($user), 'provider' => $provider],
            $tokenData
        );

        $this->logger->oauth()->info('OAuth credentials stored', [
            'user_id' => $user->id,
            'provider' => $provider,
            'provider_user_id' => $providerUser->getId(),
            'action' => 'oauth_credentials_stored',
        ]);

        return $token;
    }

    /**
     * Get active token for user and provider
     */
    public function getActiveToken(User|Admin $user, string $provider): ?OAuthToken
    {
        return OAuthToken::active()
            ->forProvider($provider)
            ->where('tokenable_id', $user->id)
            ->where('tokenable_type', get_class($user))
            ->first();
    }

    /**
     * Get valid access token (refresh if needed)
     */
    public function getValidAccessToken(User|Admin $user, string $provider): ?string
    {
        $token = $this->getActiveToken($user, $provider);

        if (! $token) {
            $this->logger->oauth()->warning('No OAuth token found for user', [
                'user_id' => $user->id,
                'provider' => $provider,
                'action' => 'oauth_token_not_found',
            ]);

            return null;
        }

        // Check if token needs refresh
        if ($token->needsRefresh()) {
            $refreshedToken = $this->refreshToken($token);
            if (! $refreshedToken) {
                return null;
            }
            $token = $refreshedToken;
        }

        return $token->access_token;
    }

    /**
     * Refresh an expired or soon-to-expire token
     */
    public function refreshToken(OAuthToken $token): ?OAuthToken
    {
        if (! $token->refresh_token) {
            $this->logger->oauth()->error('No refresh token available', [
                'user_id' => $token->user_id,
                'provider' => $token->provider,
                'action' => 'oauth_refresh_token_missing',
            ]);

            return null;
        }

        try {
            $newToken = $this->performTokenRefresh($token);

            if ($newToken) {
                $this->logger->oauth()->info('OAuth token refreshed successfully', [
                    'user_id' => $token->user_id,
                    'provider' => $token->provider,
                    'action' => 'oauth_token_refreshed',
                ]);

                return $newToken;
            }
        } catch (Exception $e) {
            $this->logger->oauth()->error('OAuth token refresh failed', [
                'user_id' => $token->user_id,
                'provider' => $token->provider,
                'error' => $e->getMessage(),
                'action' => 'oauth_token_refresh_failed',
            ]);
        }

        return null;
    }

    /**
     * Perform the actual token refresh (provider-specific)
     */
    private function performTokenRefresh(OAuthToken $token): ?OAuthToken
    {
        return match ($token->provider) {
            'spotify' => $this->refreshSpotifyToken($token),
            'google' => $this->refreshGoogleToken($token),
            // Add more providers here
            // 'github' => $this->refreshGithubToken($token),
            default => throw new Exception("Token refresh not supported for provider: {$token->provider}"),
        };
    }

    /**
     * Refresh Spotify token
     */
    private function refreshSpotifyToken(OAuthToken $token): ?OAuthToken
    {
        $response = Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token->refresh_token,
            'client_id' => config('services.spotify.client_id'),
            'client_secret' => config('services.spotify.client_secret'),
        ]);

        if (! $response->successful()) {
            throw new Exception('Spotify token refresh failed: '.$response->body());
        }

        $data = $response->json();

        // Update the token with new credentials
        $token->update([
            'access_token' => $data['access_token'],
            'refresh_token' => $data['refresh_token'] ?? $token->refresh_token, // Keep old refresh token if not provided
            'expires_at' => now()->addSeconds($data['expires_in']),
        ]);

        return $token->fresh();
    }

    /**
     * Refresh Google token
     */
    private function refreshGoogleToken(OAuthToken $token): ?OAuthToken
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'refresh_token' => $token->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (!$response->successful()) {
            throw new Exception('Google token refresh failed: ' . $response->body());
        }

        $data = $response->json();

        // Update the token with new credentials
        $token->update([
            'access_token' => $data['access_token'],
            'expires_at' => now()->addSeconds($data['expires_in']),
            // Google doesn't provide a new refresh token, keep the existing one
        ]);

        return $token->fresh();
    }

    /**
     * Make authenticated API request to provider
     */
    public function makeApiRequest(User|Admin $user, string $provider, string $method, string $url, array $options = []): array
    {
        $accessToken = $this->getValidAccessToken($user, $provider);

        if (! $accessToken) {
            throw new Exception("No valid access token available for provider: {$provider}");
        }

        $response = Http::withToken($accessToken)
            ->send($method, $url, $options);

        if (! $response->successful()) {
            $this->logger->oauth()->error('OAuth API request failed', [
                'user_id' => $user->id,
                'provider' => $provider,
                'method' => $method,
                'url' => $url,
                'status' => $response->status(),
                'response' => $response->body(),
                'action' => 'oauth_api_request_failed',
            ]);

            throw new Exception("API request failed: {$response->status()} - {$response->body()}");
        }

        return $response->json();
    }

    /**
     * Revoke OAuth token
     */
    public function revokeToken(OAuthToken $token): bool
    {
        try {
            // Mark as inactive
            $token->update(['is_active' => false]);

            // Optionally revoke on provider side
            $this->revokeTokenOnProvider($token);

            $this->logger->oauth()->info('OAuth token revoked', [
                'user_id' => $token->user_id,
                'provider' => $token->provider,
                'action' => 'oauth_token_revoked',
            ]);

            return true;
        } catch (Exception $e) {
            $this->logger->oauth()->error('OAuth token revocation failed', [
                'user_id' => $token->user_id,
                'provider' => $token->provider,
                'error' => $e->getMessage(),
                'action' => 'oauth_token_revocation_failed',
            ]);

            return false;
        }
    }

    /**
     * Revoke token on provider side
     */
    private function revokeTokenOnProvider(OAuthToken $token): void
    {
        match ($token->provider) {
            'spotify' => $this->revokeSpotifyToken($token),
            // Add more providers here
            default => null, // Some providers don't support revocation
        };
    }

    /**
     * Revoke Spotify token
     */
    private function revokeSpotifyToken(OAuthToken $token): void
    {
        Http::asForm()->post('https://accounts.spotify.com/api/token', [
            'token' => $token->access_token,
        ]);
    }

    /**
     * Clean up expired tokens
     */
    public function cleanupExpiredTokens(): int
    {
        $expiredTokens = OAuthToken::expired()->get();
        $count = $expiredTokens->count();

        foreach ($expiredTokens as $token) {
            $token->update(['is_active' => false]);
        }

        $this->logger->oauth()->info('Expired OAuth tokens cleaned up', [
            'count' => $count,
            'action' => 'oauth_tokens_cleanup',
        ]);

        return $count;
    }
}
