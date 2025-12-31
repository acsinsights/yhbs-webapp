<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use App\Models\{Booking, Boat, User, BoatServiceType};
use App\Enums\{RolesEnum, BookingStatusEnum};
use App\Notifications\WelcomeCustomerNotification;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

new class extends Component {
    use Toast;

    public ?string $service_type_slug = null;
    public ?int $boat_id = null;
    public ?int $user_id = null;
    public string $check_in = '';
    public string $check_in_time = '09:00';
    public ?string $selected_time_slot = null;
    public int $adults = 1;
    public int $children = 0;
    public array $adultNames = [];
    public array $childrenNames = [];
    public ?float $amount = null;
    public string $payment_method = 'cash';
    public string $payment_status = 'pending';
    public ?string $notes = null;

    // Customer modal
    public bool $createCustomerModal = false;
    public string $customer_name = '';
    public string $customer_email = '';

    // Slot/Duration based fields
    public ?string $booking_type = 'private'; // private or public (for ferry)
    public ?string $duration_slot = null; // 1h, 2h, 3h, 15min, 30min, 1hour_full
    public ?int $custom_hours = null;

    // Selected boat
    public ?Boat $selectedBoat = null;

    public function mount(): void
    {
        $this->boat_id = request('boat_id');
        $this->check_in = now()->format('Y-m-d');

        if ($this->boat_id) {
            $boat = Boat::find($this->boat_id);
            if ($boat) {
                $this->service_type_slug = $boat->service_type;
                $this->updatedBoatId($this->boat_id);
            }
        }
    }

    public function updatedServiceTypeSlug($value): void
    {
        // Reset boat selection when service type changes
        $this->boat_id = null;
        $this->selectedBoat = null;
        $this->duration_slot = null;
        $this->custom_hours = null;
        $this->amount = null;
    }

    public function updatedBoatId($value): void
    {
        if ($value) {
            $this->selectedBoat = Boat::find($value);
            $this->duration_slot = null;
            $this->custom_hours = null;
            $this->calculateAmount();
        }
    }

    public function updated($property): void
    {
        if (in_array($property, ['duration_slot', 'custom_hours', 'adults', 'children', 'booking_type'])) {
            $this->calculateAmount();
        }

        // Regenerate time slots when date, boat, or duration changes
        if (in_array($property, ['check_in', 'boat_id', 'duration_slot', 'custom_hours'])) {
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

    public function createCustomer(): void
    {
        $this->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|unique:users,email',
        ]);

        // Generate random password
        $password = Hash::make(Str::random(12));

        $user = User::create([
            'name' => $this->customer_name,
            'email' => $this->customer_email,
            'password' => $password,
        ]);

        $user->assignRole(RolesEnum::CUSTOMER->value);

        // Send welcome email with password reset link
        $user->notify(new WelcomeCustomerNotification());

        $this->user_id = $user->id;
        $this->createCustomerModal = false;
        $this->customer_name = '';
        $this->customer_email = '';
        $this->success('Customer created successfully. Welcome email with password reset link has been sent.');
    }

    public function getAvailableTimeSlotsProperty()
    {
        if (!$this->check_in || !$this->selectedBoat || !$this->duration_slot) {
            return collect();
        }

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

        // Generate slots based on duration
        $currentHour = $startHour;
        while ($currentHour + $durationHours <= $endHour) {
            $startTime = Carbon::parse($this->check_in)->setTime(floor($currentHour), ($currentHour - floor($currentHour)) * 60);
            $endTime = $startTime->copy()->addMinutes($durationHours * 60);

            // Add buffer time from boat configuration
            $bufferMinutes = $this->selectedBoat->buffer_time ?? 0;
            $endTimeWithBuffer = $endTime->copy()->addMinutes($bufferMinutes);

            // Check if this slot is already booked (including buffer time)
            $isBooked = Booking::where('bookingable_type', Boat::class)
                ->where('bookingable_id', $this->selectedBoat->id)
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

            // Move to next slot (step by duration)
            $currentHour += $durationHours;
        }

        return $timeSlots;
    }

    public function selectTimeSlot($time)
    {
        $this->selected_time_slot = $time;
        $this->check_in_time = $time;
    }

    public function calculateAmount(): void
    {
        if (!$this->selectedBoat) {
            return;
        }

        $boat = $this->selectedBoat;
        $amount = 0;

        match ($boat->service_type) {
            'yacht', 'taxi', 'ferry' => ($amount = $this->calculateHourlyPrice($boat)),
            'limousine' => ($amount = $this->calculateLimousinePrice($boat)),
            default => ($amount = 0),
        };

        $this->amount = $amount;
    }

    private function calculateHourlyPrice(Boat $boat): float
    {
        // For ferry, calculate based on trip type and weekday/weekend
        if ($boat->service_type === 'ferry') {
            if (!$this->duration_slot || !$this->check_in) {
                return 0;
            }

            $date = Carbon::parse($this->check_in);
            // Weekend = Friday to Saturday, Weekdays = Sunday to Thursday
            $isWeekend = in_array($date->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY]);

            $hours = match ($this->duration_slot) {
                '1h' => 1,
                '2h' => 2,
                '3h' => 3,
                'custom' => $this->custom_hours ?? 0,
                default => 0,
            };

            // Private trip - per hour
            if ($this->booking_type === 'private') {
                $pricePerHour = $isWeekend ? $boat->ferry_private_weekend ?? 0 : $boat->ferry_private_weekday ?? 0;
                return $pricePerHour * $hours;
            }

            // Public trip - per person per hour
            $pricePerPersonPerHour = $isWeekend ? $boat->ferry_public_weekend ?? 0 : $boat->ferry_public_weekday ?? 0;
            return $pricePerPersonPerHour * $hours * $this->adults;
        }

        // For yacht/taxi - fixed hourly pricing
        return match ($this->duration_slot) {
            '1h' => $boat->price_1hour ?? 0,
            '2h' => $boat->price_2hours ?? 0,
            '3h' => $boat->price_3hours ?? 0,
            'custom' => ($boat->additional_hour_price ?? 0) * ($this->custom_hours ?? 0),
            default => 0,
        };
    }

    private function calculateLimousinePrice(Boat $boat): float
    {
        return match ($this->duration_slot) {
            '15min' => $boat->price_15min ?? 0,
            '30min' => $boat->price_30min ?? 0,
            '1hour_full' => $boat->price_full_boat ?? 0,
            default => 0,
        };
    }

    public function save(): void
    {
        $validated = $this->validate([
            'boat_id' => 'required|exists:boats,id',
            'user_id' => 'required|exists:users,id',
            'check_in' => 'required|date',
            'check_in_time' => 'required',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,online,other',
            'payment_status' => 'required|in:pending,paid,failed',
            'duration_slot' => 'nullable|string',
            'booking_type' => 'nullable|string',
        ]);

        $checkInDateTime = Carbon::parse($validated['check_in'] . ' ' . $validated['check_in_time']);

        // Calculate checkout time based on duration and add buffer for yachts
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
        if ($this->selectedBoat->buffer_time) {
            $checkOutDateTime->addMinutes($this->selectedBoat->buffer_time);
        }

        $guestDetails = [
            'adults' => array_values(array_filter($this->adultNames)),
            'children' => array_values(array_filter($this->childrenNames)),
        ];

        $notesText = $this->notes ?? '';
        $notesText .= "\n\nDuration/Slot: " . ($this->duration_slot ?? 'N/A');
        if ($this->duration_slot === 'custom' && $this->custom_hours) {
            $notesText .= " ({$this->custom_hours} hours)";
        }
        if ($this->booking_type) {
            $notesText .= "\nBooking Type: " . $this->booking_type;
        }

        $booking = Booking::create([
            'bookingable_type' => Boat::class,
            'bookingable_id' => $validated['boat_id'],
            'user_id' => $validated['user_id'],
            'check_in' => $checkInDateTime,
            'check_out' => $checkOutDateTime,
            'adults' => $validated['adults'],
            'children' => $validated['children'],
            'guest_details' => $guestDetails,
            'price' => $validated['amount'],
            'total_amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_status'],
            'status' => BookingStatusEnum::BOOKED->value,
            'notes' => $notesText,
        ]);

        $this->success('Boat booking created successfully.', redirectTo: route('admin.bookings.boat.show', $booking->id));
    }

    public function with(): array
    {
        // Duration options based on boat type
        $durationOptions = [];
        if ($this->selectedBoat) {
            $durationOptions = match ($this->selectedBoat->service_type) {
                'yacht', 'taxi' => [['id' => '1h', 'name' => '1 Hour - KD ' . number_format($this->selectedBoat->price_1hour, 2)], ['id' => '2h', 'name' => '2 Hours - KD ' . number_format($this->selectedBoat->price_2hours, 2)], ['id' => '3h', 'name' => '3 Hours - KD ' . number_format($this->selectedBoat->price_3hours, 2)], ['id' => 'custom', 'name' => 'Custom Hours (KD ' . number_format($this->selectedBoat->additional_hour_price, 2) . '/hour)']],
                'ferry' => [['id' => '1h', 'name' => '1 Hour'], ['id' => '2h', 'name' => '2 Hours'], ['id' => '3h', 'name' => '3 Hours'], ['id' => 'custom', 'name' => 'Custom Hours']],
                'limousine' => [['id' => '15min', 'name' => '15 Minutes - KD ' . number_format($this->selectedBoat->price_15min, 2)], ['id' => '30min', 'name' => '30 Minutes - KD ' . number_format($this->selectedBoat->price_30min, 2)], ['id' => '1hour_full', 'name' => '1 Hour / Full Boat - KD ' . number_format($this->selectedBoat->price_full_boat, 2)]],
                default => [],
            };
        }

        // Get all active service types
        $serviceTypes = BoatServiceType::active()
            ->ordered()
            ->get()
            ->map(function ($type) {
                return [
                    'id' => $type->slug,
                    'name' => $type->name,
                ];
            });

        // Filter boats by selected service type
        $boatsQuery = Boat::active()->orderBy('name');

        if ($this->service_type_slug) {
            $boatsQuery->where('service_type', $this->service_type_slug);
        }

        $boats = $boatsQuery->get()->map(function ($boat) {
            return [
                'id' => $boat->id,
                'name' => $boat->name,
            ];
        });

        $users = User::role(RolesEnum::CUSTOMER->value)->orderBy('name')->get();

        // Duration options based on boat type
        $durationOptions = [];
        if ($this->selectedBoat) {
            $durationOptions = match ($this->selectedBoat->service_type) {
                'yacht', 'taxi' => [['id' => '1h', 'name' => '1 Hour - KD ' . number_format($this->selectedBoat->price_1hour, 2)], ['id' => '2h', 'name' => '2 Hours - KD ' . number_format($this->selectedBoat->price_2hours, 2)], ['id' => '3h', 'name' => '3 Hours - KD ' . number_format($this->selectedBoat->price_3hours, 2)], ['id' => 'custom', 'name' => 'Custom Hours (KD ' . number_format($this->selectedBoat->additional_hour_price, 2) . '/hour)']],
                'ferry' => [['id' => '1h', 'name' => '1 Hour'], ['id' => '2h', 'name' => '2 Hours'], ['id' => '3h', 'name' => '3 Hours'], ['id' => 'custom', 'name' => 'Custom Hours']],
                'limousine' => [['id' => '15min', 'name' => '15 Minutes - KD ' . number_format($this->selectedBoat->price_15min, 2)], ['id' => '30min', 'name' => '30 Minutes - KD ' . number_format($this->selectedBoat->price_30min, 2)], ['id' => '1hour_full', 'name' => '1 Hour / Full Boat - KD ' . number_format($this->selectedBoat->price_full_boat, 2)]],
                default => [],
            };
        }

        $paymentMethods = [['id' => 'cash', 'name' => 'Cash'], ['id' => 'card', 'name' => 'Credit/Debit Card'], ['id' => 'online', 'name' => 'Online Payment'], ['id' => 'other', 'name' => 'Other']];

        $paymentStatuses = [['id' => 'pending', 'name' => 'Pending'], ['id' => 'paid', 'name' => 'Paid']];

        return [
            'serviceTypes' => $serviceTypes,
            'boats' => $boats,
            'users' => $users,
            'durationOptions' => $durationOptions,
            'paymentMethods' => $paymentMethods,
            'paymentStatuses' => $paymentStatuses,
            'breadcrumbs' => [['label' => 'Dashboard', 'url' => route('admin.index')], ['label' => 'Boat Bookings', 'url' => route('admin.bookings.boat.index')], ['label' => 'Create Booking']],
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
                'label' => 'Create Booking',
            ],
        ];
    @endphp

    <x-header title="Create Boat Booking" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Create a new boat booking</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.boat.index') }}"
                class="btn-ghost btn-outline" />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="mx-auto">
        <x-form wire:submit="save">
            <div class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-3 lg:items-start">
                    <div class="space-y-6 lg:col-span-2">
                        {{-- Boat Selection Section --}}
                        <x-card class="bg-base-200">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step 1</p>
                                    <h3 class="text-xl font-semibold text-base-content mt-1">Boat Selection</h3>
                                    <p class="text-sm text-base-content/60 mt-1">Choose service type and boat</p>
                                </div>
                                <x-icon name="o-archive-box" class="w-8 h-8 text-primary/70" />
                            </div>
                            <div class="mt-6 space-y-4">
                                {{-- Service Type Selection --}}
                                <x-choices-offline label="Select Service Type *" icon="o-tag"
                                    wire:model.live="service_type_slug" :options="$serviceTypes"
                                    placeholder="Choose service type..." single searchable />

                                {{-- Boat Selection (shown after service type selected) --}}
                                @if ($service_type_slug)
                                    <x-choices-offline label="Select Boat *" icon="o-archive-box"
                                        wire:model.live="boat_id" :options="$boats"
                                        placeholder="Search and select boat..." single searchable />
                                @endif
                            </div>

                            @if ($selectedBoat)
                                <x-alert icon="o-information-circle" class="alert-info mt-4">
                                    <strong>{{ $selectedBoat->name }}</strong> -
                                    {{ $selectedBoat->service_type_label }}<br>
                                    <span class="text-sm">Capacity:
                                        {{ $selectedBoat->min_passengers }}-{{ $selectedBoat->max_passengers }}
                                        passengers</span>
                                </x-alert>
                            @endif
                        </x-card>

                        {{-- Customer Selection Section --}}
                        <x-booking.customer-section stepNumber="2" :customers="$users" />


                        @if ($selectedBoat)
                            {{-- Yacht / Taxi / Ferry (Hourly Booking) --}}
                            @if (in_array($selectedBoat->service_type, ['yacht', 'taxi', 'ferry']))
                                {{-- Ferry Trip Type Selection --}}
                                @if ($selectedBoat->service_type === 'ferry')
                                    <x-card class="bg-base-200">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-xs uppercase tracking-wide text-primary font-semibold">
                                                    Step 3
                                                </p>
                                                <h3 class="text-xl font-semibold text-base-content mt-1">Trip Type
                                                </h3>
                                                <p class="text-sm text-base-content/60 mt-1">Select private or public
                                                    trip</p>
                                            </div>
                                            <x-icon name="o-user-group" class="w-8 h-8 text-primary/70" />
                                        </div>
                                        <div class="mt-6">
                                            <x-select label="Select Trip Type *" icon="o-user-group"
                                                wire:model.live="booking_type" :options="[
                                                    ['id' => 'private', 'name' => '🔒 Private Trip (Per Hour)'],
                                                    ['id' => 'public', 'name' => '👥 Public Trip (Per Person/Hour)'],
                                                ]"
                                                placeholder="Choose trip type..." />
                                        </div>
                                    </x-card>
                                @endif

                                {{-- Step 3/4: Duration Selection --}}
                                <x-card class="bg-base-200">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-primary font-semibold">
                                                {{ $selectedBoat->service_type === 'ferry' ? 'Step 4' : 'Step 3' }}
                                            </p>
                                            <h3 class="text-xl font-semibold text-base-content mt-1">Select Duration
                                            </h3>
                                            <p class="text-sm text-base-content/60 mt-1">Choose booking duration with
                                                pricing</p>
                                        </div>
                                        <x-icon name="o-clock" class="w-8 h-8 text-primary/70" />
                                    </div>
                                    <div class="mt-6">
                                        <x-choices-offline label="Select Duration *" icon="o-clock" :options="$durationOptions"
                                            wire:model.live="duration_slot" placeholder="Choose duration..." single
                                            searchable />
                                    </div>

                                    @if ($duration_slot === 'custom')
                                        <div class="mt-4">
                                            <x-input label="Number of Hours *" icon="o-clock" type="number"
                                                wire:model.live="custom_hours" min="1" max="12"
                                                hint="Enter custom duration (KD {{ number_format($selectedBoat->additional_hour_price, 2) }} per hour)" />
                                        </div>
                                    @endif
                                </x-card>

                                {{-- Step 5: Date Selection --}}
                                @if ($duration_slot)
                                    <x-card class="bg-base-200">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-xs uppercase tracking-wide text-primary font-semibold">
                                                    Step 5</p>
                                                <h3 class="text-xl font-semibold text-base-content mt-1">Select Date &
                                                    Time Slot</h3>
                                                <p class="text-sm text-base-content/60 mt-1">Choose your preferred date
                                                    and time</p>
                                            </div>
                                            <x-icon name="o-calendar" class="w-8 h-8 text-primary/70" />
                                        </div>
                                        <div class="mt-6">
                                            <x-input label="Booking Date *" icon="o-calendar" type="date"
                                                wire:model.live="check_in" hint="Select date" />

                                            {{-- Step 3: Time Slots based on duration --}}
                                            @if ($check_in && $this->availableTimeSlots->isNotEmpty())
                                                <div class="mt-4">
                                                    <label class="label">
                                                        <span class="label-text font-semibold">
                                                            <x-icon name="o-clock" class="w-4 h-4 inline mr-1" />
                                                            Available Time Slots
                                                            ({{ $duration_slot === 'custom'
                                                                ? $custom_hours . ' Hours'
                                                                : match ($duration_slot) {
                                                                    '1h' => '1 Hour',
                                                                    '2h' => '2 Hours',
                                                                    '3h' => '3 Hours',
                                                                    default => '',
                                                                } }})
                                                            *
                                                        </span>
                                                    </label>
                                                    <div
                                                        class="max-h-64 overflow-y-auto border border-base-300 rounded-lg">
                                                        @foreach ($this->availableTimeSlots as $slot)
                                                            <div wire:key="slot-{{ $slot['start_time'] }}"
                                                                class="flex items-center justify-between p-3 border-b border-base-200 hover:bg-base-200/50 transition-colors
                                                                {{ $selected_time_slot === $slot['value'] ? 'bg-primary/10 border-l-4 border-l-primary' : '' }}
                                                                {{ !$slot['is_available'] ? 'opacity-50' : 'cursor-pointer' }}"
                                                                @if ($slot['is_available']) wire:click="selectTimeSlot('{{ $slot['value'] }}')" @endif>

                                                                <div class="flex items-center gap-3">
                                                                    <x-icon
                                                                        name="{{ $selected_time_slot === $slot['value'] ? 'o-check-circle' : 'o-clock' }}"
                                                                        class="w-5 h-5 {{ $selected_time_slot === $slot['value'] ? 'text-primary' : 'text-base-content/50' }}" />
                                                                    <span
                                                                        class="font-medium">{{ $slot['display'] }}</span>
                                                                </div>

                                                                <div>
                                                                    @if ($slot['is_available'])
                                                                        <x-badge value="Available"
                                                                            class="badge-success badge-sm" />
                                                                    @else
                                                                        <x-badge value="Booked"
                                                                            class="badge-error badge-sm" />
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    @if (!$selected_time_slot)
                                                        <div class="label">
                                                            <span class="label-text-alt text-warning">Please select a
                                                                time slot</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @elseif ($check_in && $this->availableTimeSlots->isEmpty())
                                                <x-alert icon="o-exclamation-triangle" class="alert-warning mt-4">
                                                    No available time slots for selected date and duration.
                                                </x-alert>
                                            @endif
                                        </div>
                                    </x-card>
                                @endif
                            @endif

                            {{-- Limousine Service (Time-based) --}}
                            @if ($selectedBoat->service_type === 'limousine')
                                {{-- Step 3: Duration Selection --}}
                                <x-card class="bg-base-200">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step
                                                3</p>
                                            <h3 class="text-xl font-semibold text-base-content mt-1">Select Service
                                                Duration</h3>
                                            <p class="text-sm text-base-content/60 mt-1">Choose service duration</p>
                                        </div>
                                        <x-icon name="o-clock" class="w-8 h-8 text-primary/70" />
                                    </div>
                                    <div class="mt-6">
                                        <x-choices-offline label="Select Duration *" icon="o-clock" :options="$durationOptions"
                                            wire:model.live="duration_slot" placeholder="Choose duration..." single
                                            searchable />
                                    </div>
                                </x-card>

                                {{-- Step 4/5: Date & Time Slot Selection --}}
                                @if ($duration_slot)
                                    <x-card class="bg-base-200">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="text-xs uppercase tracking-wide text-primary font-semibold">
                                                    {{ $selectedBoat && $selectedBoat->service_type === 'ferry' ? 'Step 5' : 'Step 4' }}
                                                </p>
                                                <h3 class="text-xl font-semibold text-base-content mt-1">Select Date &
                                                    Time Slot</h3>
                                                <p class="text-sm text-base-content/60 mt-1">Choose your preferred date
                                                    and time</p>
                                            </div>
                                            <x-icon name="o-calendar" class="w-8 h-8 text-primary/70" />
                                        </div>
                                        <div class="mt-6">
                                            <x-input label="Service Date *" icon="o-calendar" type="date"
                                                wire:model.live="check_in" hint="Select date" />

                                            {{-- Time Slots based on duration --}}
                                            @if ($check_in && $this->availableTimeSlots->isNotEmpty())
                                                <div class="mt-4">
                                                    <label class="label">
                                                        <span class="label-text font-semibold">
                                                            <x-icon name="o-clock" class="w-4 h-4 inline mr-1" />
                                                            Available Time Slots
                                                            ({{ $duration_slot === '15min' ? '15 Minutes' : ($duration_slot === '30min' ? '30 Minutes' : '1 Hour') }})
                                                            *
                                                        </span>
                                                    </label>
                                                    <div
                                                        class="max-h-64 overflow-y-auto border border-base-300 rounded-lg">
                                                        @foreach ($this->availableTimeSlots as $slot)
                                                            <div wire:key="slot-{{ $slot['start_time'] }}"
                                                                class="flex items-center justify-between p-3 border-b border-base-200 hover:bg-base-200/50 transition-colors
                                                                {{ $selected_time_slot === $slot['value'] ? 'bg-primary/10 border-l-4 border-l-primary' : '' }}
                                                                {{ !$slot['is_available'] ? 'opacity-50' : 'cursor-pointer' }}"
                                                                @if ($slot['is_available']) wire:click="selectTimeSlot('{{ $slot['value'] }}')" @endif>

                                                                <div class="flex items-center gap-3">
                                                                    <x-icon
                                                                        name="{{ $selected_time_slot === $slot['value'] ? 'o-check-circle' : 'o-clock' }}"
                                                                        class="w-5 h-5 {{ $selected_time_slot === $slot['value'] ? 'text-primary' : 'text-base-content/50' }}" />
                                                                    <span
                                                                        class="font-medium">{{ $slot['display'] }}</span>
                                                                </div>

                                                                <div>
                                                                    @if ($slot['is_available'])
                                                                        <x-badge value="Available"
                                                                            class="badge-success badge-sm" />
                                                                    @else
                                                                        <x-badge value="Booked"
                                                                            class="badge-error badge-sm" />
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    @if (!$selected_time_slot)
                                                        <div class="label">
                                                            <span class="label-text-alt text-warning">Please select a
                                                                time slot</span>
                                                        </div>
                                                    @endif
                                                </div>
                                            @elseif ($check_in && $this->availableTimeSlots->isEmpty())
                                                <x-alert icon="o-exclamation-triangle" class="alert-warning mt-4">
                                                    No available time slots for selected date and duration.
                                                </x-alert>
                                            @endif
                                        </div>
                                    </x-card>
                                @endif
                            @endif
                        @endif

                        {{-- Universal Guests Section (for all boat types) --}}
                        @if ($selectedBoat)
                            @php
                                $maxAdults = $selectedBoat->max_passengers ?? 10;
                                $maxChildren = $selectedBoat->max_passengers ?? 10;
                            @endphp
                            <x-booking.guest-section :stepNumber="$selectedBoat->service_type === 'ferry' ? 6 : 5" :maxAdults="$maxAdults" :maxChildren="$maxChildren"
                                :adults="$adults" :children="$children" />
                        @endif

                        {{-- Payment Section --}}
                        <x-card class="bg-base-200">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-primary font-semibold">
                                        Step 5
                                    </p>
                                    <h3 class="text-xl font-semibold text-base-content mt-1">Payment Details</h3>
                                    <p class="text-sm text-base-content/60 mt-1">Payment information for this booking
                                    </p>
                                </div>
                                <x-icon name="o-credit-card" class="w-8 h-8 text-primary/70" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                                <x-input wire:model.live="amount" label="Amount" type="number" step="0.01"
                                    min="0" icon="o-currency-dollar"
                                    hint="Total booking amount (auto-calculated)" />
                                <x-select label="Payment Method *" :options="$paymentMethods" option-value="id"
                                    option-label="name" wire:model="payment_method" icon="o-credit-card" />
                                <x-select label="Payment Status *" :options="$paymentStatuses" option-value="id"
                                    option-label="name" wire:model="payment_status" icon="o-check-circle" />
                            </div>
                        </x-card>

                        {{-- Notes Section --}}
                        <x-card class="bg-base-200">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-primary font-semibold">
                                        {{ $selectedBoat && $selectedBoat->service_type === 'ferry' ? 'Step 7' : 'Step 6' }}
                                    </p>
                                    <h3 class="text-xl font-semibold text-base-content mt-1">Additional Notes</h3>
                                    <p class="text-sm text-base-content/60 mt-1">Special requests or information</p>
                                </div>
                                <x-icon name="o-document-text" class="w-8 h-8 text-primary/70" />
                            </div>
                            <div class="mt-6">
                                <x-textarea label="Special Notes" icon="o-document-text" wire:model="notes"
                                    rows="3" placeholder="Add any special notes, requirements, or requests..."
                                    hint="Optional" />
                            </div>
                        </x-card>
                    </div>

                    {{-- Summary Column --}}
                    <div class="sticky top-24">
                        <x-card class="bg-gradient-to-br from-primary/5 to-primary/10">
                            <h3 class="text-lg font-bold text-base-content mb-4">Booking Summary</h3>

                            @if ($amount > 0)
                                <div class="bg-success/10 rounded-lg p-4 mb-4">
                                    <div class="text-sm text-base-content/60 mb-1">Total Amount</div>
                                    <div class="text-3xl font-bold text-success">KD {{ number_format($amount, 2) }}
                                    </div>
                                </div>
                            @endif

                            <div class="space-y-3 text-sm">
                                @if ($selectedBoat)
                                    <div class="flex items-center gap-2 text-base-content/70">
                                        <x-icon name="o-archive-box" class="w-4 h-4" />
                                        <span>{{ $selectedBoat->name }}</span>
                                    </div>
                                @endif

                                @if ($check_in)
                                    <div class="flex items-center gap-2 text-base-content/70">
                                        <x-icon name="o-calendar" class="w-4 h-4" />
                                        <span>{{ \Carbon\Carbon::parse($check_in)->format('M d, Y') }}</span>
                                    </div>
                                @endif

                                @if ($adults > 0)
                                    <div class="flex items-center gap-2 text-base-content/70">
                                        <x-icon name="o-user-group" class="w-4 h-4" />
                                        <span>{{ $adults }}
                                            Adults{{ $children > 0 ? ', ' . $children . ' Children' : '' }}</span>
                                    </div>
                                @endif
                            </div>
                        </x-card>

                        <x-card class="mt-4">
                            <div class="space-y-2">
                                <x-button label="Create Booking" type="submit" icon="o-check-circle"
                                    class="btn-primary w-full btn-lg" spinner="save" />
                                <x-button label="Cancel" link="{{ route('admin.bookings.boat.index') }}"
                                    icon="o-x-mark" class="btn-ghost w-full" />
                            </div>
                        </x-card>

                        <x-card class="mt-4">
                            <h4 class="text-sm font-semibold text-base-content mb-3">Information</h4>
                            <div class="text-xs space-y-2 text-base-content/70">
                                <p>• All fields marked with * are required</p>
                                <p>• Booking will be created with confirmed status</p>
                                <p>• Customer will receive confirmation email</p>
                                <p>• You can modify booking details later</p>
                            </div>
                        </x-card>
                    </div>
                </div>
            </div>
        </x-form>
    </x-card>

    {{-- Create Customer Modal --}}
    <x-booking.create-customer-modal />
</div>
