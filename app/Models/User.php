<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\RolesEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'role',
        'wallet_balance',
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
            'role' => RolesEnum::class,
            'wallet_balance' => 'decimal:2',
        ];
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(RolesEnum::ADMIN->value);
    }

    /**
     * Check if user is reception
     */
    public function isReception(): bool
    {
        return $this->hasRole(RolesEnum::RECEPTION->value);
    }

    /**
     * Check if user is admin or reception
     */
    public function isAdminOrReception(): bool
    {
        return in_array($this->role, [RolesEnum::ADMIN, RolesEnum::RECEPTION]);
    }

    /**
     * Get wallet transactions
     */
    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    /**
     * Get wallet balance
     */
    public function getWalletBalance(): float
    {
        return $this->wallet_balance ?? 0;
    }

    /**
     * Check if user has sufficient wallet balance
     */
    public function hasSufficientBalance(float $amount): bool
    {
        return $this->wallet_balance >= $amount;
    }

    /**
     * Get user notifications
     */
    public function userNotifications()
    {
        return $this->hasMany(UserNotification::class)->latest();
    }

    /**
     * Get unread notifications count
     */
    public function unreadNotificationsCount(): int
    {
        return $this->userNotifications()->unread()->count();
    }
}
