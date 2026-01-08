<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Booking;
use App\Enums\BookingStatusEnum;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingCancellationApprovedMail;
use App\Mail\BookingCancellationRejectedMail;
use App\Notifications\BookingStatusNotification;
use Mary\Traits\Toast;

new class extends Component {
    use WithPagination, Toast;

    public bool $showApproveModal = false;
    public bool $showRejectModal = false;
    public bool $showReasonModal = false;
    public bool $showDetailsModal = false;
    public ?Booking $selectedBooking = null;
    public ?int $lastViewedBookingId = null;
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

    public function openDetailsModal($bookingId): void
    {
        $this->selectedBooking = Booking::with(['user', 'bookingable'])->findOrFail($bookingId);
        $this->lastViewedBookingId = $bookingId;
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
        $this->refundAmount = $this->selectedBooking->total_amount ?? ($this->selectedBooking->price ?? 0);
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

    public function getBookingDuration($booking): string
    {
        if (!$booking->check_in || !$booking->check_out) {
            return 'N/A';
        }

        // Calculate duration in hours
        $duration = $booking->check_in->diffInHours($booking->check_out);

        if ($duration === 0) {
            $minutes = $booking->check_in->diffInMinutes($booking->check_out);
            if ($minutes === 15) {
                return '15 Minutes';
            } elseif ($minutes === 30) {
                return '30 Minutes';
            } elseif ($minutes < 60) {
                return $minutes . ' Minutes';
            }
            $duration = 1;
        }

        return $duration === 1 ? '1 Hour' : $duration . ' Hours';
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
        $maxRefund = $this->selectedBooking->total_amount ?? ($this->selectedBooking->price ?? 0);

        $this->validate(
            [
                'refundAmount' => ['required', 'numeric', 'min:0', 'max:' . $maxRefund],
            ],
            [
                'refundAmount.max' => 'Refund amount cannot exceed the amount paid by customer (' . currency_format($maxRefund) . ')',
            ],
        );

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

        // Send notification to customer
        if ($this->selectedBooking->user) {
            $this->selectedBooking->user->notify(new BookingStatusNotification($this->selectedBooking, 'cancellation_approved', ['refund_amount' => $this->refundAmount]));
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

        // Send notification to customer
        if ($this->selectedBooking->user) {
            $this->selectedBooking->user->notify(new BookingStatusNotification($this->selectedBooking, 'cancellation_rejected', ['rejection_reason' => $this->rejectionReason]));
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
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'property', 'label' => 'Property'],
                ['key' => 'amount', 'label' => 'Amount'],
                ['key' => 'requested', 'label' => 'Requested On'],
                ['key' => 'view', 'label' => ''],
                ['key' => 'actions', 'label' => 'Actions'],
            ]" :rows="$cancellationRequests" with-pagination>
                @scope('cell_customer', $booking)
                    @php
                        $bookingType = match (true) {
                            $booking->bookingable instanceof \App\Models\House => 'house',
                            $booking->bookingable instanceof \App\Models\Room => 'room',
                            $booking->bookingable instanceof \App\Models\Boat => 'boat',
                            default => 'house',
                        };
                        $detailsRoute = route('admin.bookings.' . $bookingType . '.show', $booking->id);
                    @endphp
                    <div>
                        <a href="{{ $detailsRoute }}" class="font-semibold text-primary hover:underline">
                            {{ $booking->user->name ?? 'N/A' }}
                        </a>
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

                @scope('cell_amount', $booking)
                    <strong class="text-primary">{{ currency_format($booking->total ?? ($booking->price ?? 0)) }}</strong>
                @endscope

                @scope('cell_requested', $booking)
                    <div>
                        <div class="text-sm">{{ $booking->cancellation_requested_at->format('d M Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $booking->cancellation_requested_at->format('H:i A') }}</div>
                    </div>
                @endscope

                @scope('cell_view', $booking)
                    <x-button icon="o-eye"
                        class="btn-sm btn-ghost btn-circle {{ $this->lastViewedBookingId === $booking->id ? 'ring-2 ring-success' : '' }}"
                        wire:click="openDetailsModal({{ $booking->id }})" tooltip="View Details" />
                @endscope

                @scope('cell_actions', $booking)
                    <div class="flex gap-2">
                        <x-button icon="o-check-circle" class="btn-sm btn-success"
                            wire:click="openApproveModal({{ $booking->id }})" tooltip="Approve" />
                        <x-button icon="o-x-circle" class="btn-sm btn-error"
                            wire:click="openRejectModal({{ $booking->id }})" tooltip="Reject" />
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

    <!-- Details Modal -->
    <x-modal wire:model="showDetailsModal" title="Cancellation Request Details" class="backdrop-blur"
        box-class="max-w-7xl w-full mx-4">
        @if ($selectedBooking)
            <div class="space-y-3 max-h-[80vh] overflow-y-auto px-1">
                <!-- Booking Information -->
                <div>
                    <h3 class="font-semibold text-base mb-2 flex items-center gap-1.5">
                        <x-icon name="o-document-text" class="w-4 h-4" />
                        Booking Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 p-3 bg-base-200 rounded-lg">
                        <div>
                            <span class="text-sm text-gray-500">Booking ID</span>
                            <p class="font-semibold">#{{ $selectedBooking->id }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Property</span>
                            <p class="font-semibold">{{ $selectedBooking->bookingable->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ class_basename($selectedBooking->bookingable_type) }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Customer</span>
                            <p class="font-semibold">{{ $selectedBooking->user->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $selectedBooking->user->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Guest Name</span>
                            <p class="font-semibold">{{ $selectedBooking->guest_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Wallet Balance</span>
                            <p class="font-semibold text-info">
                                {{ currency_format($selectedBooking->user->wallet_balance ?? 0) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Current Booking Details -->
                <div>
                    <h3 class="font-semibold text-base mb-2 flex items-center gap-1.5">
                        <x-icon name="o-calendar" class="w-4 h-4 text-primary" />
                        Booking Details
                    </h3>
                    <div class="p-3 bg-base-200 rounded-lg">
                        @if ($selectedBooking->bookingable instanceof \App\Models\Boat)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <span class="text-sm text-gray-500">Date</span>
                                    <p class="font-semibold">{{ $selectedBooking->check_in->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Duration</span>
                                    <p class="font-semibold">{{ $this->getBookingDuration($selectedBooking) }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Time</span>
                                    <p class="font-semibold">{{ $selectedBooking->check_in->format('h:i A') }} -
                                        {{ $selectedBooking->check_out->format('h:i A') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <span class="text-sm text-gray-500">Check-in</span>
                                    <p class="font-semibold">{{ $selectedBooking->check_in->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Check-out</span>
                                    <p class="font-semibold">{{ $selectedBooking->check_out->format('d M Y') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Cancellation Details -->
                <div>
                    <h3 class="font-semibold text-base mb-2 flex items-center gap-1.5">
                        <x-icon name="o-information-circle" class="w-4 h-4" />
                        Request Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="p-3 bg-base-200 rounded-lg">
                            <span class="text-sm text-gray-500">Booking Amount</span>
                            <p class="font-semibold text-primary text-lg">
                                {{ currency_format($selectedBooking->total ?? ($selectedBooking->price ?? 0)) }}</p>
                        </div>
                        <div class="p-3 bg-base-200 rounded-lg">
                            <span class="text-sm text-gray-500">Requested On</span>
                            <p class="font-semibold">
                                {{ $selectedBooking->cancellation_requested_at?->format('d M Y, h:i A') ?? 'N/A' }}</p>
                        </div>
                        @if ($selectedBooking->cancellation_reason)
                            <div class="p-3 bg-base-200 rounded-lg md:col-span-2">
                                <span class="text-sm text-gray-500 block mb-1">Reason for Cancellation</span>
                                <p class="whitespace-pre-line text-sm">{{ $selectedBooking->cancellation_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Close" @click="$wire.closeDetailsModal()" />
            </x-slot:actions>
        @endif
    </x-modal>
</div>
