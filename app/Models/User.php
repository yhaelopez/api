<?php

namespace App\Models;

use App\Enums\GuardEnum;
use App\Traits\RestoreStamps;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Mattiverse\Userstamps\Traits\Userstamps;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use InteractsWithMedia;
    use Notifiable;
    use RestoreStamps;
    use SoftDeletes;
    use Userstamps;

    /**
     * The guard used for authentication
     */
    protected $guard_name = GuardEnum::WEB->value;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'spotify_id',
        'google_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'restored_at' => 'datetime',
        ];
    }

    /**
     * Get the OAuth tokens for the user
     */
    public function oauthTokens(): HasMany
    {
        return $this->hasMany(OAuthToken::class);
    }

    /**
     * Get OAuth token for a specific provider
     */
    public function getOAuthToken(string $provider): ?OAuthToken
    {
        return $this->oauthTokens()
            ->active()
            ->forProvider($provider)
            ->first();
    }

    /**
     * Check if user has OAuth token for provider
     */
    public function hasOAuthToken(string $provider): bool
    {
        return ! empty($this->getOAuthToken($provider));
    }
}
