<?php

namespace App\Services;

use App\Models\User;
use App\Models\Booking;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;

class WalletService
{
    /**
     * Add credit to user's wallet (for refunds)
     */
    public function addCredit(User $user, float $amount, ?Booking $booking = null, string $description = null, string $source = 'booking_cancellation'): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $booking, $description, $source) {
            $balanceBefore = $user->wallet_balance;
            $balanceAfter = $balanceBefore + $amount;

            // Update user wallet balance
            $user->update(['wallet_balance' => $balanceAfter]);

            // Create transaction record
            return WalletTransaction::create([
                'user_id' => $user->id,
                'booking_id' => $booking?->id,
                'type' => 'credit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description ?? "Refund for booking #{$booking?->id}",
                'source' => $source,
            ]);
        });
    }

    /**
     * Deduct amount from user's wallet (for booking payment)
     */
    public function deductAmount(User $user, float $amount, ?Booking $booking = null, string $description = null): WalletTransaction
    {
        return DB::transaction(function () use ($user, $amount, $booking, $description) {
            $balanceBefore = $user->wallet_balance;

            if ($balanceBefore < $amount) {
                throw new \Exception('Insufficient wallet balance');
            }

            $balanceAfter = $balanceBefore - $amount;

            // Update user wallet balance
            $user->update(['wallet_balance' => $balanceAfter]);

            // Create transaction record
            return WalletTransaction::create([
                'user_id' => $user->id,
                'booking_id' => $booking?->id,
                'type' => 'debit',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => $description ?? "Payment for booking #{$booking?->id}",
                'source' => 'booking_payment',
            ]);
        });
    }

    /**
     * Get user's wallet balance
     */
    public function getBalance(User $user): float
    {
        return $user->wallet_balance;
    }

    /**
     * Get user's wallet transactions
     */
    public function getTransactions(User $user, int $limit = 10)
    {
        return WalletTransaction::where('user_id', $user->id)
            ->with('booking')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
