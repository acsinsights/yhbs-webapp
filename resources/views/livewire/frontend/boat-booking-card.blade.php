<?php

use Carbon\Carbon;
use Livewire\Volt\Component;
use App\Models\{Boat, Booking};
use Livewire\Attributes\Computed;

new class extends Component {
    public Boat $boat;
    public string $serviceType;

    // Form fields
    public ?string $tripType = null;
    public ?string $duration = null;
    public ?string $experienceDuration = null;
    public ?string $bookingDate = null;
    public ?string $startTime = null;
    public int $passengers = 1;
    public array $passengerNames = [];

    // UI state
    public array $availableTimeSlots = [];
    public bool $loadingTimeSlots = false;
    public string $errorMessage = '';

    public function mount(Boat $boat): void
    {
        $this->boat = $boat;
        $this->serviceType = $boat->service_type;
        $this->passengers = $boat->min_passengers ?? 1;

        // Initialize passenger names array
        $this->updatePassengerNames();
    }

    #[Computed]
    public function minDate(): string
    {
        if ($this->boat->allows_same_day_booking) {
            return now()->format('Y-m-d');
        }
        return now()->addDay()->format('Y-m-d');
    }

    public function updatedPassengers(): void
    {
        $this->updatePassengerNames();
    }

    public function updatePassengerNames(): void
    {
        $currentCount = count($this->passengerNames);

        if ($this->passengers > $currentCount) {
            // Add more fields
            for ($i = $currentCount; $i < $this->passengers; $i++) {
                $this->passengerNames[$i] = '';
            }
        } elseif ($this->passengers < $currentCount) {
            // Remove excess fields
            $this->passengerNames = array_slice($this->passengerNames, 0, $this->passengers);
        }
    }

    public function updatedBookingDate(): void
    {
        if ($this->bookingDate && $this->shouldLoadTimeSlots()) {
            $this->loadTimeSlots();
        }
    }

    public function updatedDuration(): void
    {
        if ($this->shouldLoadTimeSlots()) {
            $this->loadTimeSlots();
        }
    }

    public function updatedExperienceDuration(): void
    {
        if ($this->shouldLoadTimeSlots()) {
            $this->loadTimeSlots();
        }
    }

    public function updatedTripType(): void
    {
        if ($this->shouldLoadTimeSlots()) {
            $this->loadTimeSlots();
        }
    }

    private function shouldLoadTimeSlots(): bool
    {
        if (!$this->bookingDate) {
            return false;
        }

        // Check if we have duration based on service type
        if (in_array($this->serviceType, ['yacht', 'taxi'])) {
            return !empty($this->duration);
        } elseif ($this->serviceType === 'ferry') {
            return !empty($this->tripType) && !empty($this->duration);
        } elseif ($this->serviceType === 'limousine') {
            return !empty($this->tripType) && !empty($this->experienceDuration);
        }

        return false;
    }

    public function loadTimeSlots(): void
    {
        $this->loadingTimeSlots = true;
        $this->availableTimeSlots = [];
        $this->startTime = null;

        try {
            // Get duration in hours
            $durationHours = $this->getDurationInHours();

            if (!$durationHours) {
                $this->loadingTimeSlots = false;
                return;
            }

            // Generate time slots
            $timeSlots = [];
            $startHour = 9; // 9 AM
            $endHour = 18; // 6 PM
            $currentHour = $startHour;

            // Get buffer time in hours
            $bufferMinutes = $this->boat->buffer_time ?? 0;
            $bufferHours = $bufferMinutes / 60;

            while ($currentHour + $durationHours <= $endHour) {
                $startTime = Carbon::parse($this->bookingDate)->setTime(floor($currentHour), ($currentHour - floor($currentHour)) * 60);
                $endTime = $startTime->copy()->addMinutes($durationHours * 60);

                // Add buffer time for checking conflicts
                $endTimeWithBuffer = $endTime->copy()->addMinutes($bufferMinutes);

                // Check if slot is in the past
                $isPast = false;
                if (Carbon::parse($this->bookingDate)->isToday()) {
                    $isPast = $startTime->lessThanOrEqualTo(now());
                }

                // Check if this slot is already booked (including buffer time)
                $isBooked = Booking::where('bookingable_type', Boat::class)
                    ->where('bookingable_id', $this->boat->id)
                    ->where('status', '!=', 'cancelled')
                    ->whereDate('check_in', $this->bookingDate)
                    ->where(function ($query) use ($startTime, $endTimeWithBuffer) {
                        $query->where('check_in', '<', $endTimeWithBuffer)->where('check_out', '>', $startTime);
                    })
                    ->exists();

                $isAvailable = !$isBooked && !$isPast;

                $timeSlots[] = [
                    'value' => $startTime->format('H:i'),
                    'display' => $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A'),
                    'is_available' => $isAvailable,
                ];

                // Move to next slot (step by duration + buffer time)
                $currentHour += $durationHours + $bufferHours;
            }

            $this->availableTimeSlots = $timeSlots;
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to load time slots. Please try again.';
        } finally {
            $this->loadingTimeSlots = false;
        }
    }

    private function getDurationInHours(): ?float
    {
        if (in_array($this->serviceType, ['yacht', 'taxi', 'ferry'])) {
            return $this->duration ? (float) $this->duration : null;
        } elseif ($this->serviceType === 'limousine') {
            return match ($this->experienceDuration) {
                '15' => 0.25,
                '30' => 0.5,
                'full' => 1,
                default => null,
            };
        }

        return null;
    }

    #[Computed]
    public function totalAmount(): ?float
    {
        if (!$this->bookingDate || !$this->passengers) {
            return null;
        }

        $basePrice = 0;

        // Calculate base price based on service type and selection
        if ($this->serviceType === 'yacht' || $this->serviceType === 'taxi') {
            if ($this->duration == 1 && $this->boat->price_1hour) {
                $basePrice = $this->boat->price_1hour;
            } elseif ($this->duration == 2 && $this->boat->price_2hours) {
                $basePrice = $this->boat->price_2hours;
            } elseif ($this->duration == 3 && $this->boat->price_3hours) {
                $basePrice = $this->boat->price_3hours;
            }
        } elseif ($this->serviceType === 'ferry' && $this->duration && $this->tripType) {
            $isWeekend = Carbon::parse($this->bookingDate)->isWeekend();

            if ($this->tripType === 'private') {
                $basePrice = $isWeekend ? $this->boat->ferry_private_weekend : $this->boat->ferry_private_weekday;
            } else {
                $basePrice = $isWeekend ? $this->boat->ferry_public_weekend : $this->boat->ferry_public_weekday;
            }

            // Multiply by duration for ferry
            $basePrice = $basePrice * (float) $this->duration;
        } elseif ($this->serviceType === 'limousine' && $this->experienceDuration && $this->tripType) {
            if ($this->experienceDuration == '15' && $this->boat->price_15min) {
                $basePrice = $this->boat->price_15min;
            } elseif ($this->experienceDuration == '30' && $this->boat->price_30min) {
                $basePrice = $this->boat->price_30min;
            } elseif ($this->experienceDuration == 'full' && $this->boat->price_full_boat) {
                $basePrice = $this->boat->price_full_boat;
            }

            // For public trips, multiply by passengers
            if ($this->tripType === 'public') {
                $basePrice = $basePrice * $this->passengers;
            }
        }

        return $basePrice > 0 ? $basePrice : null;
    }

    public function proceedToBooking(): void
    {
        // Validate
        $this->validate([
            'bookingDate' => 'required|date|after_or_equal:' . $this->minDate(),
            'passengers' => 'required|integer|min:' . ($this->boat->min_passengers ?? 1) . '|max:' . ($this->boat->max_passengers ?? 20),
            'passengerNames' => 'required|array|min:' . $this->passengers,
            'passengerNames.*' => 'required|string|max:255',
        ]);

        // Service-specific validation
        if (in_array($this->serviceType, ['yacht', 'taxi', 'ferry', 'limousine'])) {
            $this->validate([
                'startTime' => 'required',
            ]);
        }

        if ($this->serviceType === 'ferry') {
            $this->validate([
                'tripType' => 'required',
                'duration' => 'required',
            ]);
        } elseif ($this->serviceType === 'limousine') {
            $this->validate([
                'tripType' => 'required',
                'experienceDuration' => 'required',
            ]);
        } elseif (in_array($this->serviceType, ['yacht', 'taxi'])) {
            $this->validate([
                'duration' => 'required',
            ]);
        }

        // Redirect to checkout with data
        $params = [
            'type' => 'boat',
            'id' => $this->boat->id,
            'check_in' => $this->bookingDate,
            'check_out' => $this->bookingDate,
            'adults' => $this->passengers,
            'children' => 0,
            'adult_names' => array_values($this->passengerNames),
            'start_time' => $this->startTime,
        ];

        if ($this->duration) {
            $params['duration'] = $this->duration;
        }

        if ($this->tripType) {
            $params['trip_type'] = $this->tripType;
        }

        if ($this->experienceDuration) {
            $params['experience_duration'] = $this->experienceDuration;
        }

        $this->redirect(route('checkout', $params), navigate: false);
    }
}; ?>

<div class="booking-form-wrap border rounded p-4 shadow-sm mb-4">
    <h5 class="mb-4">Book This Boat</h5>

    @guest
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Please <a href="{{ route('customer.login') }}" class="alert-link">login</a> to book this boat.
        </div>
    @else
        <form wire:submit="proceedToBooking">
            {{-- Step 1: Trip Type (Ferry & Limousine only) --}}
            @if ($serviceType === 'ferry')
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-ticket text-primary"></i> Select Trip Type
                    </label>
                    <select wire:model.live="tripType" class="form-control" required>
                        <option value="">Choose trip type...</option>
                        <option value="private">Private Trip</option>
                        <option value="public">Public Trip</option>
                    </select>
                    @error('tripType')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            @endif

            @if ($serviceType === 'limousine')
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-ticket text-primary"></i> Select Trip Type
                    </label>
                    <select wire:model.live="tripType" class="form-control" required>
                        <option value="">Choose trip type...</option>
                        <option value="private">Private Trip</option>
                        <option value="public">Public Trip</option>
                    </select>
                    @error('tripType')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            @endif

            {{-- Step 2: Duration Selection --}}
            @if (in_array($serviceType, ['yacht', 'taxi']))
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-hourglass-split text-primary"></i> Select Duration
                    </label>
                    <select wire:model.live="duration" class="form-control" required>
                        <option value="">Choose duration...</option>
                        @if ($boat->price_1hour)
                            <option value="1">1 Hour - {{ currency_format($boat->price_1hour) }}</option>
                        @endif
                        @if ($boat->price_2hours)
                            <option value="2">2 Hours - {{ currency_format($boat->price_2hours) }}</option>
                        @endif
                        @if ($boat->price_3hours)
                            <option value="3">3 Hours - {{ currency_format($boat->price_3hours) }}</option>
                        @endif
                    </select>
                    @error('duration')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            @endif

            @if ($serviceType === 'ferry')
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-hourglass-split text-primary"></i> Select Duration
                    </label>
                    <select wire:model.live="duration" class="form-control" required>
                        <option value="">Choose duration...</option>
                        <option value="1">1 Hour</option>
                        <option value="2">2 Hours</option>
                        <option value="3">3 Hours</option>
                    </select>
                    @error('duration')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            @endif

            @if ($serviceType === 'limousine')
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-star text-primary"></i> Select Experience Duration
                    </label>
                    <select wire:model.live="experienceDuration" class="form-control" required>
                        <option value="">Choose experience...</option>
                        @if ($boat->price_15min)
                            <option value="15">15 Minutes - {{ currency_format($boat->price_15min) }}</option>
                        @endif
                        @if ($boat->price_30min)
                            <option value="30">30 Minutes - {{ currency_format($boat->price_30min) }}</option>
                        @endif
                        @if ($boat->price_full_boat)
                            <option value="full">Full Experience - {{ currency_format($boat->price_full_boat) }}</option>
                        @endif
                    </select>
                    @error('experienceDuration')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            @endif

            {{-- Step 3: Date Selection --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-calendar-event text-primary"></i> Select Date
                </label>
                <input type="date" wire:model.live="bookingDate" class="form-control" required
                    min="{{ $this->minDate() }}">
                @error('bookingDate')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Step 4: Time Slot Selection --}}
            @if (in_array($serviceType, ['yacht', 'taxi', 'ferry', 'limousine']))
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-clock text-primary"></i> Select Time Slot
                    </label>

                    @if ($loadingTimeSlots)
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <small class="d-block mt-2 text-muted">Loading available time slots...</small>
                        </div>
                    @elseif (count($availableTimeSlots) > 0)
                        <div class="border rounded" style="max-height: 300px; overflow-y: auto;">
                            @foreach ($availableTimeSlots as $slot)
                                <div class="p-2 border-bottom {{ $slot['is_available'] ? 'cursor-pointer' : 'bg-light' }}"
                                    @if ($slot['is_available']) wire:click="$set('startTime', '{{ $slot['value'] }}')"
                                        style="cursor: pointer;" @endif>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <input type="radio" name="time_slot" value="{{ $slot['value'] }}"
                                                {{ $startTime === $slot['value'] ? 'checked' : '' }}
                                                {{ !$slot['is_available'] ? 'disabled' : '' }}>
                                            <span class="ms-2">{{ $slot['display'] }}</span>
                                        </div>
                                        @if ($slot['is_available'])
                                            <span class="badge bg-success">Available</span>
                                        @else
                                            <span class="badge bg-secondary">Booked</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @elseif ($bookingDate)
                        <div class="alert alert-warning py-2">
                            <small><i class="bi bi-exclamation-triangle me-1"></i> No time slots available for selected date
                                and duration.</small>
                        </div>
                    @endif
                    @error('startTime')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            @endif

            {{-- Step 5: Number of Passengers --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-people text-primary"></i> Number of Passengers
                </label>
                <input type="number" wire:model.live="passengers" class="form-control"
                    min="{{ $boat->min_passengers ?? 1 }}" max="{{ $boat->max_passengers ?? 20 }}" required>
                <small class="text-muted">Min: {{ $boat->min_passengers ?? 1 }}, Max:
                    {{ $boat->max_passengers ?? 20 }}</small>
                @error('passengers')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Step 6: Passenger Names --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-person-badge text-primary"></i> Passenger Names
                </label>
                @foreach ($passengerNames as $index => $name)
                    <input type="text" wire:model="passengerNames.{{ $index }}" class="form-control mb-2"
                        placeholder="Passenger {{ $index + 1 }} Name" required>
                    @error('passengerNames.' . $index)
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                @endforeach
                <small class="text-muted">Enter full name of each passenger</small>
            </div>

            @if ($errorMessage)
                <div class="alert alert-danger">{{ $errorMessage }}</div>
            @endif

            {{-- Total Amount Display --}}
            @if ($this->totalAmount() !== null)
                <div class="alert alert-success mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <strong><i class="bi bi-cash-stack me-2"></i>Total Amount:</strong>
                        <h5 class="mb-0">{{ currency_format($this->totalAmount()) }}</h5>
                    </div>
                </div>
            @endif

            <button type="submit" class="btn btn-primary w-100 py-3">
                <i class="bi bi-calendar-check me-2"></i> Proceed to Booking
            </button>
        </form>
    @endguest
</div>
