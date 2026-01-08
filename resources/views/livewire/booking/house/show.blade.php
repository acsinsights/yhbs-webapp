<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use Illuminate\View\View;
use Livewire\Volt\Component;
use App\Models\{Booking, Room, House};
use App\Enums\BookingStatusEnum;
use App\Services\WalletService;

new class extends Component {
    use Toast;

    public Booking $booking;
    public bool $showPaymentModal = false;
    public string $payment_status = '';
    public string $payment_method = '';
    public ?float $extra_fee = null;
    public ?string $extra_fee_remark = null;
    public bool $showCancelModal = false;
    public string $cancellation_reason = '';
    public ?float $refund_amount = null;
    public bool $showRescheduleModal = false;
    public ?string $new_date_range = null;
    public ?string $reschedule_notes = null;
    public array $bookedDates = [];
    public bool $showHistoryDrawer = false;
    public $activities = [];
    public float $rescheduleFee = 0;
    public string $paymentMethod = 'wallet'; // 'wallet' or 'manual'
    public int $originalNights = 0;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['bookingable', 'user', 'coupon']);
        $this->payment_status = $booking->payment_status->value;
        $this->payment_method = $booking->payment_method->value;
        $this->extra_fee = $booking->extra_fee;
        $this->extra_fee_remark = $booking->extra_fee_remark;
        $this->originalNights = $booking->check_in->diffInDays($booking->check_out);
        $this->loadBookedDates();

        // Explicitly set modal states to false
        $this->showRescheduleModal = false;
        $this->showPaymentModal = false;
        $this->showCancelModal = false;
    }

    public function updated($property): void
    {
        if ($property === 'showRescheduleModal' && $this->showRescheduleModal) {
            $this->new_date_range = null;
            $this->reschedule_notes = null;
            $this->rescheduleFee = $this->booking->calculateRescheduleFee();
            $this->paymentMethod = 'wallet';
            $this->loadBookedDates();
            $this->dispatch('reinit-datepicker');
        }

        if ($property === 'showHistoryDrawer' && $this->showHistoryDrawer) {
            $this->loadActivities();
        }
    }

    public function loadActivities(): void
    {
        $this->activities = \Spatie\Activitylog\Models\Activity::where('subject_type', get_class($this->booking))
            ->where('subject_id', $this->booking->id)
            ->with('causer')
            ->latest()
            ->get();
    }

    public function loadBookedDates(): void
    {
        // Get booked dates for this house, excluding current booking
        $bookings = Booking::where('bookingable_type', House::class)
            ->where('bookingable_id', $this->booking->bookingable_id)
            ->where('id', '!=', $this->booking->id)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->get(['check_in', 'check_out']);

        $this->bookedDates = $bookings
            ->flatMap(function ($booking) {
                $dates = [];
                $checkIn = new \DateTime($booking->check_in);
                $checkOut = new \DateTime($booking->check_out);

                while ($checkIn < $checkOut) {
                    $dates[] = $checkIn->format('Y-m-d');
                    $checkIn->modify('+1 day');
                }

                return $dates;
            })
            ->unique()
            ->values()
            ->toArray();
    }

    public function checkin(): void
    {
        $this->booking->update([
            'status' => 'checked_in',
        ]);

        $this->success('Booking checked in successfully.');
        $this->booking->refresh();
    }

    public function checkout(): void
    {
        $this->booking->update([
            'status' => 'checked_out',
        ]);

        $this->success('Booking checked out successfully.', redirectTo: route('admin.bookings.house.index'));
    }

    public function updatePayment(): void
    {
        $this->validate([
            'payment_status' => 'required|in:pending,paid,failed',
            'payment_method' => 'required|in:cash,card,online,other',
            'extra_fee' => 'nullable|numeric|min:0',
            'extra_fee_remark' => 'nullable|string|max:500',
        ]);

        $this->booking->update([
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'extra_fee' => $this->extra_fee,
            'extra_fee_remark' => $this->extra_fee_remark,
        ]);

        // Log activity
        activity()
            ->performedOn($this->booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'payment_status' => $this->payment_status,
                'payment_method' => $this->payment_method,
                'extra_fee' => $this->extra_fee,
                'extra_fee_remark' => $this->extra_fee_remark,
            ])
            ->log('Payment details updated');

        $this->showPaymentModal = false;
        $this->success('Payment details updated successfully.');
    }

    public function cancelBooking(): void
    {
        // Prevent cancellation if already checked in or checked out
        if ($this->booking->isCheckedIn() || $this->booking->isCheckedOut()) {
            $this->error('Cannot cancel a booking that is already checked in or checked out.');
            $this->showCancelModal = false;
            return;
        }

        $this->validate([
            'cancellation_reason' => 'required|min:10',
            'refund_amount' => 'nullable|numeric|min:0|max:' . $this->booking->price,
        ]);

        // Update booking with cancellation details
        $this->booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => auth()->id(),
            'cancellation_reason' => $this->cancellation_reason,
            'refund_amount' => $this->refund_amount ?? 0,
            'refund_status' => $this->refund_amount > 0 ? 'completed' : null,
            'notes' => ($this->booking->notes ? $this->booking->notes . "\n\n" : '') . 'Admin Cancellation: ' . $this->cancellation_reason,
        ]);

        // Add refund to customer's wallet if amount is specified
        if ($this->refund_amount && $this->refund_amount > 0) {
            try {
                $walletService = app(\App\Services\WalletService::class);
                $walletService->addCredit($this->booking->user, (float) $this->refund_amount, $this->booking, "Refund for cancelled booking #{$this->booking->booking_id}", 'admin_cancellation');

                // Refresh user to get updated wallet balance
                $this->booking->user->refresh();

                $refundMessage = ' Refund of ' . currency_format($this->refund_amount) . " credited to {$this->booking->user->name}'s wallet. New Balance: " . currency_format($this->booking->user->wallet_balance) . '.';
            } catch (\Exception $e) {
                \Log::error('Wallet refund failed: ' . $e->getMessage());
                $refundMessage = ' WARNING: Refund processing failed. Please add manually.';
            }
        } else {
            $refundMessage = '';
        }

        // Log activity
        activity()
            ->performedOn($this->booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'cancellation_reason' => $this->cancellation_reason,
                'refund_amount' => $this->refund_amount,
            ])
            ->log('Booking cancelled by admin');

        $this->showCancelModal = false;
        $this->success('Booking cancelled successfully.' . $refundMessage, redirectTo: route('admin.bookings.house.index'));
    }

    public function rescheduleBooking(): void
    {
        $this->validate([
            'new_date_range' => 'required|string',
            'reschedule_notes' => 'nullable|string|min:3',
            'rescheduleFee' => 'required|numeric|min:0',
            'paymentMethod' => 'required|in:wallet,manual',
        ]);

        // Parse date range (format: "2025-01-15 to 2025-01-20")
        $dates = explode(' to ', $this->new_date_range);

        if (count($dates) !== 2) {
            $this->error('Please select both check-in and check-out dates.');
            return;
        }

        $newCheckIn = Carbon::parse(trim($dates[0]));
        $newCheckOut = Carbon::parse(trim($dates[1]));

        // Validate dates
        if ($newCheckIn->isBefore(Carbon::today())) {
            $this->error('Check-in date cannot be in the past.');
            return;
        }

        if ($newCheckOut->isBefore($newCheckIn)) {
            $this->error('Check-out date must be after check-in date.');
            return;
        }

        // Validate that the number of nights remains the same
        $newNights = $newCheckIn->diffInDays($newCheckOut);
        if ($newNights !== $this->originalNights) {
            $this->error("Booking must maintain the same duration. Original: {$this->originalNights} nights, Selected: {$newNights} nights.");
            return;
        }

        // Check if any of the requested dates are already booked
        $requestedDates = [];
        $current = $newCheckIn->copy();
        while ($current->lt($newCheckOut)) {
            $requestedDates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        $conflictingDates = array_intersect($requestedDates, $this->bookedDates);

        if (!empty($conflictingDates)) {
            $this->error('The selected dates conflict with existing bookings. Please choose different dates.');
            return;
        }

        // Check if there are any confirmed bookings for the requested dates (excluding current booking)
        $hasConflict = Booking::where('bookingable_type', House::class)
            ->where('bookingable_id', $this->booking->bookingable_id)
            ->where('id', '!=', $this->booking->id)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->where(function ($query) use ($newCheckIn, $newCheckOut) {
                $query
                    ->whereBetween('check_in', [$newCheckIn, $newCheckOut])
                    ->orWhereBetween('check_out', [$newCheckIn, $newCheckOut])
                    ->orWhere(function ($q) use ($newCheckIn, $newCheckOut) {
                        $q->where('check_in', '<=', $newCheckIn)->where('check_out', '>=', $newCheckOut);
                    });
            })
            ->exists();

        if ($hasConflict) {
            $this->error('The selected dates are already booked by another customer. Please choose different dates.');
            return;
        }

        $user = $this->booking->user;
        $walletBalance = $user->wallet_balance ?? 0;

        // Validate wallet balance if wallet payment is selected
        if ($this->paymentMethod === 'wallet' && $this->rescheduleFee > 0) {
            if ($walletBalance < $this->rescheduleFee) {
                $this->error('Customer does not have sufficient wallet balance (' . currency_format($walletBalance) . '). Please select "Collect Manually" or reduce the fee.');
                return;
            }
        }

        // Update booking with new dates
        $oldCheckIn = $this->booking->check_in->format('M d, Y');
        $oldCheckOut = $this->booking->check_out->format('M d, Y');

        // Use database transaction
        \DB::transaction(function () use ($user, $newCheckIn, $newCheckOut, $oldCheckIn, $oldCheckOut) {
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
                    'description' => 'Reschedule fee for booking #' . $this->booking->booking_id,
                    'booking_id' => $this->booking->id,
                ]);
            }

            $this->booking->update([
                'check_in' => $newCheckIn,
                'check_out' => $newCheckOut,
                'reschedule_fee' => $this->rescheduleFee,
                'rescheduled_by' => auth()->id(),
            ]);

            // Log activity
            $description = "Rescheduled from {$oldCheckIn} - {$oldCheckOut} to {$newCheckIn->format('M d, Y')} - {$newCheckOut->format('M d, Y')}";
            if ($this->reschedule_notes) {
                $description .= ". Reason: {$this->reschedule_notes}";
            }
            if ($this->rescheduleFee > 0) {
                $description .= '. Reschedule fee: ' . currency_format($this->rescheduleFee) . ' (' . ($this->paymentMethod === 'wallet' ? 'Deducted from wallet' : 'To be collected manually') . ')';
            }

            activity()
                ->performedOn($this->booking)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old_check_in' => $oldCheckIn,
                    'old_check_out' => $oldCheckOut,
                    'new_check_in' => $newCheckIn->format('M d, Y'),
                    'new_check_out' => $newCheckOut->format('M d, Y'),
                    'reschedule_fee' => $this->rescheduleFee,
                    'payment_method' => $this->paymentMethod,
                    'notes' => $this->reschedule_notes,
                ])
                ->log($description);

            // Create notification for customer
            \App\Models\UserNotification::create([
                'user_id' => $this->booking->user_id,
                'type' => 'booking_rescheduled',
                'title' => 'Booking Rescheduled',
                'message' => "Your booking #{$this->booking->booking_id} has been rescheduled from {$oldCheckIn} - {$oldCheckOut} to {$newCheckIn->format('M d, Y')} - {$newCheckOut->format('M d, Y')}." . ($this->rescheduleFee > 0 ? ' Reschedule fee: ' . currency_format($this->rescheduleFee) : ''),
                'data' => [
                    'booking_id' => $this->booking->id,
                    'old_check_in' => $oldCheckIn,
                    'old_check_out' => $oldCheckOut,
                    'new_check_in' => $newCheckIn->format('M d, Y'),
                    'new_check_out' => $newCheckOut->format('M d, Y'),
                    'reschedule_fee' => $this->rescheduleFee,
                    'payment_method' => $this->paymentMethod,
                    'notes' => $this->reschedule_notes,
                ],
            ]);
        });

        $successMessage = 'Booking rescheduled successfully.';
        if ($this->paymentMethod === 'wallet' && $this->rescheduleFee > 0) {
            $successMessage .= ' Fee has been deducted from customer wallet.';
        } elseif ($this->paymentMethod === 'manual' && $this->rescheduleFee > 0) {
            $successMessage .= ' Fee will be collected manually from customer.';
        }

        $this->showRescheduleModal = false;
        $this->success($successMessage);
        $this->booking->refresh();
        $this->loadBookedDates();
    }

    public function rendering(View $view)
    {
        $view->booking = $this->booking;
    }
}; ?>

@section('cdn')
    {{-- Flatpickr  --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        document.addEventListener('livewire:initialized', () => {
            // Prevent auto-opening of datepicker when modal opens
            Livewire.hook('morph.updated', ({
                el,
                component
            }) => {
                setTimeout(() => {
                    const datepickerInputs = document.querySelectorAll(
                        '[wire\\:model\\.live="new_date_range"]');
                    datepickerInputs.forEach(input => {
                        if (input && input._flatpickr) {
                            // Close if auto-opened
                            input._flatpickr.close();
                            // Blur the input
                            input.blur();
                        }
                    });
                }, 50);
            });
        });
    </script>
@endsection

<div>
    @php
        $breadcrumbs = [
            [
                'link' => route('admin.index'),
                'icon' => 's-home',
            ],
            [
                'link' => route('admin.bookings.house.index'),
                'label' => 'Room Bookings',
            ],
            [
                'label' => 'Booking Details',
            ],
        ];
    @endphp

    <x-header title="Booking Details" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">View booking information</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.house.index') }}"
                class="btn-ghost btn-outline" />

            @if (
                $booking->status === \App\Enums\BookingStatusEnum::BOOKED &&
                    (now()->isSameDay($booking->check_in) || now()->isAfter($booking->check_in)))
                <x-button icon="o-arrow-right-end-on-rectangle" label="Check In" wire:click="checkin"
                    wire:confirm="Are you sure you want to check in this booking?" class="btn-info" spinner="checkin" />
            @elseif ($booking->canCheckOut())
                <x-button icon="o-arrow-right-start-on-rectangle" label="Check Out" wire:click="checkout"
                    wire:confirm="Are you sure you want to checkout this booking?" class="btn-success"
                    spinner="checkout" />
            @endif

            <x-dropdown icon="o-ellipsis-vertical" class="btn-ghost btn-outline btn-circle">
                <x-menu-item title="History" icon="o-clock" wire:click.stop="$set('showHistoryDrawer', true)" />
                @if ($booking->isCheckedIn() || $booking->isCheckedOut())
                    <x-menu-item title="Download Receipt" icon="o-arrow-down-tray"
                        @click="window.location.href='{{ route('admin.booking.download-receipt', $booking->id) }}'" />
                    <x-menu-separator />
                @endif
                @if ($booking->status === \App\Enums\BookingStatusEnum::BOOKED || $booking->canBeEdited())
                    <x-menu-item title="Edit" icon="o-pencil"
                        link="{{ route('admin.bookings.house.edit', $booking->id) }}" />
                    <x-menu-item title="Reschedule" icon="o-calendar"
                        wire:click.stop="$set('showRescheduleModal', true)" />
                    <x-menu-separator />
                @endif
                @if (
                    !$booking->isCheckedIn() &&
                        !$booking->isCheckedOut() &&
                        $booking->status !== \App\Enums\BookingStatusEnum::CANCELLED)
                    <x-menu-item title="Cancel Booking" icon="o-x-circle"
                        wire:click.stop="$set('showCancelModal', true)" class="text-error" />
                @endif
            </x-dropdown>
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Booking Information --}}
            <x-card shadow>
                <x-slot:title>
                    <div class="flex items-center gap-2">
                        <x-icon name="o-document-text" class="w-5 h-5" />
                        <span>Booking Information</span>
                    </div>
                </x-slot:title>
                <x-slot:menu>
                    <x-badge :value="$booking->status->label()" class="{{ $booking->status->badgeColor() }}" />
                </x-slot:menu>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Booking ID</div>
                            <div class="font-semibold">#{{ $booking->booking_id }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Booking Date</div>
                            <div class="font-semibold">{{ $booking->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                    </div>

                    @if ($booking->check_in)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Check In</div>
                                <div class="font-semibold">
                                    {{ $booking->check_in->format('M d, Y') }}
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Check Out</div>
                                <div class="font-semibold">
                                    {{ $booking->check_out->format('M d, Y') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($booking->check_in && $booking->check_out)
                        @php
                            $checkIn = $booking->check_in;
                            $checkOut = $booking->check_out;
                            $days = round($checkIn->diffInDays($checkOut));
                            $durationText = $days . ' ' . ($days === 1 ? 'night' : 'nights');
                        @endphp
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Duration</div>
                            <div class="font-semibold">{{ $durationText }}</div>
                        </div>
                    @endif

                    @if ($booking->arrival_time)
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Arrival Time</div>
                            <div class="font-semibold">{{ $booking->arrival_time }}</div>
                        </div>
                    @endif

                    @if ($booking->trip_type)
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Trip Type</div>
                            <div class="font-semibold">{{ ucfirst($booking->trip_type) }}</div>
                        </div>
                    @endif

                    @if ($booking->notes)
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Notes</div>
                            <div class="text-sm">{{ strip_tags($booking->notes) }}</div>
                        </div>
                    @endif

                    {{-- Guest Details --}}
                    @if ($booking->guest_details)
                        <div class="mt-4 border-t pt-4">
                            <div class="text-sm font-semibold text-base-content/80 mb-3">
                                <x-icon name="o-users" class="w-4 h-4 inline mr-1" />
                                Guest Information
                            </div>
                            @if (is_array($booking->guest_details))
                                <div class="space-y-3">
                                    @foreach ($booking->guest_details as $key => $value)
                                        @php
                                            // Skip empty children_names and special_requests
                                            $skipEmpty = in_array($key, ['children_names', 'special_requests']);
                                            if ($skipEmpty) {
                                                if (
                                                    is_array($value) &&
                                                    (empty($value) || count(array_filter($value)) === 0)
                                                ) {
                                                    continue;
                                                }
                                                if (is_string($value) && empty(trim($value))) {
                                                    continue;
                                                }
                                            }
                                        @endphp
                                        @if (is_array($value))
                                            <div class="bg-base-200/50 p-3 rounded-lg">
                                                <div class="font-semibold text-xs uppercase text-primary mb-2">
                                                    {{ str_replace('_', ' ', ucfirst($key)) }}
                                                </div>
                                                <div class="space-y-1">
                                                    @if ($key === 'customer')
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                                            @foreach ($value as $subKey => $subValue)
                                                                @if (!is_array($subValue))
                                                                    <div class="text-xs">
                                                                        <span
                                                                            class="text-base-content/60">{{ ucwords(str_replace('_', ' ', $subKey)) }}:</span>
                                                                        <span
                                                                            class="font-medium">{{ $subValue }}</span>
                                                                    </div>
                                                                @endif
                                                            @endforeach
                                                        </div>
                                                    @elseif (in_array($key, ['adult_names', 'children_names']))
                                                        @if (!empty($value) && count(array_filter($value)) > 0)
                                                            <div class="flex flex-wrap gap-2">
                                                                @foreach ($value as $index => $name)
                                                                    @if (!empty($name))
                                                                        <x-badge value="{{ $name }}"
                                                                            class="badge-soft {{ $key === 'adult_names' ? 'badge-primary' : 'badge-secondary' }}" />
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    @else
                                                        @foreach ($value as $subKey => $subValue)
                                                            @if (!is_array($subValue))
                                                                <div class="text-xs">
                                                                    <span
                                                                        class="text-base-content/60">{{ ucfirst($subKey) }}:</span>
                                                                    <span
                                                                        class="font-medium">{{ $subValue }}</span>
                                                                </div>
                                                            @else
                                                                <div class="text-xs">
                                                                    <span
                                                                        class="text-base-content/60">{{ ucfirst($subKey) }}:</span>
                                                                    <span
                                                                        class="font-medium">{{ implode(', ', $subValue) }}</span>
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <div class="bg-base-200/50 p-3 rounded-lg">
                                                <div class="text-xs">
                                                    <span
                                                        class="text-base-content/60 font-medium">{{ ucwords(str_replace('_', ' ', $key)) }}:</span>
                                                    <div class="mt-1 text-sm">{{ $value }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- House Information --}}
            @if ($booking->bookingable)
                <x-card shadow>
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-home-modern" class="w-5 h-5" />
                            <span>House Information</span>
                        </div>
                    </x-slot:title>

                    <div class="space-y-4">
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">House Name</div>
                            <div class="font-semibold text-lg">{{ $booking->bookingable->name }}</div>
                        </div>
                        @if ($booking->bookingable->house_number)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">House Number</div>
                                <div class="font-semibold">{{ $booking->bookingable->house_number }}</div>
                            </div>
                        @endif
                        @if ($booking->bookingable->number_of_rooms)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Number of Rooms</div>
                                <div class="font-semibold">{{ $booking->bookingable->number_of_rooms }}</div>
                            </div>
                        @endif
                        <div class="grid grid-cols-2 gap-4">
                            @if ($booking->bookingable->adults)
                                <div>
                                    <div class="text-sm text-base-content/50 mb-1">Max Adults</div>
                                    <x-badge :value="$booking->bookingable->adults" class="badge-soft badge-primary" />
                                </div>
                            @endif
                            @if ($booking->bookingable->children)
                                <div>
                                    <div class="text-sm text-base-content/50 mb-1">Max Children</div>
                                    <x-badge :value="$booking->bookingable->children" class="badge-soft badge-secondary" />
                                </div>
                            @endif
                        </div>
                        @if ($booking->bookingable->description)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Description</div>
                                <div class="text-sm">{{ strip_tags($booking->bookingable->description) }}</div>
                            </div>
                        @endif
                    </div>
                </x-card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Customer Information --}}
            <x-card shadow>
                <x-slot:title>
                    <div class="flex items-center gap-2">
                        <x-icon name="o-user" class="w-5 h-5" />
                        <span>Customer</span>
                    </div>
                </x-slot:title>

                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Name</div>
                        <div class="font-semibold">{{ $booking->user->name ?? 'N/A' }}</div>
                    </div>

                    @if ($booking->user)
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Email</div>
                            <div class="text-sm">{{ $booking->user->email }}</div>
                        </div>
                        @if ($booking->user->phone)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Phone</div>
                                <div class="text-sm">{{ $booking->user->phone }}</div>
                            </div>
                        @endif
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Adults</div>
                            <x-badge :value="$booking->adults ?? 0" class="badge-soft badge-primary" />
                        </div>

                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Children</div>
                            <x-badge :value="$booking->children ?? 0" class="badge-soft badge-secondary" />
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Payment Information --}}
            <x-card shadow>
                <x-slot:title>
                    <div class="flex items-center gap-2">
                        <x-icon name="o-currency-dollar" class="w-5 h-5" />
                        <span>Payment</span>
                    </div>
                </x-slot:title>
                <x-slot:menu>
                    @if ($booking->canBeEdited())
                        <x-button icon="o-pencil" label="Update" wire:click="$set('showPaymentModal', true)"
                            class="btn-ghost btn-sm" />
                    @endif
                </x-slot:menu>

                <div class="space-y-4">
                    {{-- Payment Breakdown --}}
                    <div class="p-4 bg-base-200 rounded-lg space-y-2">
                        <h3 class="text-sm font-semibold text-base-content/70 mb-3">Payment Breakdown</h3>

                        @if ($booking->price_per_night && $booking->nights)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-base-content/60">Price per Night</span>
                                <span class="font-medium">{{ currency_format($booking->price_per_night) }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-base-content/60">Number of Nights</span>
                                <span class="font-medium">{{ $booking->nights }}</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-base-content/60">Subtotal</span>
                                <span
                                    class="font-medium">{{ currency_format($booking->price_per_night * $booking->nights) }}</span>
                            </div>
                        @else
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-base-content/60">Base Amount</span>
                                <span class="font-medium">{{ currency_format($booking->price ?? 0) }}</span>
                            </div>
                        @endif

                        @if ($booking->service_fee && $booking->service_fee > 0)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-base-content/60">Service Fee</span>
                                <span class="font-medium">{{ currency_format($booking->service_fee) }}</span>
                            </div>
                        @endif

                        @if ($booking->tax && $booking->tax > 0)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-base-content/60">Tax</span>
                                <span class="font-medium">{{ currency_format($booking->tax) }}</span>
                            </div>
                        @endif

                        @if ($booking->reschedule_fee && $booking->reschedule_fee > 0)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-base-content/60">Reschedule Fee</span>
                                <span
                                    class="font-medium text-warning">{{ currency_format($booking->reschedule_fee) }}</span>
                            </div>
                        @endif

                        @if ($booking->extra_fee && $booking->extra_fee > 0)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-base-content/60">Extra Fee</span>
                                <span class="font-medium text-info">{{ currency_format($booking->extra_fee) }}</span>
                            </div>
                            @if ($booking->extra_fee_remark)
                                <div class="text-xs text-base-content/50 italic ml-2">
                                    <x-icon name="o-information-circle" class="w-3 h-3 inline" />
                                    {{ $booking->extra_fee_remark }}
                                </div>
                            @endif
                        @endif

                        @if ($booking->discount_amount && $booking->discount_amount > 0)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-base-content/60">Discount
                                    @if ($booking->coupon)
                                        ({{ $booking->coupon->code }})
                                    @endif
                                </span>
                                <span
                                    class="font-medium text-success">-{{ currency_format($booking->discount_amount) }}</span>
                            </div>
                        @endif

                        @if ($booking->wallet_amount_used && $booking->wallet_amount_used > 0)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-base-content/60">Wallet Used</span>
                                <span
                                    class="font-medium text-secondary">-{{ currency_format($booking->wallet_amount_used) }}</span>
                            </div>
                        @endif

                        <div class="divider my-2"></div>

                        <div class="flex justify-between items-center">
                            <span class="text-base font-semibold">Total Amount</span>
                            @php
                                // booking->price already has discount applied
                                // Final amount = price (after discount) - wallet + fees
                                $calculatedTotal =
                                    $booking->price +
                                    ($booking->reschedule_fee ?? 0) +
                                    ($booking->extra_fee ?? 0) -
                                    ($booking->wallet_amount_used ?? 0);
                            @endphp
                            <span class="text-2xl font-bold">{{ currency_format($calculatedTotal) }}</span>
                        </div>
                    </div>

                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Payment Method</div>
                        <x-badge :value="$booking->payment_method->label()" class="{{ $booking->payment_method->badgeColor() }}" />
                    </div>
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Payment Status</div>
                        <x-badge :value="$booking->payment_status->label()" class="{{ $booking->payment_status->badgeColor() }}" />
                    </div>

                    @if ($booking->coupon)
                        <div class="mt-4 p-3 bg-success/10 rounded-lg border border-success/20">
                            <div class="flex items-center gap-2 mb-2">
                                <x-icon name="o-ticket" class="w-4 h-4 text-success" />
                                <span class="text-sm font-semibold text-success">Coupon Applied</span>
                            </div>
                            <div class="space-y-1 text-xs">
                                <div><strong>Code:</strong> {{ $booking->coupon->code }}</div>
                                @if ($booking->coupon->description)
                                    <div><strong>Description:</strong> {{ $booking->coupon->description }}</div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>

    {{-- Payment Update Modal --}}
    <x-modal wire:model="showPaymentModal" title="Update Payment Details" class="backdrop-blur">
        <div class="space-y-4">
            <x-select label="Payment Status" wire:model="payment_status" :options="[
                ['id' => 'pending', 'name' => 'Pending'],
                ['id' => 'paid', 'name' => 'Paid'],
                ['id' => 'failed', 'name' => 'Failed'],
            ]" icon="o-credit-card" />

            <x-select label="Payment Method" wire:model="payment_method" :options="[
                ['id' => 'cash', 'name' => 'Cash'],
                ['id' => 'card', 'name' => 'Card'],
                ['id' => 'online', 'name' => 'Online'],
                ['id' => 'other', 'name' => 'Other'],
            ]" icon="o-banknotes" />

            <x-input label="Extra Fee (Optional)" wire:model="extra_fee" type="number" step="0.01"
                min="0" icon="o-currency-dollar" hint="Additional charges if any" />

            <x-textarea label="Extra Fee Remark (Optional)" wire:model="extra_fee_remark"
                placeholder="Reason for extra fee..." rows="3" hint="Explain why extra fee is charged" />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showPaymentModal = false" />
            <x-button label="Update" wire:click="updatePayment" class="btn-primary" spinner="updatePayment" />
        </x-slot:actions>
    </x-modal>

    {{-- Cancel Booking Modal --}}
    <x-modal wire:model="showCancelModal" title="Cancel Booking" class="backdrop-blur">
        <div class="space-y-4">
            <x-alert title="Warning!"
                description="This action cannot be undone. Please provide a reason for cancellation."
                icon="o-exclamation-triangle" class="alert-warning" />

            <x-textarea label="Cancellation Reason" wire:model="cancellation_reason"
                placeholder="Please provide a detailed reason for cancellation..." rows="4"
                hint="Minimum 10 characters required" />

            <x-input label="Refund Amount (Optional)" wire:model="refund_amount" type="number" step="0.01"
                min="0" max="{{ $booking->price }}" icon="o-currency-dollar"
                hint="Enter amount to refund to customer's wallet. Max: {{ $booking->price }}" />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showCancelModal = false" />
            <x-button label="Confirm Cancellation" wire:click="cancelBooking" class="btn-error"
                spinner="cancelBooking" />
        </x-slot:actions>
    </x-modal>

    {{-- Reschedule Booking Drawer --}}
    <x-drawer wire:model="showRescheduleModal" title="Reschedule Booking" class="w-11/12 lg:w-1/3" right>
        <div class="space-y-4">
            <x-alert title="Current Booking Dates"
                description="Check-in: {{ $booking->check_in->format('M d, Y') }} | Check-out: {{ $booking->check_out->format('M d, Y') }} | Duration: {{ $originalNights }} {{ Str::plural('night', $originalNights) }}"
                icon="o-information-circle" class="alert-info" />

            <x-alert title="Important"
                description="The booking duration must remain {{ $originalNights }} {{ Str::plural('night', $originalNights) }}. When you select a new check-in date, the check-out date will automatically be set to maintain the same duration."
                icon="o-exclamation-triangle" class="alert-warning" />

            <div wire:key="reschedule-datepicker-{{ $booking->id }}">
                <x-datepicker label="Select New Date Range (Check-in to Check-out)" wire:model.live="new_date_range"
                    icon="o-calendar" :config="[
                        'mode' => 'range',
                        'dateFormat' => 'Y-m-d',
                        'altInput' => true,
                        'altFormat' => 'M d, Y',
                        'minDate' => 'today',
                        'disable' => $bookedDates,
                        'conjunction' => ' to ',
                        'allowInput' => false,
                        'clickOpens' => true,
                    ]" />
                <p class="text-xs text-base-content/60 mt-1">📅 Select {{ $originalNights }}-night date range. The
                    booking duration must remain the same. Red dates are already booked.</p>
            </div>

            <x-textarea label="Reason for Rescheduling (Optional)" wire:model="reschedule_notes"
                placeholder="Enter reason for rescheduling..." rows="3"
                hint="Provide context for the date change" />

            <div class="divider">Reschedule Fee</div>

            <x-input label="Reschedule Fee" wire:model="rescheduleFee" type="number" step="0.01" min="0"
                icon="o-currency-dollar" hint="Fee will be charged to customer for rescheduling" />

            <x-alert title="Customer Wallet Balance"
                description="Current balance: {{ currency_format($booking->user->wallet_balance ?? 0) }}"
                icon="o-wallet" class="alert-info" />

            <x-radio label="Payment Method" :options="[
                ['id' => 'wallet', 'name' => 'Deduct from Wallet'],
                ['id' => 'manual', 'name' => 'Collect Manually'],
            ]" wire:model="paymentMethod" />

            @if ($paymentMethod === 'wallet' && $rescheduleFee > 0)
                <x-alert title="Wallet Deduction"
                    description="The reschedule fee will be automatically deducted from customer's wallet balance."
                    icon="o-information-circle" class="alert-warning" />
            @elseif ($paymentMethod === 'manual' && $rescheduleFee > 0)
                <x-alert title="Manual Collection"
                    description="You will need to collect the reschedule fee manually from the customer."
                    icon="o-information-circle" class="alert-info" />
            @endif

            @if (count($bookedDates) > 0)
                <x-alert title="Booked Dates Info"
                    description="Red highlighted dates in the calendar are already booked and cannot be selected."
                    icon="o-exclamation-triangle" class="alert-warning" />
            @else
                <x-alert title="No Conflicts" description="All dates are currently available for this house."
                    icon="o-check-circle" class="alert-success" />
            @endif
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showRescheduleModal = false" />
            <x-button label="Confirm Reschedule" wire:click="rescheduleBooking" class="btn-primary"
                spinner="rescheduleBooking" />
        </x-slot:actions>
    </x-drawer>

    {{-- Activity History Drawer --}}
    @include('livewire.booking.partials.activity-history-drawer')
</div>
