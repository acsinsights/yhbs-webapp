<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'booking_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'source',
        'expires_at',
        'is_expired',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'expires_at' => 'datetime',
        'is_expired' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Check if this wallet credit has expired
     */
    public function isExpired(): bool
    {
        if ($this->type !== 'credit' || !$this->expires_at) {
            return false;
        }

        return now()->isAfter($this->expires_at);
    }

    /**
     * Scope to get only non-expired credits
     */
    public function scopeNonExpired($query)
    {
        return $query->where(function ($q) {
            $q->where('type', 'debit')
                ->orWhere(function ($q2) {
                    $q2->where('type', 'credit')
                        ->where('is_expired', false)
                        ->where(function ($q3) {
                            $q3->whereNull('expires_at')
                                ->orWhere('expires_at', '>', now());
                        });
                });
        });
    }
}
