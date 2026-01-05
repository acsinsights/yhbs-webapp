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

            // Calculate expiry date (90 days from now)
            $expiresAt = now()->addDays(90);

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
                'expires_at' => $expiresAt,
                'is_expired' => false,
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

    /**
     * Expire old wallet credits (90 days old)
     */
    public function expireOldCredits(): int
    {
        $expiredCount = 0;

        // Get all expired credits that haven't been marked as expired yet
        $expiredCredits = WalletTransaction::where('type', 'credit')
            ->where('is_expired', false)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->get();

        foreach ($expiredCredits as $credit) {
            DB::transaction(function () use ($credit, &$expiredCount) {
                // Mark as expired
                $credit->update(['is_expired' => true]);

                // Deduct from user's wallet balance
                $user = $credit->user;
                $newBalance = max(0, $user->wallet_balance - $credit->amount);
                $user->update(['wallet_balance' => $newBalance]);

                $expiredCount++;
            });
        }

        return $expiredCount;
    }
}
