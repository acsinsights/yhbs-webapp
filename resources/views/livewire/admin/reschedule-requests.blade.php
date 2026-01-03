<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Booking;
use App\Enums\BookingStatusEnum;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingRescheduleApprovedMail;
use App\Mail\BookingRescheduleRejectedMail;
use Mary\Traits\Toast;

new class extends Component {
    use WithPagination, Toast;

    public bool $showApproveModal = false;
    public bool $showRejectModal = false;
    public bool $showReasonModal = false;
    public bool $showDetailsModal = false;
    public ?Booking $selectedBooking = null;
    public float $rescheduleFee = 0;
    public string $rejectionReason = '';
    public string $selectedReason = '';
    public string $paymentMethod = 'wallet'; // 'wallet' or 'manual'
    public float $extraFee = 0;
    public string $extraFeeRemark = '';

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

    public function openDetailsModal($bookingId): void
    {
        $this->selectedBooking = Booking::with(['user', 'bookingable'])->findOrFail($bookingId);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->selectedBooking = null;
    }

    public function openApproveModal($bookingId): void
    {
        $this->selectedBooking = Booking::with(['user', 'bookingable'])->findOrFail($bookingId);
        $this->rescheduleFee = $this->selectedBooking->reschedule_fee ?? $this->selectedBooking->calculateRescheduleFee();
        $this->paymentMethod = 'wallet';
        $this->showApproveModal = true;
    }

    public function closeApproveModal(): void
    {
        $this->showApproveModal = false;
        $this->selectedBooking = null;
        $this->rescheduleFee = 0;
        $this->paymentMethod = 'wallet';
        $this->extraFee = 0;
        $this->extraFeeRemark = '';
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

    public function approveReschedule(): void
    {
        $this->validate(
            [
                'rescheduleFee' => ['required', 'numeric', 'min:0'],
                'paymentMethod' => ['required', 'in:wallet,manual'],
                'extraFee' => ['nullable', 'numeric', 'min:0'],
                'extraFeeRemark' => ['nullable', 'string', 'max:500'],
            ],
            [
                'rescheduleFee.required' => 'Reschedule fee is required.',
                'rescheduleFee.min' => 'Reschedule fee cannot be negative.',
                'paymentMethod.required' => 'Please select a payment method.',
                'extraFee.min' => 'Extra fee cannot be negative.',
            ],
        );

        if (!$this->selectedBooking) {
            $this->error('Booking not found.');
            return;
        }

        $user = $this->selectedBooking->user;
        $walletBalance = $user->wallet_balance ?? 0;

        // Validate wallet balance if wallet payment is selected
        if ($this->paymentMethod === 'wallet' && $this->rescheduleFee > 0) {
            if ($walletBalance < $this->rescheduleFee) {
                $this->error('Customer does not have sufficient wallet balance (' . currency_format($walletBalance) . '). Please select "Collect Manually" or reduce the fee.');
                return;
            }
        }

        // Use database transaction
        \DB::transaction(function () use ($user) {
            // Deduct reschedule fee from wallet if wallet payment selected
            if ($this->paymentMethod === 'wallet' && $this->rescheduleFee > 0) {
                $userLocked = \App\Models\User::lockForUpdate()->find($user->id);
                $balanceBefore = $userLocked->wallet_balance ?? 0;

                $userLocked->wallet_balance = $balanceBefore - $this->rescheduleFee;
                $userLocked->save();

                $balanceAfter = $userLocked->wallet_balance;

                \App\Models\WalletTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $this->rescheduleFee,
                    'type' => 'debit',
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'description' => 'Reschedule fee for booking #' . $this->selectedBooking->id,
                    'booking_id' => $this->selectedBooking->id,
                ]);
            }

            // Update booking dates and status
            $this->selectedBooking->update([
                'reschedule_status' => 'approved',
                'check_in' => $this->selectedBooking->new_check_in,
                'check_out' => $this->selectedBooking->new_check_out,
                'reschedule_fee' => $this->rescheduleFee,
                'rescheduled_by' => auth()->id(),
                'extra_fee' => $this->extraFee > 0 ? $this->extraFee : null,
                'extra_fee_remark' => $this->extraFee > 0 ? $this->extraFeeRemark : null,
            ]);

            // Create notification for the customer
            \App\Models\UserNotification::create([
                'user_id' => $this->selectedBooking->user_id,
                'title' => 'Booking Reschedule Approved',
                'message' => 'Your reschedule request for booking #' . $this->selectedBooking->booking_id . ' has been approved.' . ($this->extraFee > 0 ? ' Extra fee: ' . currency_format($this->extraFee) . ($this->extraFeeRemark ? ' (' . $this->extraFeeRemark . ')' : '') : ''),
                'type' => 'success',
                'link' => route('customer.bookings.show', $this->selectedBooking->id),
            ]);
        });

        try {
            Mail::to($this->selectedBooking->user->email)->send(new BookingRescheduleApprovedMail($this->selectedBooking));
        } catch (\Exception $e) {
            \Log::error('Failed to send reschedule approval email: ' . $e->getMessage());
        }

        $successMessage = 'Reschedule approved successfully. Booking dates have been updated.';
        if ($this->paymentMethod === 'wallet' && $this->rescheduleFee > 0) {
            $successMessage .= ' Fee has been deducted from customer wallet.';
        } elseif ($this->paymentMethod === 'manual' && $this->rescheduleFee > 0) {
            $successMessage .= ' Fee will be collected manually from customer.';
        }

        $this->success($successMessage);
        $this->closeApproveModal();
        $this->resetPage();
    }

    public function rejectReschedule(): void
    {
        $this->validate(['rejectionReason' => 'required|string|min:10|max:500']);

        if (!$this->selectedBooking) {
            $this->error('Booking not found.');
            return;
        }

        $this->selectedBooking->update([
            'reschedule_status' => 'rejected',
            'reschedule_reason' => $this->selectedBooking->reschedule_reason . "\n\nRejection Reason: " . $this->rejectionReason,
        ]);

        try {
            Mail::to($this->selectedBooking->user->email)->send(new BookingRescheduleRejectedMail($this->selectedBooking, $this->rejectionReason));
        } catch (\Exception $e) {
            \Log::error('Failed to send reschedule rejection email: ' . $e->getMessage());
        }

        $this->success('Reschedule request rejected.');
        $this->closeRejectModal();
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'rescheduleRequests' => Booking::whereNotNull('reschedule_requested_at')
                ->where('reschedule_status', 'pending')
                ->with(['user', 'bookingable'])
                ->orderBy('reschedule_requested_at', 'desc')
                ->paginate(15),
        ];
    }
}; ?>

<div>
    <x-header title="Reschedule Requests" separator>
        <x-slot:middle class="!justify-end">
            <x-badge value="{{ $rescheduleRequests->total() }} Pending" class="badge-warning" />
        </x-slot:middle>
    </x-header>

    @if ($rescheduleRequests->count() > 0)
        <x-card>
            <x-table :headers="[
                ['key' => 'id', 'label' => 'ID'],
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'property', 'label' => 'Property'],
                ['key' => 'current_dates', 'label' => 'Current Dates'],
                ['key' => 'new_dates', 'label' => 'New Dates'],
                ['key' => 'fee', 'label' => 'Fee'],
                ['key' => 'requested', 'label' => 'Requested On'],
                ['key' => 'reason', 'label' => 'Reason'],
                ['key' => 'actions', 'label' => 'Actions'],
            ]" :rows="$rescheduleRequests" with-pagination>
                @scope('cell_id', $booking)
                    <strong>#{{ $booking->id }}</strong>
                @endscope

                @scope('cell_customer', $booking)
                    <div>
                        <div class="font-semibold">{{ $booking->user->name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-500">{{ $booking->user->email ?? 'N/A' }}</div>
                        <div class="text-xs text-info mt-1">
                            <x-icon name="o-wallet" class="w-3 h-3 inline" />
                            Wallet: {{ currency_format($booking->user->wallet_balance ?? 0) }}
                        </div>
                    </div>
                @endscope

                @scope('cell_property', $booking)
                    <div>
                        <div class="font-semibold">{{ $booking->bookingable->name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-500">{{ class_basename($booking->bookingable_type) }}</div>
                    </div>
                @endscope

                @scope('cell_current_dates', $booking)
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

                @scope('cell_new_dates', $booking)
                    <div>
                        <div class="text-sm font-semibold">
                            <x-icon name="o-calendar" class="w-4 h-4 text-success inline" />
                            {{ $booking->new_check_in?->format('d M Y') ?? 'N/A' }}
                        </div>
                        <div class="text-sm font-semibold">
                            <x-icon name="o-calendar" class="w-4 h-4 text-error inline" />
                            {{ $booking->new_check_out?->format('d M Y') ?? 'N/A' }}
                        </div>
                    </div>
                @endscope

                @scope('cell_fee', $booking)
                    <strong class="text-warning">{{ currency_format($booking->reschedule_fee ?? 0) }}</strong>
                @endscope

                @scope('cell_requested', $booking)
                    <div>
                        <div class="text-sm">{{ $booking->reschedule_requested_at->format('d M Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $booking->reschedule_requested_at->format('H:i A') }}</div>
                    </div>
                @endscope

                @scope('cell_reason', $booking)
                    <x-button icon="o-eye" class="btn-sm btn-ghost"
                        wire:click="openReasonModal('{{ addslashes($booking->reschedule_reason) }}')" label="View" />
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
                <p class="text-gray-500">All reschedule requests have been processed.</p>
            </div>
        </x-card>
    @endif

    <!-- Approve Modal -->
    <x-modal wire:model="showApproveModal" title="Approve Reschedule Request" separator>
        @if ($selectedBooking)
            <div class="space-y-4">
                <x-alert icon="o-information-circle" class="alert-info mb-4">
                    Booking #{{ $selectedBooking->id }} - {{ $selectedBooking->bookingable->name ?? 'N/A' }}
                </x-alert>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <h6 class="font-semibold mb-2">Current Dates:</h6>
                        <div class="text-sm">Check-in: {{ $selectedBooking->check_in->format('d M Y') }}</div>
                        <div class="text-sm">Check-out: {{ $selectedBooking->check_out->format('d M Y') }}</div>
                    </div>
                    <div>
                        <h6 class="font-semibold mb-2">New Dates:</h6>
                        <div class="text-sm font-semibold text-success">Check-in:
                            {{ $selectedBooking->new_check_in?->format('d M Y') ?? 'N/A' }}</div>
                        <div class="text-sm font-semibold text-error">Check-out:
                            {{ $selectedBooking->new_check_out?->format('d M Y') ?? 'N/A' }}</div>
                    </div>
                </div>

                <x-input label="Reschedule Fee" wire:model="rescheduleFee" type="number" step="0.01"
                    prefix="{{ currency_symbol() }}" inline />

                <x-input label="Extra Fee (Optional)" wire:model="extraFee" type="number" step="0.01" min="0"
                    prefix="{{ currency_symbol() }}" hint="Any additional fees for the reschedule" inline />

                <x-textarea label="Extra Fee Remark (Optional)" wire:model="extraFeeRemark" rows="2"
                    placeholder="Reason for extra fee..." hint="This will be shown to the customer" />

                <x-alert icon="o-information-circle" class="alert-info">
                    Customer Wallet Balance:
                    <strong>{{ currency_format($selectedBooking->user->wallet_balance ?? 0) }}</strong>
                </x-alert>

                <div class="mb-4">
                    <label class="block font-semibold mb-2">Payment Method:</label>
                    <div class="space-y-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="paymentMethod" value="wallet"
                                class="radio radio-primary mr-2">
                            <div>
                                <span class="font-medium">Deduct from Wallet</span>
                                <p class="text-xs text-gray-500">Fee will be automatically deducted from customer's
                                    wallet balance</p>
                            </div>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="paymentMethod" value="manual"
                                class="radio radio-primary mr-2">
                            <div>
                                <span class="font-medium">Collect Manually / Waive Fee</span>
                                <p class="text-xs text-gray-500">Collect payment manually from customer or waive the fee
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                @if ($paymentMethod === 'wallet')
                    <x-alert icon="o-exclamation-triangle" class="alert-warning">
                        The reschedule fee will be deducted from the customer's wallet and booking dates will be updated
                        immediately.
                    </x-alert>
                @else
                    <x-alert icon="o-information-circle" class="alert-info">
                        Booking dates will be updated. Fee collection is your responsibility - coordinate with customer
                        directly.
                    </x-alert>
                @endif
            </div>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.closeApproveModal()" />
                <x-button label="Approve & Update" class="btn-success" wire:click="approveReschedule" spinner />
            </x-slot:actions>
        @endif
    </x-modal>

    <!-- Reject Modal -->
    <x-modal wire:model="showRejectModal" title="Reject Reschedule Request" separator>
        @if ($selectedBooking)
            <div class="space-y-4">
                <x-alert icon="o-information-circle" class="alert-info mb-4">
                    Booking #{{ $selectedBooking->id }} - {{ $selectedBooking->bookingable->name ?? 'N/A' }}
                </x-alert>

                <x-textarea label="Rejection Reason" wire:model="rejectionReason" rows="4"
                    placeholder="Please provide a clear reason for rejecting this reschedule request..."
                    hint="Customer will receive this message via email" />

                <x-alert icon="o-exclamation-triangle" class="alert-error">
                    This action will notify the customer that their reschedule request has been declined.
                </x-alert>
            </div>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.closeRejectModal()" />
                <x-button label="Reject Request" class="btn-error" wire:click="rejectReschedule" spinner />
            </x-slot:actions>
        @endif
    </x-modal>

    <!-- Reason View Modal -->
    <x-modal wire:model="showReasonModal" title="Reschedule Reason" separator>
        <div class="prose max-w-none">
            <p class="whitespace-pre-line">{{ $selectedReason }}</p>
        </div>

        <x-slot:actions>
            <x-button label="Close" @click="$wire.closeReasonModal()" />
        </x-slot:actions>
    </x-modal>
</div>
