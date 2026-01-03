<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use App\Models\{Booking, Boat};
use App\Services\WalletService;

new class extends Component {
    use Toast;

    public Booking $booking;
    public bool $showPaymentModal = false;
    public string $payment_status = '';
    public string $payment_method = '';
    public ?float $extra_fee = null;
    public ?string $extra_fee_remark = null;
    public bool $showStatusModal = false;
    public string $booking_status = '';
    public bool $showCancelModal = false;
    public string $cancellation_reason = '';
    public ?float $refund_amount = null;
    public bool $showRescheduleModal = false;
    public ?string $new_check_in = null;
    public ?string $new_time_slot = null;
    public ?string $reschedule_notes = null;
    public bool $showHistoryDrawer = false;
    public $activities = [];
    public ?string $reschedule_duration = null;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['bookingable', 'user']);

        if ($booking->bookingable_type !== Boat::class) {
            $this->error('Invalid booking type.', redirectTo: route('admin.bookings.boat.index'));
            return;
        }

        $this->payment_status = $booking->payment_status->value;
        $this->payment_method = $booking->payment_method->value;
        $this->extra_fee = $booking->extra_fee;
        $this->extra_fee_remark = $booking->extra_fee_remark;
        $this->booking_status = $booking->status->value;

        // Extract duration from notes
        if ($booking->notes && preg_match('/Duration\/Slot: (\d+)h/', $booking->notes, $matches)) {
            $this->reschedule_duration = $matches[1] . 'h';
        } elseif ($booking->check_in && $booking->check_out) {
            $hours = $booking->check_in->diffInHours($booking->check_out);
            $this->reschedule_duration = $hours . 'h';
        }

        // Explicitly set modal states to false
        $this->showRescheduleModal = false;
        $this->showPaymentModal = false;
        $this->showCancelModal = false;
    }

    public function updated($property): void
    {
        if ($property === 'showRescheduleModal' && $this->showRescheduleModal) {
            $this->new_check_in = null;
            $this->new_time_slot = null;
            $this->reschedule_notes = null;
        }

        if ($property === 'showHistoryDrawer' && $this->showHistoryDrawer) {
            $this->loadActivities();
        }

        // Reset selected time slot when date changes
        if ($property === 'new_check_in') {
            $this->new_time_slot = null;
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

    public function updatePayment(): void
    {
        $this->validate([
            'payment_status' => 'required|in:pending,paid',
            'payment_method' => 'required|in:cash,card,online,other',
            'extra_fee' => 'nullable|numeric|min:0',
            'extra_fee_remark' => 'nullable|string|max:500',
        ]);

        $oldPaymentStatus = $this->booking->payment_status->value;
        $this->booking->update([
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
            'extra_fee' => $this->extra_fee,
            'extra_fee_remark' => $this->extra_fee_remark,
        ]);

        // Log payment update
        activity()
            ->performedOn($this->booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'payment_status' => $this->payment_status,
                'payment_method' => $this->payment_method,
                'extra_fee' => $this->extra_fee,
                'extra_fee_remark' => $this->extra_fee_remark,
            ])
            ->log('Payment updated: ' . $this->payment_method . ' - ' . $this->payment_status);

        $this->showPaymentModal = false;
        $this->success('Payment details updated successfully.');
        $this->booking->refresh();
    }

    public function updateStatus(): void
    {
        $this->validate([
            'booking_status' => 'required|in:pending,booked,cancelled',
        ]);

        $oldStatus = $this->booking->status->value;
        $this->booking->update([
            'status' => $this->booking_status,
        ]);

        // Log status change
        activity()
            ->performedOn($this->booking)
            ->causedBy(auth()->user())
            ->withProperties(['old_status' => $oldStatus, 'new_status' => $this->booking_status])
            ->log('Status changed from ' . $oldStatus . ' to ' . $this->booking_status);

        $this->showStatusModal = false;
        $this->success('Booking status updated successfully.');
        $this->booking->refresh();
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
        $this->success('Booking cancelled successfully.' . $refundMessage, redirectTo: route('admin.bookings.boat.index'));
    }

    public function rescheduleBooking(): void
    {
        $this->validate([
            'new_check_in' => 'required|date|after_or_equal:today',
            'new_time_slot' => 'required|string',
            'reschedule_notes' => 'nullable|string|min:3',
        ]);

        $newCheckIn = Carbon::parse($this->new_check_in . ' ' . $this->new_time_slot);

        // Calculate duration from current booking
        $durationHours = 1;
        if ($this->booking->check_in && $this->booking->check_out) {
            $durationHours = $this->booking->check_in->diffInHours($this->booking->check_out);
        }

        $oldCheckIn = $this->booking->check_in->format('M d, Y h:i A');
        $oldCheckOut = $this->booking->check_out->format('M d, Y h:i A');
        $newCheckOut = $newCheckIn->copy()->addHours($durationHours);

        // Update booking with new date/time
        $this->booking->update([
            'check_in' => $newCheckIn,
            'check_out' => $newCheckOut,
        ]);

        // Log rescheduling
        activity()
            ->performedOn($this->booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_check_in' => $oldCheckIn,
                'old_check_out' => $oldCheckOut,
                'new_check_in' => $newCheckIn->format('M d, Y h:i A'),
                'new_check_out' => $newCheckOut->format('M d, Y h:i A'),
                'notes' => $this->reschedule_notes,
            ])
            ->log('Booking rescheduled from ' . $oldCheckIn . ' to ' . $newCheckIn->format('M d, Y h:i A') . ($this->reschedule_notes ? '. Reason: ' . $this->reschedule_notes : ''));

        $this->showRescheduleModal = false;
        $this->success('Booking rescheduled successfully.');
        $this->booking->refresh();
    }

    public function getAvailableTimeSlotsProperty()
    {
        if (!$this->new_check_in || !$this->booking->bookingable || !$this->reschedule_duration) {
            return collect();
        }

        // Determine duration in hours
        $durationHours = (int) str_replace('h', '', $this->reschedule_duration);

        $timeSlots = collect();
        $startHour = 9; // 9 AM
        $endHour = 18; // 6 PM

        // Generate slots based on duration
        $currentHour = $startHour;
        while ($currentHour + $durationHours <= $endHour) {
            $startTime = Carbon::parse($this->new_check_in)->setTime(floor($currentHour), ($currentHour - floor($currentHour)) * 60);
            $endTime = $startTime->copy()->addMinutes($durationHours * 60);

            // Add buffer time from boat configuration
            $bufferMinutes = $this->booking->bookingable->buffer_time ?? 0;
            $endTimeWithBuffer = $endTime->copy()->addMinutes($bufferMinutes);

            // Check if this slot is already booked (excluding current booking)
            $isBooked = Booking::where('bookingable_type', Boat::class)
                ->where('bookingable_id', $this->booking->bookingable_id)
                ->where('id', '!=', $this->booking->id)
                ->whereDate('check_in', $this->new_check_in)
                ->where(function ($query) use ($startTime, $endTimeWithBuffer) {
                    $query
                        ->whereBetween('check_in', [$startTime, $endTimeWithBuffer])
                        ->orWhereBetween('check_out', [$startTime, $endTimeWithBuffer])
                        ->orWhere(function ($q) use ($startTime, $endTimeWithBuffer) {
                            $q->where('check_in', '<=', $startTime)->where('check_out', '>=', $endTimeWithBuffer);
                        });
                })
                ->exists();

            // Check if this is the current booking slot
            $isCurrentSlot = $this->booking->check_in && $this->booking->check_in->format('Y-m-d') === $this->new_check_in && $this->booking->check_in->format('H:i') === $startTime->format('H:i');

            $timeSlots->push([
                'start_time' => $startTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'display' => $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A'),
                'is_available' => !$isBooked,
                'is_current' => $isCurrentSlot,
                'value' => $startTime->format('H:i'),
                'duration' => $durationHours,
            ]);

            // Move to next slot (step by duration)
            $currentHour += $durationHours;
        }

        return $timeSlots;
    }

    public function with(): array
    {
        return [
            'breadcrumbs' => [['label' => 'Dashboard', 'url' => route('admin.index')], ['label' => 'Boat Bookings', 'link' => route('admin.bookings.boat.index')], ['label' => 'Booking #' . $this->booking->booking_id]],
        ];
    }
}; ?>

<div>
    @php
        $breadcrumbs = [
            [
                'link' => route('admin.index'),
                'icon' => 's-home',
            ],
            [
                'link' => route('admin.bookings.boat.index'),
                'label' => 'Boat Bookings',
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
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.boat.index') }}"
                class="btn-ghost btn-outline" />

            <x-dropdown icon="o-ellipsis-vertical" class="btn-ghost btn-circle">
                <x-menu-item title="History" icon="o-clock" wire:click.stop="$set('showHistoryDrawer', true)" />
                @if ($booking->status->value === 'booked' || $booking->status->value === 'pending')
                    <x-menu-item title="Edit" icon="o-pencil"
                        link="{{ route('admin.bookings.boat.edit', $booking->id) }}" />
                    <x-menu-item title="Reschedule" icon="o-calendar"
                        wire:click.stop="$set('showRescheduleModal', true)" />
                    <x-menu-separator />
                @endif
                @if (!$booking->isCheckedIn() && !$booking->isCheckedOut() && $booking->status->value !== 'cancelled')
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
                    <div class="flex items-center gap-2">
                        <x-badge :value="$booking->status->label()" class="{{ $booking->status->badgeColor() }}" />
                        @if ($booking->status->value !== 'cancelled')
                            <x-button icon="o-pencil" label="Update Status" wire:click="$set('showStatusModal', true)"
                                class="btn-ghost btn-sm" />
                        @endif
                    </div>
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
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Time Slot</div>
                            <div class="font-semibold">
                                {{ $booking->check_in->format('M d, Y') }}<br>
                                <span class="text-primary">{{ $booking->check_in->format('h:i A') }}</span>
                                @if ($booking->check_out)
                                    <span class="text-base-content/30"> → </span>
                                    <span class="text-success">{{ $booking->check_out->format('h:i A') }}</span>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Passengers</div>
                        <x-badge :value="$booking->adults ?? 0" class="badge-soft badge-primary" />
                    </div>

                    @if ($booking->guest_details)
                        @php
                            $guestDetails = is_string($booking->guest_details)
                                ? json_decode($booking->guest_details, true)
                                : $booking->guest_details;
                            $hasPassengerNames =
                                isset($guestDetails['adults']) &&
                                is_array($guestDetails['adults']) &&
                                count(array_filter($guestDetails['adults'])) > 0;
                        @endphp

                        @if ($hasPassengerNames)
                            <div>
                                <div class="text-sm text-base-content/50 mb-2">Passenger Names</div>
                                <div class="grid gap-2 md:grid-cols-2">
                                    @foreach ($guestDetails['adults'] as $index => $name)
                                        @if ($name)
                                            <div class="flex items-center gap-2 p-2 bg-base-200 rounded-lg">
                                                <x-icon name="o-user" class="w-4 h-4 text-primary" />
                                                <span class="text-sm font-medium">{{ $name }}</span>
                                                <x-badge value="Passenger {{ $index + 1 }}"
                                                    class="badge-xs badge-primary" />
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    @endif

                    @if ($booking->notes)
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Notes</div>
                            <div class="text-sm">{{ strip_tags($booking->notes) }}</div>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- Boat Information --}}
            @if ($booking->bookingable)
                <x-card shadow>
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-archive-box" class="w-5 h-5" />
                            <span>Boat Information</span>
                        </div>
                    </x-slot:title>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Boat Name</div>
                                <div class="font-semibold text-lg">{{ $booking->bookingable->name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Service Type</div>
                                <x-badge :value="$booking->bookingable->service_type_label" class="badge-primary" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Capacity</div>
                                <div class="text-sm">
                                    {{ $booking->bookingable->min_passengers }}-{{ $booking->bookingable->max_passengers }}
                                    passengers</div>
                            </div>
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Location</div>
                                <div class="text-sm">{{ $booking->bookingable->location ?? 'N/A' }}</div>
                            </div>
                        </div>
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
                </div>
            </x-card>

            {{-- Payment Information --}}
            <x-card shadow>
                <x-slot:title>
                    <div class="flex items-center gap-2">
                        <x-icon name="o-currency-dollar" class="w-5 h-5" />
                        <span>Payment Summary</span>
                    </div>
                </x-slot:title>
                <x-slot:menu>
                    @if ($booking->status->value !== 'cancelled')
                        <x-button icon="o-pencil" label="Update" wire:click="$set('showPaymentModal', true)"
                            class="btn-ghost btn-sm" />
                    @endif
                </x-slot:menu>

                <div class="space-y-4">
                    <!-- Booking Amount with Breakdown -->
                    <div class="p-4 bg-base-200 rounded-lg space-y-2">
                        <h3 class="text-sm font-semibold text-base-content/70 mb-3">Payment Breakdown</h3>

                        <div class="flex justify-between items-center text-sm">
                            @php
                                $durationHours =
                                    $booking->check_out && $booking->check_in
                                        ? $booking->check_in->diffInHours($booking->check_out)
                                        : 1;
                            @endphp
                            <span class="text-base-content/60">
                                Base Amount
                                @if ($durationHours > 0)
                                    ({{ $durationHours }} hour{{ $durationHours > 1 ? 's' : '' }})
                                @endif
                            </span>
                            <span
                                class="font-medium">{{ currency_format(($booking->total_amount > 0 ? $booking->total_amount : $booking->price) ?? 0) }}</span>
                        </div>

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
                                <span class="text-base-content/60">Discount</span>
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
                            <span
                                class="text-2xl font-bold">{{ currency_format((($booking->total_amount > 0 ? $booking->total_amount : $booking->price) ?? 0) + ($booking->reschedule_fee ?? 0) + ($booking->extra_fee ?? 0) - ($booking->discount_amount ?? 0) - ($booking->wallet_amount_used ?? 0)) }}</span>
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

                    <div class="divider my-2"></div>

                    <!-- Passenger Names -->
                    @if ($booking->guest_details)
                        <div>
                            <div class="text-sm text-base-content/50 mb-2">Passenger Names</div>
                            <div class="space-y-2">
                                @php
                                    $guestDetails = is_string($booking->guest_details)
                                        ? json_decode($booking->guest_details, true)
                                        : $booking->guest_details;
                                @endphp
                                @if (isset($guestDetails['adults']) && is_array($guestDetails['adults']))
                                    @foreach ($guestDetails['adults'] as $index => $name)
                                        @if ($name)
                                            <div
                                                class="flex items-center gap-2 p-2 bg-base-100 border border-base-300 rounded-lg">
                                                <x-icon name="o-user" class="w-4 h-4 text-primary" />
                                                <span class="text-sm flex-1">{{ $name }}</span>
                                                <x-badge value="Passenger {{ $index + 1 }}"
                                                    class="badge-xs badge-primary" />
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                                @if (isset($guestDetails['children']) && is_array($guestDetails['children']))
                                    @foreach ($guestDetails['children'] as $index => $name)
                                        @if ($name)
                                            <div
                                                class="flex items-center gap-2 p-2 bg-base-100 border border-base-300 rounded-lg">
                                                <x-icon name="o-user" class="w-4 h-4 text-secondary" />
                                                <span class="text-sm flex-1">{{ $name }}</span>
                                                <x-badge value="Child {{ $index + 1 }}"
                                                    class="badge-xs badge-secondary" />
                                            </div>
                                        @endif
                                    @endforeach
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
            <x-choices-offline label="Payment Status *" wire:model="payment_status" :options="[['id' => 'pending', 'name' => 'Pending'], ['id' => 'paid', 'name' => 'Paid']]"
                icon="o-credit-card" searchable single />

            <x-choices-offline label="Payment Method *" wire:model="payment_method" :options="[
                ['id' => 'cash', 'name' => 'Cash'],
                ['id' => 'card', 'name' => 'Card'],
                ['id' => 'online', 'name' => 'Online'],
                ['id' => 'other', 'name' => 'Other'],
            ]"
                icon="o-banknotes" searchable single />

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

    {{-- Booking Status Update Modal --}}
    <x-modal wire:model="showStatusModal" title="Update Booking Status" class="backdrop-blur">
        <div class="space-y-4 h-50 py-2">
            <x-choices-offline label="Booking Status *" wire:model="booking_status" :options="[
                ['id' => 'pending', 'name' => 'Pending'],
                ['id' => 'booked', 'name' => 'Booked'],
                ['id' => 'cancelled', 'name' => 'Cancelled'],
            ]" icon="o-flag"
                searchable single />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showStatusModal = false" />
            <x-button label="Update" wire:click="updateStatus" class="btn-primary" spinner="updateStatus" />
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

    {{-- Reschedule Booking Modal --}}
    <x-modal wire:model="showRescheduleModal" title="Reschedule Booking" class="backdrop-blur">
        <div class="space-y-4">
            <x-alert title="Current Booking"
                description="Departure: {{ $booking->check_in->format('M d, Y h:i A') }} | Return: {{ $booking->check_out->format('M d, Y h:i A') }}"
                icon="o-information-circle" class="alert-info" />

            <x-input label="New Date *" type="date" icon="o-calendar" wire:model.live="new_check_in"
                min="{{ now()->format('Y-m-d') }}" />

            @if ($new_check_in && $this->availableTimeSlots->isNotEmpty())
                <div>
                    <label class="label">
                        <span class="label-text">Select Time Slot *</span>
                    </label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @foreach ($this->availableTimeSlots as $slot)
                            <div wire:key="slot-{{ $slot['value'] }}"
                                class="border rounded-lg p-3 transition-all
                                {{ $new_time_slot === $slot['value'] ? 'border-primary bg-primary/10' : 'border-base-300' }}
                                {{ !$slot['is_available'] ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-primary' }}
                                {{ $slot['is_current'] ? 'ring-2 ring-warning' : '' }}"
                                @if ($slot['is_available']) wire:click="$set('new_time_slot', '{{ $slot['value'] }}')" @endif>
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <div class="font-semibold text-sm">{{ $slot['display'] }}</div>
                                        <div class="text-xs text-base-content/60 mt-1">
                                            {{ $slot['duration'] }} hour{{ $slot['duration'] > 1 ? 's' : '' }}
                                        </div>
                                    </div>
                                    <div class="flex flex-col gap-1 items-end">
                                        @if ($slot['is_current'])
                                            <x-badge value="Current" class="badge-warning badge-sm" />
                                        @endif
                                        @if ($slot['is_available'])
                                            <x-badge value="Available" class="badge-success badge-sm" />
                                        @else
                                            <x-badge value="Booked" class="badge-error badge-sm" />
                                        @endif
                                        @if ($new_time_slot === $slot['value'])
                                            <x-icon name="o-check-circle" class="w-5 h-5 text-primary" />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @elseif ($new_check_in && $this->availableTimeSlots->isEmpty())
                <x-alert title="No Slots Available"
                    description="No time slots available for the selected date. Please choose another date."
                    icon="o-exclamation-triangle" class="alert-warning" />
            @endif

            <x-textarea label="Reason for Rescheduling (Optional)" wire:model="reschedule_notes"
                placeholder="Enter reason for rescheduling..." rows="3"
                hint="Provide context for the date change" />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showRescheduleModal = false" />
            <x-button label="Confirm Reschedule" wire:click="rescheduleBooking" class="btn-primary"
                spinner="rescheduleBooking" />
        </x-slot:actions>
    </x-modal>

    {{-- Activity History Drawer --}}
    @include('livewire.booking.partials.activity-history-drawer')
</div>
