<?php

namespace App\Models;

use App\Models\UserEmailVerification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'is_admin',
        'email_verified_at',
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
     * The attributes that should be appended to the model's array form.
     *
     * @var list<string>
     */
    protected $appends = [
        'name',
        'avatar',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's full name.
     */
    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Avatar URL for the user.
     *
     */
    public function getAvatarAttribute(): string
    {
        return 'https://www.gravatar.com/avatar/' . md5(strtolower($this->email));
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return 'https://www.gravatar.com/avatar/' . md5(strtolower($this->email));
    }

    /**
     * Determine if the user can access Filament admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin === true;
    }

    /**
     * Relationship: Get the latest email verification token.
     */
    public function emailVerification()
    {
        return $this->hasOne(UserEmailVerification::class)->latestOfMany();
    }

    /**
     * Check if the user has verified their email.
     */
    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Relationship: Billing Address.
     */
    public function billing()
    {
        return $this->hasOne(UserBilling::class);
    }
}
