<?php

use Livewire\Volt\Component;
use App\Models\WalletTransaction;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function with(): array
    {
        $transactions = WalletTransaction::where('user_id', auth()->id())
            ->with('booking')
            ->orderBy('created_at', 'desc')
            ->paginate(2);

        return [
            'transactions' => $transactions,
            'currentBalance' => auth()->user()->wallet_balance ?? 0,
        ];
    }
}; ?>

<div>
    <!-- Current Balance Card -->
    <div class="wallet-balance-header text-center py-4 px-3 bg-light border-bottom">
        <p class="text-muted mb-2 small">Current Wallet Balance</p>
        <h2 class="mb-2" style="color: #667eea; font-weight: 700;">{{ currency_format($currentBalance) }}</h2>
        @if ($currentBalance > 0)
            <span class="badge bg-success">
                <i class="bi bi-check-circle-fill me-1"></i>Available for bookings
            </span>
        @else
            <span class="badge bg-secondary">
                <i class="bi bi-info-circle me-1"></i>No balance available
            </span>
        @endif
    </div>

    <!-- Transactions List -->
    <div class="transactions-list p-3">
        @if ($transactions->count() > 0)
            @foreach ($transactions as $transaction)
                <div class="transaction-item p-3 mb-3 border rounded"
                    style="border-left: 4px solid {{ $transaction->type === 'credit' ? '#28a745' : '#dc3545' }} !important;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                @if ($transaction->type === 'credit')
                                    <i class="bi bi-arrow-down-circle-fill text-success" style="font-size: 20px;"></i>
                                    <span class="badge bg-success-subtle text-success">Credit</span>
                                @else
                                    <i class="bi bi-arrow-up-circle-fill text-danger" style="font-size: 20px;"></i>
                                    <span class="badge bg-danger-subtle text-danger">Debit</span>
                                @endif
                            </div>
                            <h6 class="mb-1">{{ $transaction->description }}</h6>
                            <small class="text-muted">
                                <i
                                    class="bi bi-calendar3 me-1"></i>{{ $transaction->created_at->format('d M Y, h:i A') }}
                            </small>
                            @if ($transaction->booking)
                                <div class="mt-1">
                                    <small class="text-muted">
                                        <i class="bi bi-receipt me-1"></i>Booking #{{ $transaction->booking_id }}
                                    </small>
                                </div>
                            @endif

                            @if ($transaction->type === 'credit' && $transaction->expires_at)
                                <div class="mt-2">
                                    @php
                                        $daysUntilExpiry = now()->diffInDays($transaction->expires_at, false);
                                    @endphp
                                    @if ($transaction->is_expired)
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle me-1"></i>Expired
                                        </span>
                                    @elseif ($daysUntilExpiry <= 7)
                                        <span class="badge bg-warning">
                                            <i class="bi bi-exclamation-triangle me-1"></i>Expires in
                                            {{ $daysUntilExpiry }} days
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-clock-history me-1"></i>Expires
                                            {{ $transaction->expires_at->format('d M Y') }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="text-end ms-3">
                            <h5 class="mb-1 fw-bold"
                                style="color: {{ $transaction->type === 'credit' ? '#28a745' : '#dc3545' }};">
                                {{ $transaction->type === 'credit' ? '+' : '-' }}{{ currency_format($transaction->amount) }}
                            </h5>
                            <small class="text-muted">Balance:
                                {{ currency_format($transaction->balance_after) }}</small>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Pagination -->
            <div class="mt-3">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 48px; color: #ccc;"></i>
                <h5 class="mt-3">No Transactions Yet</h5>
                <p class="text-muted">Your wallet transaction history will appear here</p>
            </div>
        @endif
    </div>

    <style>
        .transaction-item {
            transition: all 0.3s ease;
            background: #fff;
        }

        .transaction-item:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateX(2px);
        }
    </style>
</div>
