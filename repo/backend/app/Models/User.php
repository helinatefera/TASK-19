<?php

namespace App\Models;

use App\Enums\RestrictionLevel;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'display_name',
        'email_encrypted',
        'phone_encrypted',
        'address_encrypted',
        'email_hash',
        'phone_hash',
        'locale',
        'timezone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'email_encrypted',
        'phone_encrypted',
        'address_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'email_encrypted' => 'encrypted',
            'phone_encrypted' => 'encrypted',
            'address_encrypted' => 'encrypted',
        ];
    }

    /* ------------------------------------------------------------------ */
    /*  Relationships                                                      */
    /* ------------------------------------------------------------------ */

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(Campaign::class, 'creator_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    public function receivedReviews(): HasMany
    {
        return $this->hasMany(Review::class, 'reviewee_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function creditScore(): HasOne
    {
        return $this->hasOne(CreditScore::class);
    }

    public function anomalyFlags(): HasMany
    {
        return $this->hasMany(AnomalyFlag::class);
    }

    public function deviceFingerprints(): HasMany
    {
        return $this->hasMany(DeviceFingerprint::class);
    }

    public function eventSubscriptions(): HasMany
    {
        return $this->hasMany(EventSubscription::class);
    }

    /* ------------------------------------------------------------------ */
    /*  Methods                                                            */
    /* ------------------------------------------------------------------ */

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('name', $role)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        return $this->roles()
            ->whereHas('permissions', fn ($q) => $q->where('name', $permission))
            ->exists();
    }

    public function isRestricted(): bool
    {
        $creditScore = $this->creditScore;

        if (! $creditScore) {
            return false;
        }

        return $creditScore->restriction_level !== RestrictionLevel::None;
    }
}
