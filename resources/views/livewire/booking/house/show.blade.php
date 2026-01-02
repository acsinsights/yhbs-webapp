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
    public bool $showCancelModal = false;
    public string $cancellation_reason = '';
    public ?float $refund_amount = null;
    public bool $showRescheduleModal = false;
    public ?string $new_date_range = null;
    public ?string $reschedule_notes = null;
    public array $bookedDates = [];
    public bool $showHistoryDrawer = false;
    public $activities = [];

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['bookingable', 'user']);
        $this->payment_status = $booking->payment_status->value;
        $this->payment_method = $booking->payment_method->value;
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
        ]);

        $this->booking->update([
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
        ]);

        $this->showPaymentModal = false;
        $this->success('Payment details updated successfully.');
    }

    public function cancelBooking(): void
    {
        // Prevent cancellation if already checked in
        if ($this->booking->isCheckedIn()) {
            $this->error('Cannot cancel a booking that is already checked in.');
            $this->showCancelModal = false;
            return;
        }

        $this->validate([
            'cancellation_reason' => 'required|min:10',
            'refund_amount' => 'nullable|numeric|min:0|max:' . $this->booking->price,
        ]);

        $this->booking->update([
            'status' => 'cancelled',
            'notes' => ($this->booking->notes ? $this->booking->notes . "\n\n" : '') . 'Cancellation Reason: ' . $this->cancellation_reason,
        ]);

        // Add refund to customer's wallet if amount is specified
        if ($this->refund_amount && $this->refund_amount > 0) {
            $walletService = app(WalletService::class);
            $walletService->addCredit($this->booking->user, $this->refund_amount, $this->booking, "Refund for cancelled booking #{$this->booking->booking_id}", 'booking_cancellation');
        }

        $this->showCancelModal = false;
        $this->success('Booking cancelled successfully.' . ($this->refund_amount ? " Refund of {$this->refund_amount} added to customer's wallet." : ''), redirectTo: route('admin.bookings.house.index'));
    }

    public function rescheduleBooking(): void
    {
        $this->validate([
            'new_date_range' => 'required|string',
            'reschedule_notes' => 'nullable|string|min:3',
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

        // Update booking with new dates
        $oldCheckIn = $this->booking->check_in->format('M d, Y');
        $oldCheckOut = $this->booking->check_out->format('M d, Y');

        $this->booking->update([
            'check_in' => $newCheckIn,
            'check_out' => $newCheckOut,
        ]);

        // Log activity
        $description = "Rescheduled from {$oldCheckIn} - {$oldCheckOut} to {$newCheckIn->format('M d, Y')} - {$newCheckOut->format('M d, Y')}";
        if ($this->reschedule_notes) {
            $description .= ". Reason: {$this->reschedule_notes}";
        }

        activity()
            ->performedOn($this->booking)
            ->causedBy(auth()->user())
            ->withProperties([
                'old_check_in' => $oldCheckIn,
                'old_check_out' => $oldCheckOut,
                'new_check_in' => $newCheckIn->format('M d, Y'),
                'new_check_out' => $newCheckOut->format('M d, Y'),
                'notes' => $this->reschedule_notes,
            ])
            ->log($description);

        // Create notification for customer
        \App\Models\UserNotification::create([
            'user_id' => $this->booking->user_id,
            'type' => 'booking_rescheduled',
            'title' => 'Booking Rescheduled',
            'message' => "Your booking #{$this->booking->booking_id} has been rescheduled from {$oldCheckIn} - {$oldCheckOut} to {$newCheckIn->format('M d, Y')} - {$newCheckOut->format('M d, Y')}.",
            ,
            'data' => [
                'booking_id' => $this->booking->id,
                'old_check_in' => $oldCheckIn,
                'old_check_out' => $oldCheckOut,
                'new_check_in' => $newCheckIn->format('M d, Y'),
                'new_check_out' => $newCheckOut->format('M d, Y'),
                'notes' => $this->reschedule_notes,
            ],
        ]);

        $this->showRescheduleModal = false;
        $this->success('Booking rescheduled successfully.');
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

            @if ($booking->status === \App\Enums\BookingStatusEnum::BOOKED)
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

                    @if ($booking->notes)
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Notes</div>
                            <div class="text-sm">{{ strip_tags($booking->notes) }}</div>
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
                        @if ($booking->bookingable->sku)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">SKU</div>
                                <div class="font-semibold">{{ $booking->bookingable->sku }}</div>
                            </div>
                        @endif
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
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Amount</div>
                        <div class="font-semibold text-2xl">{{ currency_format($booking->price ?? 0) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Payment Method</div>
                        <x-badge :value="$booking->payment_method->label()" class="{{ $booking->payment_method->badgeColor() }}" />
                    </div>
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Payment Status</div>
                        <x-badge :value="$booking->payment_status->label()" class="{{ $booking->payment_status->badgeColor() }}" />
                    </div>
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
                description="Check-in: {{ $booking->check_in->format('M d, Y') }} | Check-out: {{ $booking->check_out->format('M d, Y') }}"
                icon="o-information-circle" class="alert-info" />

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
                <p class="text-xs text-base-content/60 mt-1">📅 Select check-in and check-out dates. Red dates are
                    already booked.</p>
            </div>

            <x-textarea label="Reason for Rescheduling (Optional)" wire:model="reschedule_notes"
                placeholder="Enter reason for rescheduling..." rows="3"
                hint="Provide context for the date change" />

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
    <x-drawer wire:model="showHistoryDrawer" title="Booking History" class="w-11/12 lg:w-2/5" right>
        <div class="space-y-4">
            @if (count($activities) > 0)
                <div class="space-y-3">
                    @foreach ($activities as $activity)
                        <x-card shadow>
                            <div class="space-y-2">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        @php
                                            // Parse description to extract reason
                                            $mainDescription = $activity->description;
                                            $reason = null;
                                            if (strpos($activity->description, '. Reason: ') !== false) {
                                                [$mainDescription, $reason] = explode(
                                                    '. Reason: ',
                                                    $activity->description,
                                                    2,
                                                );
                                            }
                                        @endphp
                                        <p class="text-sm font-medium text-base-content">
                                            {{ $mainDescription }}
                                        </p>
                                        @if ($reason)
                                            <p class="text-xs text-base-content/70 mt-2 italic">
                                                <strong>Reason:</strong> {{ $reason }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center justify-between text-xs text-base-content/50">
                                    <div class="flex items-center gap-2">
                                        <x-icon name="o-user" class="w-3 h-3" />
                                        <span>{{ $activity->causer?->name ?? 'System' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <x-icon name="o-clock" class="w-3 h-3" />
                                        <span>{{ $activity->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            @else
                <x-alert title="No History" description="No activity history available for this booking yet."
                    icon="o-information-circle" class="alert-info" />
            @endif
        </div>

        <x-slot:actions>
            <x-button label="Close" @click="$wire.showHistoryDrawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
