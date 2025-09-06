<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OAuthToken extends Model
{
    use HasFactory;

    protected $table = 'oauth_tokens';

    protected $fillable = [
        'tokenable_id',
        'tokenable_type',
        'provider',
        'provider_user_id',
        'access_token',
        'refresh_token',
        'expires_at',
        'scopes',
        'provider_data',
        'is_active',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'scopes' => 'array',
        'provider_data' => 'array',
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    /**
     * Get the parent tokenable model (User or Admin)
     */
    public function tokenable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if the token is expired
     */
    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false; // No expiration set
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if the token needs refresh (expires within 5 minutes)
     */
    public function needsRefresh(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return $this->expires_at->subMinutes(5)->isPast();
    }

    /**
     * Check if the token has a specific scope
     */
    public function hasScope(string $scope): bool
    {
        return in_array($scope, $this->scopes ?? []);
    }

    /**
     * Get the access token (decrypted)
     */
    public function getAccessTokenAttribute($value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    /**
     * Set the access token (encrypted)
     */
    public function setAccessTokenAttribute($value): void
    {
        $this->attributes['access_token'] = $value ? encrypt($value) : null;
    }

    /**
     * Get the refresh token (decrypted)
     */
    public function getRefreshTokenAttribute($value): ?string
    {
        return $value ? decrypt($value) : null;
    }

    /**
     * Set the refresh token (encrypted)
     */
    public function setRefreshTokenAttribute($value): void
    {
        $this->attributes['refresh_token'] = $value ? encrypt($value) : null;
    }

    /**
     * Scope to get active tokens
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get tokens for a specific provider
     */
    public function scopeForProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope to get expired tokens
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Scope to get tokens that need refresh
     */
    public function scopeNeedsRefresh($query)
    {
        return $query->where('expires_at', '<', now()->addMinutes(5));
    }
}
