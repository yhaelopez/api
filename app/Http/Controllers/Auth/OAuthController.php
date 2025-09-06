<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\OAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OAuthController extends Controller
{
    public function __construct(
        private OAuthService $oauthService
    ) {}

    /**
     * Redirect the user to the OAuth provider
     */
    public function redirect(string $provider): RedirectResponse
    {
        return $this->oauthService->redirectToProvider($provider, 'admin');
    }

    /**
     * Handle the OAuth callback from the provider
     */
    public function callback(string $provider, Request $request): RedirectResponse
    {
        return $this->oauthService->handleCallback($provider, 'admin');
    }
}
