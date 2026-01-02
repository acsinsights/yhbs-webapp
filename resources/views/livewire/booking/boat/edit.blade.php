<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use App\Models\{Booking, Boat};
use App\Enums\{BookingStatusEnum, PaymentMethodEnum, PaymentStatusEnum};

new class extends Component {
    use Toast;

    public Booking $booking;
    public string $check_in = '';
    public string $check_out = '';
    public string $check_in_time = '09:00';
    public ?string $selected_time_slot = null;
    public int $adults = 1;
    public int $children = 0;
    public array $adultNames = [];
    public array $childrenNames = [];
    public ?float $amount = null;
    public string $payment_method = 'cash';
    public string $payment_status = 'pending';
    public string $status = 'pending';
    public ?string $notes = null;

    // Duration/Slot based fields
    public ?string $booking_type = 'private';
    public ?string $duration_slot = null;
    public ?int $custom_hours = null;

    public function mount(Booking $booking): void
    {
        if ($booking->bookingable_type !== Boat::class) {
            $this->error('Invalid booking type.', redirectTo: route('admin.bookings.boat.index'));
            return;
        }

        $this->booking = $booking->load(['bookingable', 'user']);
        $this->check_in = \Carbon\Carbon::parse($booking->check_in)->format('Y-m-d');
        $this->check_in_time = \Carbon\Carbon::parse($booking->check_in)->format('H:i');
        $this->check_out = $booking->check_out ? \Carbon\Carbon::parse($booking->check_out)->format('Y-m-d') : '';
        $this->adults = $booking->adults;
        $this->children = $booking->children;
        $this->amount = $booking->total_amount ?? $booking->price;
        $this->payment_method = $booking->payment_method->value;
        $this->payment_status = $booking->payment_status->value;
        $this->status = $booking->status->value;
        $this->notes = $booking->notes;

        // Load guest names from guest_details
        if ($booking->guest_details) {
            $this->adultNames = $booking->guest_details['adults'] ?? [];
            $this->childrenNames = $booking->guest_details['children'] ?? [];
        }

        // Initialize empty arrays if needed
        if (count($this->adultNames) < $this->adults) {
            $this->adultNames = array_pad($this->adultNames, $this->adults, '');
        }
        if (count($this->childrenNames) < $this->children) {
            $this->childrenNames = array_pad($this->childrenNames, $this->children, '');
        }

        // Parse duration and booking type from notes if available
        if ($this->notes) {
            if (preg_match('/Duration\/Slot: (\S+)/', $this->notes, $matches)) {
                $this->duration_slot = $matches[1];
                if (preg_match('/\((\d+) hours\)/', $this->notes, $hourMatches)) {
                    $this->custom_hours = (int) $hourMatches[1];
                }
            }
            if (preg_match('/Booking Type: (\w+)/', $this->notes, $typeMatches)) {
                $this->booking_type = $typeMatches[1];
            }
        }
    }

    public function updated($property): void
    {
        // Regenerate time slots when date, duration changes
        if (in_array($property, ['check_in', 'duration_slot', 'custom_hours'])) {
            $this->selected_time_slot = null;
        }
    }

    public function updatedAdults(): void
    {
        // Initialize adult names array
        $currentCount = count($this->adultNames);
        if ($this->adults > $currentCount) {
            for ($i = $currentCount; $i < $this->adults; $i++) {
                $this->adultNames[$i] = '';
            }
        } elseif ($this->adults < $currentCount) {
            $this->adultNames = array_slice($this->adultNames, 0, $this->adults);
        }
    }

    public function updatedChildren(): void
    {
        // Initialize children names array
        $currentCount = count($this->childrenNames);
        if ($this->children > $currentCount) {
            for ($i = $currentCount; $i < $this->children; $i++) {
                $this->childrenNames[$i] = '';
            }
        } elseif ($this->children < $currentCount) {
            $this->childrenNames = array_slice($this->childrenNames, 0, $this->children);
        }
    }

    public function getAvailableTimeSlotsProperty()
    {
        if (!$this->check_in || !$this->booking->bookingable || !$this->duration_slot) {
            return collect();
        }

        $boat = $this->booking->bookingable;

        // Determine duration in hours
        $durationHours = match ($this->duration_slot) {
            '1h' => 1,
            '2h' => 2,
            '3h' => 3,
            'custom' => $this->custom_hours ?? 1,
            '15min' => 0.25,
            '30min' => 0.5,
            '1hour_full' => 1,
            default => 1,
        };

        $timeSlots = collect();
        $startHour = 9; // 9 AM
        $endHour = 18; // 6 PM

        // Get buffer time in hours
        $bufferMinutes = $boat->buffer_time ?? 0;
        $bufferHours = $bufferMinutes / 60;

        // Generate slots based on duration + buffer time
        $currentHour = $startHour;
        while ($currentHour + $durationHours <= $endHour) {
            $startTime = \Carbon\Carbon::parse($this->check_in)->setTime(floor($currentHour), ($currentHour - floor($currentHour)) * 60);
            $endTime = $startTime->copy()->addMinutes($durationHours * 60);

            // Add buffer time from boat configuration
            $endTimeWithBuffer = $endTime->copy()->addMinutes($bufferMinutes);

            // Check if this slot is already booked (including buffer time)
            // Exclude current booking from availability check
            $isBooked = Booking::where('bookingable_type', Boat::class)
                ->where('bookingable_id', $boat->id)
                ->where('id', '!=', $this->booking->id) // Exclude current booking
                ->whereDate('check_in', $this->check_in)
                ->where(function ($query) use ($startTime, $endTimeWithBuffer) {
                    $query
                        ->whereBetween('check_in', [$startTime, $endTimeWithBuffer])
                        ->orWhereBetween('check_out', [$startTime, $endTimeWithBuffer])
                        ->orWhere(function ($q) use ($startTime, $endTimeWithBuffer) {
                            $q->where('check_in', '<=', $startTime)->where('check_out', '>=', $endTimeWithBuffer);
                        });
                })
                ->exists();

            $timeSlots->push([
                'start_time' => $startTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'display' => $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A'),
                'is_available' => !$isBooked,
                'value' => $startTime->format('H:i'),
                'duration' => $durationHours,
            ]);

            // Move to next slot (step by duration + buffer time)
            $currentHour += $durationHours + $bufferHours;
        }

        return $timeSlots;
    }

    public function selectTimeSlot($time)
    {
        $this->selected_time_slot = $time;
        $this->check_in_time = $time;
    }

    public function save(): void
    {
        // Validate booking cut-off rules
        if ($this->booking->bookingable) {
            $checkInDate = Carbon::parse($this->check_in);
            $now = Carbon::now();

            // Marina 1, 2, 4 - Must book by 11:59 PM day before
            if (in_array($this->booking->bookingable->name, ['Marina 1', 'Marina 2', 'Marina 4'])) {
                $cutoffTime = $checkInDate->copy()->subDay()->endOfDay();
                if ($now->greaterThan($cutoffTime)) {
                    $this->error('Marina bookings must be made by 11:59 PM the day before the trip. Please select a later date.');
                    return;
                }
            }

            // VIP Limousine - Same day allowed, operational 8 AM - 10 PM
            if ($this->booking->bookingable->name === 'VIP Limousine') {
                $checkInDateTime = \Carbon\Carbon::parse($this->check_in . ' ' . $this->check_in_time);
                $operationalStart = $checkInDateTime->copy()->setTime(8, 0);
                $operationalEnd = $checkInDateTime->copy()->setTime(22, 0);

                if ($checkInDateTime->lessThan($operationalStart) || $checkInDateTime->greaterThan($operationalEnd)) {
                    $this->error('VIP Limousine operates between 8:00 AM and 10:00 PM only.');
                    return;
                }
            }

            // Abu Al Khair, Bint Al Khair, Sea Bus - Month-by-month only
            if (in_array($this->booking->bookingable->name, ['Abu Al Khair', 'Bint Al Khair', 'Sea Bus'])) {
                $maxAdvanceMonths = 1;
                $maxBookingDate = $now->copy()->addMonths($maxAdvanceMonths)->endOfMonth();

                if ($checkInDate->greaterThan($maxBookingDate)) {
                    $this->error('Ferry services can only be booked within the current and next month. Schedule depends on weather & tide conditions.');
                    return;
                }
            }
        }

        $validated = $this->validate([
            'check_in_time' => 'required',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,online,other',
            'payment_status' => 'required|in:pending,paid',
            'status' => 'required|in:pending,booked,cancelled',
        ]);

        $checkInDateTime = \Carbon\Carbon::parse($this->check_in . ' ' . $validated['check_in_time']);

        // Calculate checkout time based on duration
        $durationHours = match ($this->duration_slot) {
            '1h' => 1,
            '2h' => 2,
            '3h' => 3,
            'custom' => $this->custom_hours ?? 1,
            '15min' => 0.25,
            '30min' => 0.5,
            '1hour_full' => 1,
            default => 1,
        };

        $checkOutDateTime = $checkInDateTime->copy()->addMinutes($durationHours * 60);

        // Add buffer time from boat configuration
        if ($this->booking->bookingable->buffer_time) {
            $checkOutDateTime->addMinutes($this->booking->bookingable->buffer_time);
        }

        $guestDetails = [
            'adults' => array_values(array_filter($this->adultNames)),
            'children' => array_values(array_filter($this->childrenNames)),
        ];

        $notesText = $this->notes ?? '';
        if (!str_contains($notesText, 'Duration/Slot:')) {
            $notesText .= "\n\nDuration/Slot: " . ($this->duration_slot ?? 'N/A');
            if ($this->duration_slot === 'custom' && $this->custom_hours) {
                $notesText .= " ({$this->custom_hours} hours)";
            }
            if ($this->booking_type) {
                $notesText .= "\nBooking Type: " . $this->booking_type;
            }
        }

        $this->booking->update([
            'check_in' => $checkInDateTime,
            'check_out' => $checkOutDateTime,
            'adults' => $validated['adults'],
            'children' => $validated['children'],
            'guest_details' => $guestDetails,
            'price' => $validated['amount'],
            'total_amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_status'],
            'status' => $validated['status'],
            'notes' => $notesText,
        ]);

        $this->success('Booking updated successfully.', redirectTo: route('admin.bookings.boat.show', $this->booking->id));
    }

    public function with(): array
    {
        $boat = $this->booking->bookingable;

        // Duration options based on boat type
        $durationOptions = match ($boat->service_type) {
            'yacht', 'taxi' => [['id' => '1h', 'name' => '1 Hour - KD ' . number_format($boat->price_1hour, 2)], ['id' => '2h', 'name' => '2 Hours - KD ' . number_format($boat->price_2hours, 2)], ['id' => '3h', 'name' => '3 Hours - KD ' . number_format($boat->price_3hours, 2)], ['id' => 'custom', 'name' => 'Custom Hours (KD ' . number_format($boat->additional_hour_price, 2) . '/hour)']],
            'ferry' => [['id' => '1h', 'name' => '1 Hour'], ['id' => '2h', 'name' => '2 Hours'], ['id' => '3h', 'name' => '3 Hours'], ['id' => 'custom', 'name' => 'Custom Hours']],
            'limousine' => [['id' => '15min', 'name' => '15 Minutes - KD ' . number_format($boat->price_15min, 2)], ['id' => '30min', 'name' => '30 Minutes - KD ' . number_format($boat->price_30min, 2)], ['id' => '1hour_full', 'name' => '1 Hour / Full Boat - KD ' . number_format($boat->price_full_boat, 2)]],
            default => [],
        };

        return [
            'breadcrumbs' => [['label' => 'Dashboard', 'url' => route('admin.index')], ['label' => 'Boat Bookings', 'link' => route('admin.bookings.boat.index')], ['label' => 'Booking #' . $this->booking->booking_id, 'link' => route('admin.bookings.boat.show', $this->booking->id)], ['label' => 'Edit']],
            'paymentMethods' => [['id' => 'cash', 'name' => 'Cash'], ['id' => 'card', 'name' => 'Credit/Debit Card'], ['id' => 'online', 'name' => 'Online Payment'], ['id' => 'other', 'name' => 'Other']],
            'paymentStatuses' => [['id' => 'pending', 'name' => 'Pending'], ['id' => 'paid', 'name' => 'Paid']],
            'bookingStatuses' => [['id' => 'pending', 'name' => 'Pending'], ['id' => 'booked', 'name' => 'Booked'], ['id' => 'cancelled', 'name' => 'Cancelled']],
            'durationOptions' => $durationOptions,
            'bookingTypes' => [['id' => 'private', 'name' => 'Private Trip'], ['id' => 'public', 'name' => 'Public Trip']],
        ];
    }
}; ?>

<div>
    <x-header title="Edit Boat Booking #{{ $booking->booking_id }}" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Manage booking details and payment information</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back to Booking"
                link="{{ route('admin.bookings.boat.show', $booking->id) }}" class="btn-primary btn-soft" responsive />
        </x-slot:actions>
    </x-header>

    <form wire:submit="save" class="mt-5">
        <div class="grid gap-6 lg:grid-cols-3">
            {{-- Main Form --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Boat & Customer Info Cards --}}
                <div class="grid md:grid-cols-2 gap-6">
                    <x-card
                        class="shadow-md hover:shadow-lg transition-shadow bg-gradient-to-br from-primary/5 to-primary/10">
                        <x-slot:title>
                            <div class="flex items-center gap-2">
                                <div class="p-2 rounded-lg bg-primary/10">
                                    <x-icon name="o-archive-box" class="w-5 h-5 text-primary" />
                                </div>
                                <span class="text-base">Boat Details</span>
                            </div>
                        </x-slot:title>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <x-icon name="o-archive-box" class="w-10 h-10 text-primary mt-1" />
                                <div class="flex-1">
                                    <div class="text-xs text-base-content/50 mb-1">Boat Name</div>
                                    <div class="font-bold text-xl">{{ $booking->bookingable->name }}</div>
                                </div>
                            </div>
                            <div class="divider my-2"></div>
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-base-content/50">Service Type</div>
                                <x-badge :value="$booking->bookingable->service_type_label" class="badge-primary badge-lg" />
                            </div>
                        </div>
                    </x-card>

                    <x-card
                        class="shadow-md hover:shadow-lg transition-shadow bg-gradient-to-br from-success/5 to-success/10">
                        <x-slot:title>
                            <div class="flex items-center gap-2">
                                <div class="p-2 rounded-lg bg-success/10">
                                    <x-icon name="o-user-circle" class="w-5 h-5 text-success" />
                                </div>
                                <span class="text-base">Customer Info</span>
                            </div>
                        </x-slot:title>
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <x-icon name="o-user-circle" class="w-10 h-10 text-success mt-1" />
                                <div class="flex-1">
                                    <div class="text-xs text-base-content/50 mb-1">Customer Name</div>
                                    <div class="font-bold text-xl">{{ $booking->user->name }}</div>
                                </div>
                            </div>
                            <div class="divider my-2"></div>
                            <div class="flex items-center gap-2">
                                <x-icon name="o-envelope" class="w-4 h-4 text-warning" />
                                <div class="text-sm">{{ $booking->user->email }}</div>
                            </div>
                        </div>
                    </x-card>
                </div>

                {{-- Booking Details Card --}}
                <x-card class="shadow-lg hover:shadow-xl transition-shadow">
                    <x-slot:title>
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-primary/10">
                                <x-icon name="o-pencil-square" class="w-6 h-6 text-primary" />
                            </div>
                            <div>
                                <span class="text-lg font-bold">Edit Booking Details</span>
                                <p class="text-xs text-base-content/50 mt-1">Update dates, guests, and pricing
                                    information</p>
                            </div>
                        </div>
                    </x-slot:title>
                    <div class="grid gap-6">
                        <x-card class="bg-gradient-to-r from-base-200/50 to-base-300/30">
                            <x-slot:title>
                                <div class="flex items-center gap-2 text-sm">
                                    <x-icon name="o-calendar" class="w-5 h-5 text-primary" />
                                    <span>Date & Time</span>
                                </div>
                            </x-slot:title>
                            <x-datepicker label="Departure Date *" icon="o-calendar" wire:model.live="check_in" />
                        </x-card>

                        {{-- Duration & Trip Type Section --}}
                        @if ($booking->bookingable->service_type === 'ferry')
                            <x-card class="bg-gradient-to-r from-base-200/50 to-base-300/30">
                                <x-slot:title>
                                    <div class="flex items-center gap-2 text-sm">
                                        <x-icon name="o-ticket" class="w-5 h-5 text-warning" />
                                        <span>Trip Type & Duration</span>
                                    </div>
                                </x-slot:title>
                                <div class="grid md:grid-cols-2 gap-4">
                                    <x-choices-offline label="Booking Type" icon="o-user-group" :options="$bookingTypes"
                                        wire:model="booking_type" searchable single />
                                    <x-choices-offline label="Duration" icon="o-clock" :options="$durationOptions"
                                        wire:model.live="duration_slot" searchable single />
                                </div>
                                @if ($duration_slot === 'custom')
                                    <x-input label="Custom Hours" icon="o-clock" type="number"
                                        wire:model.live="custom_hours" min="1" class="mt-4" />
                                @endif
                            </x-card>
                        @else
                            <x-card class="bg-gradient-to-r from-base-200/50 to-base-300/30">
                                <x-slot:title>
                                    <div class="flex items-center gap-2 text-sm">
                                        <x-icon name="o-clock" class="w-5 h-5 text-warning" />
                                        <span>Duration Selection</span>
                                    </div>
                                </x-slot:title>
                                <x-choices-offline label="Duration/Slot *" icon="o-clock" :options="$durationOptions"
                                    wire:model.live="duration_slot" searchable single />
                                @if ($duration_slot === 'custom')
                                    <x-input label="Custom Hours" icon="o-clock" type="number"
                                        wire:model.live="custom_hours" min="1" class="mt-4" />
                                @endif
                            </x-card>
                        @endif

                        {{-- Time Slot Selection --}}
                        @if ($duration_slot && count($this->availableTimeSlots) > 0)
                            <x-card class="bg-gradient-to-r from-base-200/50 to-base-300/30">
                                <x-slot:title>
                                    <div class="flex items-center gap-2 text-sm">
                                        <x-icon name="o-clock" class="w-5 h-5 text-success" />
                                        <span>Select Time Slot</span>
                                    </div>
                                </x-slot:title>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
                                    @foreach ($this->availableTimeSlots->where('is_available', true) as $slot)
                                        <button type="button" wire:click="selectTimeSlot('{{ $slot['value'] }}')"
                                            @class([
                                                'btn btn-sm text-xs transition-all',
                                                'btn-primary' => $selected_time_slot === $slot['value'],
                                                'btn-outline' => $selected_time_slot !== $slot['value'],
                                            ])>
                                            <x-icon name="o-clock" class="w-4 h-4" />
                                            {{ $slot['display'] }}
                                        </button>
                                    @endforeach
                                </div>
                                @if ($selected_time_slot)
                                    <x-alert icon="o-check-circle" class="alert-success mt-4">
                                        <strong>Selected Time:</strong> {{ $check_in_time }}
                                    </x-alert>
                                @endif
                            </x-card>
                        @endif

                        <x-card class="bg-gradient-to-r from-base-200/50 to-base-300/30">
                            <x-slot:title>
                                <div class="flex items-center gap-2 text-sm">
                                    <x-icon name="o-users" class="w-5 h-5 text-info" />
                                    <span>Number of Guests</span>
                                </div>
                            </x-slot:title>
                            <div class="grid md:grid-cols-2 gap-4">
                                <x-input label="Adults *" icon="o-user" type="number" wire:model.live="adults"
                                    min="1" hint="Minimum 1 adult required" />
                                <x-input label="Children" icon="o-user" type="number" wire:model.live="children"
                                    min="0" hint="Optional" />
                            </div>
                        </x-card>

                        {{-- Guest Names Section --}}
                        @if ($adults > 0 || $children > 0)
                            <x-card class="bg-gradient-to-r from-base-200/50 to-base-300/30">
                                <x-slot:title>
                                    <div class="flex items-center gap-2 text-sm">
                                        <x-icon name="o-identification" class="w-5 h-5 text-success" />
                                        <span>Guest Names</span>
                                    </div>
                                </x-slot:title>

                                @if ($adults > 0)
                                    <div class="mb-4">
                                        <p
                                            class="text-sm font-semibold text-base-content/70 mb-3 flex items-center gap-2">
                                            <x-icon name="o-user" class="w-4 h-4" />
                                            Adult Names ({{ $adults }})
                                        </p>
                                        <div class="grid md:grid-cols-2 gap-3">
                                            @for ($i = 0; $i < $adults; $i++)
                                                <x-input label="Adult {{ $i + 1 }}"
                                                    wire:model="adultNames.{{ $i }}"
                                                    placeholder="Enter full name" />
                                            @endfor
                                        </div>
                                    </div>
                                @endif

                                @if ($children > 0)
                                    <div>
                                        <p
                                            class="text-sm font-semibold text-base-content/70 mb-3 flex items-center gap-2">
                                            <x-icon name="o-user" class="w-4 h-4" />
                                            Children Names ({{ $children }})
                                        </p>
                                        <div class="grid md:grid-cols-2 gap-3">
                                            @for ($i = 0; $i < $children; $i++)
                                                <x-input label="Child {{ $i + 1 }}"
                                                    wire:model="childrenNames.{{ $i }}"
                                                    placeholder="Enter full name" />
                                            @endfor
                                        </div>
                                    </div>
                                @endif
                            </x-card>
                        @endif

                        <x-card class="bg-gradient-to-r from-base-200/50 to-base-300/30">
                            <div class="grid md:grid-cols-2 gap-4">
                                <x-input label="Total Amount (KD) *" type="number" step="0.01"
                                    wire:model="amount" prefix="KD" class="input-lg" />
                            </div>

                            <x-textarea label="Special Notes" icon="o-document-text" wire:model="notes"
                                rows="4"
                                placeholder="Add any special notes, requirements, or instructions for this booking..."
                                hint="Optional additional information" />
                        </x-card>
                    </div>
                </x-card>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                <x-card class="shadow-md hover:shadow-lg transition-shadow bg-gradient-to-br from-info/5 to-info/10">
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <div class="p-2 rounded-lg bg-info/10">
                                <x-icon name="o-flag" class="w-5 h-5 text-info" />
                            </div>
                            <div>
                                <span class="font-bold">Booking Status</span>
                                <p class="text-xs text-base-content/50 mt-1">Manage booking state</p>
                            </div>
                        </div>
                    </x-slot:title>
                    <x-choices-offline label="Update Status *" icon="o-flag" :options="$bookingStatuses" wire:model="status"
                        hint="Current booking status" searchable single />
                </x-card>

                <x-card
                    class="shadow-md hover:shadow-lg transition-shadow bg-gradient-to-br from-success/5 to-success/10">
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <div class="p-2 rounded-lg bg-success/10">
                                <x-icon name="o-credit-card" class="w-5 h-5 text-success" />
                            </div>
                            <div>
                                <span class="font-bold">Payment Info</span>
                                <p class="text-xs text-base-content/50 mt-1">Payment method & status</p>
                            </div>
                        </div>
                    </x-slot:title>
                    <div class="space-y-4">
                        <x-choices-offline label="Payment Method *" icon="o-credit-card" :options="$paymentMethods"
                            wire:model="payment_method" searchable single />

                        <x-choices-offline label="Payment Status *" icon="o-banknotes" :options="$paymentStatuses"
                            wire:model="payment_status" searchable single />
                    </div>
                </x-card>

                <x-card
                    class="shadow-md hover:shadow-lg transition-shadow bg-gradient-to-br from-warning/5 to-warning/10">
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <div class="p-2 rounded-lg bg-warning/10">
                                <x-icon name="o-cog" class="w-5 h-5 text-warning" />
                            </div>
                            <span class="font-bold">Actions</span>
                        </div>
                    </x-slot:title>
                    <div class="space-y-3">
                        <x-button label="Save Changes" type="submit" icon="o-check-circle"
                            class="btn-primary w-full btn-lg shadow-lg hover:shadow-xl transition-shadow"
                            spinner="save" />
                        <x-button label="Cancel" link="{{ route('admin.bookings.boat.show', $booking->id) }}"
                            icon="o-x-mark" class="btn-ghost w-full" />
                    </div>
                </x-card>

                <x-card class="shadow-md bg-base-200/50">
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-clock" class="w-5 h-5 text-info" />
                            <span class="text-sm">Last Updated</span>
                        </div>
                    </x-slot:title>
                    <div class="text-sm">
                        <div class="flex items-center gap-2 text-base-content/70 mb-2">
                            <x-icon name="o-calendar" class="w-4 h-4" />
                            <span>{{ $booking->updated_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-xs text-base-content/50 pl-6">
                            {{ $booking->updated_at->format('l, d M Y \a\t H:i') }}
                        </div>
                    </div>
                </x-card>
            </div>
        </div>
    </form>
</div>
