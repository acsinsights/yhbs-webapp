<?php

use Carbon\Carbon;
use Livewire\Volt\Component;
use App\Models\{Room, Yacht, House, Booking};

new class extends Component {
    public $bookable; // Can be Room, Yacht, or House
    public string $type; // 'room', 'yacht', or 'house'
    public $bookedDates = [];

    public ?string $check_in = null;
    public ?string $check_out = null;
    public string $check_in_time = '14:00';
    public string $check_out_time = '12:00';
    public int $adults = 1;
    public int $children = 0;
    public array $adultNames = [];
    public array $childrenNames = [];
    public ?int $totalNights = null;
    public ?float $totalPrice = null;
    public bool $isAvailable = true;
    public string $availabilityMessage = '';

    public function mount($bookable, string $type): void
    {
        $this->bookable = $bookable;
        $this->type = $type;

        // Get the appropriate model class
        $modelClass = match ($type) {
            'room' => Room::class,
            'yacht' => Yacht::class,
            'house' => House::class,
            default => throw new \Exception('Invalid bookable type'),
        };

        // Get booked dates
        $query = Booking::where('bookingable_type', $modelClass)
            ->where('bookingable_id', $bookable->id)
            ->whereIn('status', ['pending', 'booked', 'checked_in']);

        $this->bookedDates = $query
            ->get(['check_in', 'check_out'])
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

    public function updatedCheckIn(): void
    {
        $this->validateDates();
        $this->checkAvailability();
        $this->calculatePrice();
    }

    public function updatedCheckOut(): void
    {
        $this->validateDates();
        $this->checkAvailability();
        $this->calculatePrice();
    }

    public function updatedAdults(): void
    {
        $maxAdults = $this->bookable->adults ?? 10;

        if ($this->adults > $maxAdults) {
            $this->adults = $maxAdults;
        }
        if ($this->adults < 1) {
            $this->adults = 1;
        }

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
        $maxChildren = $this->bookable->children ?? 10;

        if ($this->children > $maxChildren) {
            $this->children = $maxChildren;
        }
        if ($this->children < 0) {
            $this->children = 0;
        }

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

    public function updatedCheckInTime(): void
    {
        $this->calculatePrice();
    }

    public function updatedCheckOutTime(): void
    {
        $this->calculatePrice();
    }

    protected function validateDates(): void
    {
        if (!$this->check_in || !$this->check_out) {
            return;
        }

        $checkIn = Carbon::parse($this->check_in);
        $checkOut = Carbon::parse($this->check_out);
        $now = Carbon::now();

        // Check-in must be today or in future
        if ($checkIn->lt($now->startOfDay())) {
            $this->check_in = $now->format('Y-m-d');
            $checkIn = Carbon::parse($this->check_in);
        }

        // Check-out must be after check-in
        if ($checkOut->lte($checkIn)) {
            $this->check_out = $checkIn->copy()->addDay()->format('Y-m-d');
        }
    }

    protected function checkAvailability(): void
    {
        if (!$this->check_in || !$this->check_out) {
            $this->isAvailable = true;
            $this->availabilityMessage = '';
            return;
        }

        $checkIn = Carbon::parse($this->check_in);
        $checkOut = Carbon::parse($this->check_out);

        // Generate range of dates
        $selectedDates = [];
        $current = $checkIn->copy();
        while ($current->lt($checkOut)) {
            $selectedDates[] = $current->format('Y-m-d');
            $current->addDay();
        }

        // Check if any selected date is booked
        $hasConflict = !empty(array_intersect($selectedDates, $this->bookedDates));

        $this->isAvailable = !$hasConflict;

        if ($hasConflict) {
            $this->availabilityMessage = 'Selected dates are not available. Please choose different dates.';
        } else {
            $this->availabilityMessage = ucfirst($this->type) . ' is available for selected dates!';
        }
    }

    protected function calculatePrice(): void
    {
        if (!$this->check_in || !$this->check_out || !$this->isAvailable) {
            $this->totalNights = null;
            $this->totalPrice = null;
            return;
        }

        // Combine date and time for accurate calculation
        $checkInDateTime = Carbon::parse($this->check_in . ' ' . $this->check_in_time);
        $checkOutDateTime = Carbon::parse($this->check_out . ' ' . $this->check_out_time);

        // Calculate the difference in hours
        $totalHours = $checkInDateTime->diffInHours($checkOutDateTime);

        if ($totalHours <= 0) {
            $this->totalNights = null;
            $this->totalPrice = null;
            return;
        }

        // For yachts, calculate based on hours; for rooms/houses, calculate based on nights
        if ($this->type === 'yacht') {
            $this->totalNights = $totalHours; // Store hours instead of nights for yachts
            // Yacht pricing: hourly rate x hours
            $this->totalPrice = $totalHours * ($this->bookable->price_per_hour ?? ($this->bookable->price_per_night ?? 0));
        } else {
            // For rooms and houses: calculate nights (every 24 hours = 1 night)
            $nights = ceil($totalHours / 24);
            $this->totalNights = $nights;
            $this->totalPrice = $nights * ($this->bookable->price_per_night ?? 0);
        }
    }

    public function bookNow()
    {
        $maxAdults = $this->bookable->adults ?? 10;
        $maxChildren = $this->bookable->children ?? 10;

        // Validate
        $this->validate(
            [
                'check_in' => 'required|date|after_or_equal:today',
                'check_out' => 'required|date|after:check_in',
                'check_in_time' => 'required|date_format:H:i',
                'check_out_time' => 'required|date_format:H:i',
                'adults' => "required|integer|min:1|max:$maxAdults",
                'children' => "required|integer|min:0|max:$maxChildren",
                'adultNames.*' => 'required|string|max:255',
                'childrenNames.*' => 'nullable|string|max:255',
            ],
            [
                'adultNames.*.required' => 'Please provide names for all adults.',
                'check_in_time.required' => 'Check-in time is required.',
                'check_out_time.required' => 'Check-out time is required.',
            ],
        );

        if (!$this->isAvailable) {
            $this->dispatch('error', 'Selected dates are not available.');
            return;
        }

        // Combine date and time
        $checkInDateTime = $this->check_in . ' ' . $this->check_in_time;
        $checkOutDateTime = $this->check_out . ' ' . $this->check_out_time;

        // Redirect to checkout with booking details
        $queryParams = [
            'type' => $this->type,
            'id' => $this->bookable->id,
            'check_in' => $checkInDateTime,
            'check_out' => $checkOutDateTime,
            'adults' => $this->adults,
            'children' => $this->children,
            'adult_names' => array_values(array_filter($this->adultNames)),
            'children_names' => array_values(array_filter($this->childrenNames)),
        ];

        return redirect()->route('checkout', $queryParams);
    }
}; ?>

<div class="booking-form-wrap mb-4 p-4 border rounded">
    <h4 class="mb-3">Book This {{ ucfirst($type) }}</h4>
    <div class="price-display mb-3 text-center">
        @if ($type === 'yacht')
            <h2 class="text-primary">
                {{ currency_format($bookable->price_per_hour ?? ($bookable->price_per_night ?? 0)) }}
            </h2>
            <p class="text-muted">per hour</p>
        @else
            <h2 class="text-primary">{{ currency_format($bookable->price_per_night) }}</h2>
            <p class="text-muted">per night</p>
        @endif
    </div>

    @if ($bookable->is_active)
        <form wire:submit="bookNow">
            <!-- Date Range Picker -->
            <div class="mb-3">
                <label class="form-label">
                    <i class="bi bi-calendar-range me-2"></i>Select Dates (Check-in to Check-out)
                </label>
                <input type="text" class="form-control" required placeholder="Select check-in and check-out dates"
                    id="dateRangePicker-{{ $bookable->id }}" data-booked-dates='@json($bookedDates)' readonly>
                <input type="hidden" wire:model="check_in" id="checkInDate-{{ $bookable->id }}">
                <input type="hidden" wire:model="check_out" id="checkOutDate-{{ $bookable->id }}">
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const picker = document.getElementById('dateRangePicker-{{ $bookable->id }}');
                    const checkInInput = document.getElementById('checkInDate-{{ $bookable->id }}');
                    const checkOutInput = document.getElementById('checkOutDate-{{ $bookable->id }}');
                    const bookedDates = JSON.parse(picker.getAttribute('data-booked-dates') || '[]');

                    flatpickr(picker, {
                        mode: 'range',
                        minDate: 'today',
                        dateFormat: 'Y-m-d',
                        disable: bookedDates,
                        onChange: function(selectedDates, dateStr, instance) {
                            if (selectedDates.length === 2) {
                                const checkIn = selectedDates[0].toISOString().split('T')[0];
                                const checkOut = selectedDates[1].toISOString().split('T')[0];

                                checkInInput.value = checkIn;
                                checkOutInput.value = checkOut;

                                // Trigger Livewire update
                                checkInInput.dispatchEvent(new Event('input'));
                                checkOutInput.dispatchEvent(new Event('input'));

                                @this.set('check_in', checkIn);
                                @this.set('check_out', checkOut);
                            }
                        }
                    });
                });
            </script>

            <!-- Time Inputs -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="bi bi-clock me-2"></i>Check-in Time
                    </label>
                    <input type="time" wire:model.live="check_in_time" class="form-control" required>
                    @error('check_in_time')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">
                        <i class="bi bi-clock me-2"></i>Check-out Time
                    </label>
                    <input type="time" wire:model.live="check_out_time" class="form-control" required>
                    @error('check_out_time')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Availability Message -->
            @if ($availabilityMessage)
                @if ($isAvailable)
                    <div class="mb-3">
                        <span class="badge bg-success d-flex align-items-center justify-content-center py-2">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            Available for selected dates
                        </span>
                    </div>
                @else
                    <div class="alert alert-danger mb-3">
                        <small>
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <span>{{ $availabilityMessage }}</span>
                        </small>
                    </div>
                @endif
            @endif

            <!-- Price Calculation -->
            @if ($totalNights && $totalPrice && $isAvailable)
                <div class="alert alert-info mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        @if ($type === 'yacht')
                            <span><strong>{{ $totalNights }}</strong>
                                {{ $totalNights === 1 ? 'hour' : 'hours' }}</span>
                        @else
                            <span><strong>{{ $totalNights }}</strong>
                                {{ $totalNights === 1 ? 'night' : 'nights' }}</span>
                        @endif
                        <strong class="text-primary">{{ currency_format($totalPrice) }}</strong>
                    </div>
                </div>
            @endif

            <!-- Adults -->
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-people me-2"></i>Adults</label>
                <input type="number" wire:model.live="adults" class="form-control" min="1"
                    max="{{ $bookable->adults ?? 1 }}" required>
                <small class="text-muted">Max: {{ $bookable->adults ?? 1 }} adults</small>
                @error('adults')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <!-- Children -->
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-person me-2"></i>Children</label>
                <input type="number" wire:model.live="children" class="form-control" min="0"
                    max="{{ $bookable->children ?? 0 }}">
                <small class="text-muted">Max: {{ $bookable->children ?? 0 }} children</small>
                @error('children')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <!-- Guest Names -->
            @if ($adults > 0 || $children > 0)
                <div class="mb-3">
                    <label class="form-label">
                        <i class="bi bi-person-lines-fill me-2"></i>Guest Names
                    </label>

                    <!-- Adult Names -->
                    @if ($adults > 0)
                        <div class="mb-2">
                            <small class="text-muted fw-bold d-block mb-2">
                                <i class="bi bi-people me-1"></i>Adults (Required)
                            </small>
                            @for ($i = 0; $i < $adults; $i++)
                                <div class="mb-2">
                                    <input type="text" wire:model="adultNames.{{ $i }}"
                                        class="form-control form-control-sm"
                                        placeholder="Adult {{ $i + 1 }} Name" required>
                                    @error("adultNames.{$i}")
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endfor
                        </div>
                    @endif

                    <!-- Children Names -->
                    @if ($children > 0)
                        <div class="mb-2">
                            <small class="text-muted fw-bold d-block mb-2">
                                <i class="bi bi-person me-1"></i>Children (Optional)
                            </small>
                            @for ($i = 0; $i < $children; $i++)
                                <div class="mb-2">
                                    <input type="text" wire:model="childrenNames.{{ $i }}"
                                        class="form-control form-control-sm"
                                        placeholder="Child {{ $i + 1 }} Name">
                                    @error("childrenNames.{$i}")
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endfor
                        </div>
                    @endif

                    <small class="text-muted">Please provide names for all guests</small>
                </div>
            @endif

            <button type="submit" class="primary-btn1 w-100"
                {{ !$isAvailable || !$check_in || !$check_out ? 'disabled' : '' }} wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="bookNow">Book Now</span>
                <span wire:loading wire:target="bookNow">
                    <span class="spinner-border spinner-border-sm me-2" role="status"></span>
                    Processing...
                </span>
            </button>
        </form>
    @else
        <button class="btn btn-secondary w-100" disabled>Not Available</button>
    @endif
</div>
