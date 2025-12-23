<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Booking;
use App\Enums\BookingStatusEnum;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingCancellationApprovedMail;
use App\Mail\BookingCancellationRejectedMail;
use Mary\Traits\Toast;

new class extends Component {
    use WithPagination, Toast;

    public bool $showApproveModal = false;
    public bool $showRejectModal = false;
    public bool $showReasonModal = false;
    public ?Booking $selectedBooking = null;
    public float $refundAmount = 0;
    public string $rejectionReason = '';
    public string $selectedReason = '';

    public function openReasonModal($reason): void
    {
        $this->selectedReason = $reason;
        $this->showReasonModal = true;
    }

    public function closeReasonModal(): void
    {
        $this->showReasonModal = false;
        $this->selectedReason = '';
    }

    public function openApproveModal($bookingId): void
    {
        $this->selectedBooking = Booking::with(['user', 'bookingable'])->findOrFail($bookingId);
        $this->refundAmount = $this->selectedBooking->total ?? ($this->selectedBooking->price ?? 0);
        $this->showApproveModal = true;
    }

    public function closeApproveModal(): void
    {
        $this->showApproveModal = false;
        $this->selectedBooking = null;
        $this->refundAmount = 0;
        $this->resetValidation();
    }

    public function openRejectModal($bookingId): void
    {
        $this->selectedBooking = Booking::with(['user', 'bookingable'])->findOrFail($bookingId);
        $this->showRejectModal = true;
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->selectedBooking = null;
        $this->rejectionReason = '';
        $this->resetValidation();
    }

    public function approveCancellation(): void
    {
        $this->validate(['refundAmount' => 'required|numeric|min:0']);

        if (!$this->selectedBooking) {
            $this->error('Booking not found.');
            return;
        }

        $this->selectedBooking->update([
            'cancellation_status' => 'approved',
            'cancelled_at' => now(),
            'status' => BookingStatusEnum::CANCELLED,
            'refund_amount' => $this->refundAmount,
            'refund_status' => 'pending',
            'cancelled_by' => auth()->id(),
        ]);

        if ($this->refundAmount > 0) {
            // Use database transaction and lock to prevent race conditions
            \DB::transaction(function () {
                $user = \App\Models\User::lockForUpdate()->find($this->selectedBooking->user_id);
                $balanceBefore = $user->wallet_balance ?? 0;

                $user->wallet_balance = $balanceBefore + $this->refundAmount;
                $user->save();

                $balanceAfter = $user->wallet_balance;

                \App\Models\WalletTransaction::create([
                    'user_id' => $this->selectedBooking->user_id,
                    'amount' => $this->refundAmount,
                    'type' => 'credit',
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'description' => 'Refund for cancelled booking #' . $this->selectedBooking->id,
                    'booking_id' => $this->selectedBooking->id,
                ]);

                $this->selectedBooking->update(['refund_status' => 'completed']);
            });
        }

        try {
            Mail::to($this->selectedBooking->user->email)->send(new BookingCancellationApprovedMail($this->selectedBooking));
        } catch (\Exception $e) {
            \Log::error('Failed to send cancellation approval email: ' . $e->getMessage());
        }

        $this->success('Cancellation approved successfully. Refund has been credited to customer wallet.');
        $this->closeApproveModal();
        $this->resetPage();
    }

    public function rejectCancellation(): void
    {
        $this->validate(['rejectionReason' => 'required|string|min:10|max:500']);

        if (!$this->selectedBooking) {
            $this->error('Booking not found.');
            return;
        }

        $this->selectedBooking->update([
            'cancellation_status' => 'rejected',
            'cancellation_reason' => $this->selectedBooking->cancellation_reason . "\n\nRejection Reason: " . $this->rejectionReason,
        ]);

        try {
            Mail::to($this->selectedBooking->user->email)->send(new BookingCancellationRejectedMail($this->selectedBooking, $this->rejectionReason));
        } catch (\Exception $e) {
            \Log::error('Failed to send cancellation rejection email: ' . $e->getMessage());
        }

        $this->success('Cancellation request rejected.');
        $this->closeRejectModal();
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'cancellationRequests' => Booking::whereNotNull('cancellation_requested_at')
                ->where('cancellation_status', 'pending')
                ->with(['user', 'bookingable'])
                ->orderBy('cancellation_requested_at', 'desc')
                ->paginate(15),
        ];
    }
}; ?>

<div>
    <x-header title="Cancellation Requests" separator>
        <x-slot:middle class="!justify-end">
            <x-badge value="{{ $cancellationRequests->total() }} Pending" class="badge-error" />
        </x-slot:middle>
    </x-header>

    @if ($cancellationRequests->count() > 0)
        <x-card>
            <x-table :headers="[
                ['key' => 'id', 'label' => 'ID'],
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'property', 'label' => 'Property'],
                ['key' => 'dates', 'label' => 'Check-in / Check-out'],
                ['key' => 'amount', 'label' => 'Amount'],
                ['key' => 'requested', 'label' => 'Requested On'],
                ['key' => 'reason', 'label' => 'Reason'],
                ['key' => 'actions', 'label' => 'Actions'],
            ]" :rows="$cancellationRequests" with-pagination>
                @scope('cell_id', $booking)
                    <strong>#{{ $booking->id }}</strong>
                @endscope

                @scope('cell_customer', $booking)
                    <div>
                        <div class="font-semibold">{{ $booking->user->name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-500">{{ $booking->user->email ?? 'N/A' }}</div>
                    </div>
                @endscope

                @scope('cell_property', $booking)
                    <div>
                        <div class="font-semibold">{{ $booking->bookingable->name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-500">{{ class_basename($booking->bookingable_type) }}</div>
                    </div>
                @endscope

                @scope('cell_dates', $booking)
                    <div>
                        <div class="text-sm">
                            <x-icon name="o-calendar" class="w-4 h-4 text-success inline" />
                            {{ $booking->check_in->format('d M Y') }}
                        </div>
                        <div class="text-sm">
                            <x-icon name="o-calendar" class="w-4 h-4 text-error inline" />
                            {{ $booking->check_out->format('d M Y') }}
                        </div>
                    </div>
                @endscope

                @scope('cell_amount', $booking)
                    <strong
                        class="text-primary">{{ currency_format(number_format($booking->total ?? ($booking->price ?? 0), 2)) }}</strong>
                @endscope

                @scope('cell_requested', $booking)
                    <div>
                        <div class="text-sm">{{ $booking->cancellation_requested_at->format('d M Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $booking->cancellation_requested_at->format('H:i A') }}</div>
                    </div>
                @endscope

                @scope('cell_reason', $booking)
                    <x-button icon="o-eye" class="btn-sm btn-ghost"
                        wire:click="openReasonModal('{{ addslashes($booking->cancellation_reason) }}')" label="View" />
                @endscope

                @scope('cell_actions', $booking)
                    <div class="flex gap-2">
                        <x-button icon="o-check-circle" class="btn-sm btn-success"
                            wire:click="openApproveModal({{ $booking->id }})" spinner>
                            Approve
                        </x-button>
                        <x-button icon="o-x-circle" class="btn-sm btn-error"
                            wire:click="openRejectModal({{ $booking->id }})" spinner>
                            Reject
                        </x-button>
                    </div>
                @endscope
            </x-table>
        </x-card>
    @else
        <x-card>
            <div class="text-center py-12">
                <x-icon name="o-inbox" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                <h3 class="text-lg font-semibold mb-2">No Pending Requests</h3>
                <p class="text-gray-500">All cancellation requests have been processed.</p>
            </div>
        </x-card>
    @endif

    <!-- Approve Modal -->
    <x-modal wire:model="showApproveModal" title="Approve Cancellation Request" separator>
        @if ($selectedBooking)
            <div class="space-y-4">
                <x-alert icon="o-information-circle" class="alert-info mb-4">
                    Booking #{{ $selectedBooking->id }} - {{ $selectedBooking->bookingable->name ?? 'N/A' }}
                </x-alert>

                <x-input label="Refund Amount" wire:model="refundAmount" type="number" step="0.01"
                    prefix="{{ currency_symbol() }}" inline />

                <x-alert icon="o-exclamation-triangle" class="alert-warning">
                    The refund amount will be credited to the customer's wallet immediately.
                </x-alert>
            </div>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.closeApproveModal()" />
                <x-button label="Approve & Refund" class="btn-success" wire:click="approveCancellation" spinner />
            </x-slot:actions>
        @endif
    </x-modal>

    <!-- Reject Modal -->
    <x-modal wire:model="showRejectModal" title="Reject Cancellation Request" separator>
        @if ($selectedBooking)
            <div class="space-y-4">
                <x-alert icon="o-information-circle" class="alert-info mb-4">
                    Booking #{{ $selectedBooking->id }} - {{ $selectedBooking->bookingable->name ?? 'N/A' }}
                </x-alert>

                <x-textarea label="Rejection Reason" wire:model="rejectionReason" rows="4"
                    placeholder="Please provide a clear reason for rejecting this cancellation request..."
                    hint="Customer will receive this message via email" />

                <x-alert icon="o-exclamation-triangle" class="alert-error">
                    This action will notify the customer that their cancellation request has been declined.
                </x-alert>
            </div>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.closeRejectModal()" />
                <x-button label="Reject Request" class="btn-error" wire:click="rejectCancellation" spinner />
            </x-slot:actions>
        @endif
    </x-modal>

    <!-- Reason View Modal -->
    <x-modal wire:model="showReasonModal" title="Cancellation Reason" separator>
        <div class="prose max-w-none">
            <p class="whitespace-pre-line">{{ $selectedReason }}</p>
        </div>

        <x-slot:actions>
            <x-button label="Close" @click="$wire.closeReasonModal()" />
        </x-slot:actions>
    </x-modal>
</div>
