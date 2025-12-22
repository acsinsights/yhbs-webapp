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
    public $adults = 1;
    public $children = 0;
    public array $adultNames = [];
    public array $childrenNames = [];
    public ?int $totalNights = null;
    public ?float $totalPrice = null;
    public bool $isAvailable = true;
    public string $availabilityMessage = '';
    public string $errorMessage = '';
    public string $successMessage = '';

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

        // Get booked dates (only future and current bookings)
        $today = now()->startOfDay()->format('Y-m-d');
        $query = Booking::where('bookingable_type', $modelClass)
            ->where('bookingable_id', $bookable->id)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->where('check_out', '>=', $today); // Only consider bookings that haven't ended yet

        $this->bookedDates = $query
            ->get(['check_in', 'check_out'])
            ->flatMap(function ($booking) use ($today) {
                $dates = [];
                $checkIn = Carbon::parse($booking->check_in)->startOfDay();
                $checkOut = Carbon::parse($booking->check_out)->startOfDay();
                $todayCarbon = Carbon::parse($today);

                // Start from today if check-in is in the past
                if ($checkIn->lt($todayCarbon)) {
                    $checkIn = $todayCarbon->copy();
                }

                // Generate all dates in the booking range
                // IMPORTANT: We exclude the checkout date because guests check out that day
                // So a new guest can check in on the same day someone else checks out
                while ($checkIn->lt($checkOut)) {
                    $dates[] = $checkIn->format('Y-m-d');
                    $checkIn->addDay();
                }
                // Note: We do NOT add the checkout date to booked dates
                // This allows new bookings to start on that date

                return $dates;
            })
            ->unique()
            ->values()
            ->toArray();

        // Initialize adult names array (default 1 adult)
        $this->adultNames = [''];

        // Initialize children names array if needed
        if ($this->children > 0) {
            for ($i = 0; $i < $this->children; $i++) {
                $this->childrenNames[$i] = '';
            }
        }
    }

    public function getMaxAdultsProperty(): int
    {
        if ($this->type === 'yacht') {
            $maxGuests = $this->bookable->max_guests ?? 10;
            return max(1, $maxGuests - $this->children);
        }
        return $this->bookable->adults ?? 10;
    }

    public function getMaxChildrenProperty(): int
    {
        if ($this->type === 'yacht') {
            $maxGuests = $this->bookable->max_guests ?? 10;
            return max(0, $maxGuests - $this->adults);
        }
        return $this->bookable->children ?? 10;
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
        // Handle empty string or null values
        if ($this->adults === '' || $this->adults === null) {
            $this->adults = 1;
            return;
        }

        // Ensure it's an integer
        $this->adults = (int) $this->adults;

        // For yachts, use max guests logic
        if ($this->type === 'yacht') {
            $maxGuests = $this->bookable->max_guests ?? 10;
            $maxAdults = $maxGuests - $this->children;

            if ($this->adults > $maxAdults) {
                $this->adults = $maxAdults;
            }
            if ($this->adults < 1) {
                $this->adults = 1;
            }
        } else {
            // For rooms/houses, use the specific adults field
            $maxAdults = $this->bookable->adults ?? 10;

            if ($this->adults > $maxAdults) {
                $this->adults = $maxAdults;
            }
            if ($this->adults < 1) {
                $this->adults = 1;
            }
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
        // Handle empty string or null values
        if ($this->children === '' || $this->children === null) {
            $this->children = 0;
            return;
        }

        // Ensure it's an integer
        $this->children = (int) $this->children;

        // For yachts, use max guests logic
        if ($this->type === 'yacht') {
            $maxGuests = $this->bookable->max_guests ?? 10;
            $maxChildren = $maxGuests - $this->adults;

            if ($this->children > $maxChildren) {
                $this->children = $maxChildren;
            }
            if ($this->children < 0) {
                $this->children = 0;
            }
        } else {
            // For rooms/houses, use the specific children field
            $maxChildren = $this->bookable->children ?? 10;

            if ($this->children > $maxChildren) {
                $this->children = $maxChildren;
            }
            if ($this->children < 0) {
                $this->children = 0;
            }
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

        // Minimum 2 days booking required (check-out must be at least 1 day after check-in)
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

        $checkIn = Carbon::parse($this->check_in)->startOfDay();
        $checkOut = Carbon::parse($this->check_out)->startOfDay();
        $now = Carbon::now()->startOfDay();

        // Check if check-in date is in the past
        if ($checkIn->lt($now)) {
            $this->isAvailable = false;
            $this->availabilityMessage = 'Check-in date cannot be in the past.';
            return;
        }

        // Check if check-out is before or same as check-in (minimum 2 days required)
        if ($checkOut->lte($checkIn)) {
            $this->isAvailable = false;
            $this->availabilityMessage = 'Minimum 2 days booking required. Check-out must be at least 1 day after check-in.';
            return;
        }

        // Generate range of dates to check
        // For same-day bookings (check-in = check-out), we check only the check-in date
        // For multi-day bookings, we check from check-in up to (but not including) check-out
        $selectedDates = [];

        if ($checkIn->equalTo($checkOut)) {
            // Same-day booking - only check the single date
            $selectedDates[] = $checkIn->format('Y-m-d');
        } else {
            // Multi-day booking - check from check-in to (but not including) check-out
            $current = $checkIn->copy();
            while ($current->lt($checkOut)) {
                $selectedDates[] = $current->format('Y-m-d');
                $current->addDay();
            }
        }

        // Check if any selected date is booked
        $conflictDates = array_intersect($selectedDates, $this->bookedDates);
        $hasConflict = !empty($conflictDates);

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

        $checkInDate = Carbon::parse($this->check_in)->startOfDay();
        $checkOutDate = Carbon::parse($this->check_out)->startOfDay();

        // Calculate the difference in days
        $nights = $checkInDate->diffInDays($checkOutDate);

        // Log for debugging
        \Log::info('Night Calculation Debug', [
            'check_in_raw' => $this->check_in,
            'check_out_raw' => $this->check_out,
            'check_in_parsed' => $checkInDate->format('Y-m-d'),
            'check_out_parsed' => $checkOutDate->format('Y-m-d'),
            'nights_calculated' => $nights,
        ]);

        // Allow same-day bookings (0 nights = 1 day booking)
        // Note: diffInDays returns float, so use == instead of ===
        if ($nights == 0) {
            $nights = 1;
        }

        $this->totalNights = $nights;

        // For yachts, use per hour pricing (assuming full day = 24 hours)
        if ($this->type === 'yacht') {
            $hours = $nights * 24;
            $this->totalPrice = $hours * ($this->bookable->price_per_hour ?? ($this->bookable->price_per_night ?? 0));
        } else {
            // For rooms and houses: calculate based on night-specific pricing structure
            // Note: $nights is float, so use == instead of ===
            if ($nights == 1) {
                // 1 night - use price_per_night
                $this->totalPrice = $this->bookable->price_per_night ?? 0;
                \Log::info('Price Calculation - 1 Night', [
                    'type' => $this->type,
                    'property_id' => $this->bookable->id,
                    'price_per_night' => $this->bookable->price_per_night,
                    'total_price' => $this->totalPrice,
                ]);
            } elseif ($nights == 2) {
                // 2 nights - use price_per_2night if available, otherwise 2x price_per_night
                if ($this->bookable->price_per_2night) {
                    $this->totalPrice = $this->bookable->price_per_2night;
                } else {
                    $this->totalPrice = ($this->bookable->price_per_night ?? 0) * 2;
                }
            } elseif ($nights == 3) {
                // 3 nights - use price_per_3night if available, otherwise 3x price_per_night
                if ($this->bookable->price_per_3night) {
                    $this->totalPrice = $this->bookable->price_per_3night;
                } else {
                    $this->totalPrice = ($this->bookable->price_per_night ?? 0) * 3;
                }
            } else {
                // 4+ nights - use price_per_3night + (additional nights x additional_night_price)
                if ($this->bookable->price_per_3night) {
                    $basePrice = $this->bookable->price_per_3night;
                } else {
                    $basePrice = ($this->bookable->price_per_night ?? 0) * 3;
                }
                $additionalNights = $nights - 3;
                $additionalPrice = $additionalNights * ($this->bookable->additional_night_price ?? ($this->bookable->price_per_night ?? 0));
                $this->totalPrice = $basePrice + $additionalPrice;
            }
        }
    }

    public function bookNow()
    {
        try {
            // Reset messages
            $this->errorMessage = '';
            $this->successMessage = '';

            // Check if dates are selected
            if (!$this->check_in || !$this->check_out) {
                $this->errorMessage = 'Please select check-in and check-out dates.';
                return;
            }

            // Check availability
            if (!$this->isAvailable) {
                $this->errorMessage = 'Selected dates are not available. Please choose different dates.';
                return;
            }

            // Get max values based on type
            if ($this->type === 'yacht') {
                $maxGuests = $this->bookable->max_guests ?? 10;
                $maxAdults = $maxGuests;
                $maxChildren = $maxGuests;
            } else {
                $maxAdults = $this->bookable->adults ?? 10;
                $maxChildren = $this->bookable->children ?? 10;
            }

            // Build validation rules dynamically
            $rules = [
                'check_in' => 'required|date',
                'check_out' => 'required|date',
                'adults' => "required|integer|min:1|max:$maxAdults",
                'children' => "nullable|integer|min:0|max:$maxChildren",
            ];

            // Add validation for adult names only if adults > 0
            if ($this->adults > 0) {
                for ($i = 0; $i < $this->adults; $i++) {
                    $rules["adultNames.{$i}"] = 'required|string|min:1|max:255';
                }
            }

            // Add validation for children names only if children > 0
            if ($this->children > 0) {
                for ($i = 0; $i < $this->children; $i++) {
                    $rules["childrenNames.{$i}"] = 'nullable|string|max:255';
                }
            }

            // Validate
            $validated = $this->validate($rules, [
                'check_in.required' => 'Check-in date is required.',
                'check_out.required' => 'Check-out date is required.',
                'adultNames.*.required' => 'Please provide name for all adults.',
                'adultNames.*.min' => 'Adult name is required.',
            ]);

            // For yachts, validate that adults + children <= max guests
            if ($this->type === 'yacht') {
                $maxGuests = $this->bookable->max_guests ?? 10;
                if ($this->adults + $this->children > $maxGuests) {
                    $this->errorMessage = "Total guests cannot exceed $maxGuests.";
                    return;
                }
            }

            // Ensure adult names are filled
            $filledAdultNames = array_filter($this->adultNames, fn($name) => !empty(trim($name)));
            if (count($filledAdultNames) < $this->adults) {
                $this->errorMessage = 'Please provide names for all adults.';
                return;
            }

            // Filter out empty names
            $adultNames = array_values(array_filter($this->adultNames, fn($name) => !empty(trim($name))));
            $childrenNames = array_values(array_filter($this->childrenNames, fn($name) => !empty(trim($name))));

            // Redirect to checkout with booking details
            $queryParams = [
                'type' => $this->type,
                'id' => $this->bookable->id,
                'check_in' => $this->check_in,
                'check_out' => $this->check_out,
                'adults' => $this->adults,
                'children' => $this->children,
                'adult_names' => $adultNames,
                'children_names' => $childrenNames,
            ];

            return redirect()->route('checkout', $queryParams);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validation errors are automatically handled by Livewire
            throw $e;
        } catch (\Exception $e) {
            $this->errorMessage = 'An error occurred while processing your booking. Please try again.';
            \Log::error('Booking error: ' . $e->getMessage());
        }
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
        <!-- Error Message -->
        @if ($errorMessage)
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ $errorMessage }}
                <button type="button" class="btn-close" wire:click="$set('errorMessage', '')"></button>
            </div>
        @endif

        <!-- Success Message -->
        @if ($successMessage)
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                {{ $successMessage }}
                <button type="button" class="btn-close" wire:click="$set('successMessage', '')"></button>
            </div>
        @endif

        <!-- Validation Errors -->
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

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
                @if ($check_in && $check_out)
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Check-in: {{ \Carbon\Carbon::parse($check_in)->format('M d, Y') }} |
                        Check-out: {{ \Carbon\Carbon::parse($check_out)->format('M d, Y') }}
                    </small>
                @endif
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
                                const checkInTime = selectedDates[0].getTime();
                                const checkOutTime = selectedDates[1].getTime();

                                // Prevent same date selection - require minimum 1 day difference
                                if (checkInTime === checkOutTime) {
                                    instance.clear();
                                    alert(
                                        'Please select different dates for check-in and check-out. Minimum 2 days booking required.');
                                    return;
                                }

                                // Use local date string to avoid timezone issues
                                const checkIn = selectedDates[0].getFullYear() + '-' +
                                    String(selectedDates[0].getMonth() + 1).padStart(2, '0') + '-' +
                                    String(selectedDates[0].getDate()).padStart(2, '0');
                                const checkOut = selectedDates[1].getFullYear() + '-' +
                                    String(selectedDates[1].getMonth() + 1).padStart(2, '0') + '-' +
                                    String(selectedDates[1].getDate()).padStart(2, '0');

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
                            <span>
                                <strong>{{ $totalNights }}</strong>
                                {{ $totalNights === 1 ? 'hour' : 'hours' }}
                            </span>
                        @else
                            <span>
                                <strong>{{ $totalNights }}</strong>
                                {{ $totalNights === 1 ? 'night' : 'nights' }}
                                <small class="text-muted d-block">
                                    ({{ $totalNights + 1 }} {{ $totalNights + 1 === 1 ? 'day' : 'days' }})
                                </small>
                            </span>
                        @endif
                        <strong class="text-primary">{{ currency_format($totalPrice) }}</strong>
                    </div>
                </div>
            @endif

            <!-- Adults -->
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-people me-2"></i>Adults</label>
                <input type="number" wire:model.live="adults" class="form-control" min="1"
                    max="{{ $this->maxAdults }}" required>
                <small class="text-muted">Max: {{ $this->maxAdults }} adults</small>
                @error('adults')
                    <span class="text-danger small">{{ $message }}</span>
                @enderror
            </div>

            <!-- Children -->
            <div class="mb-3">
                <label class="form-label"><i class="bi bi-person me-2"></i>Children</label>
                <input type="number" wire:model.live="children" class="form-control" min="0"
                    max="{{ $this->maxChildren }}">
                <small class="text-muted">Max: {{ $this->maxChildren }} children</small>
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

            <!-- Total Amount Display -->
            @if ($totalPrice && $isAvailable)
                <div class="alert alert-success mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-bold"><i class="bi bi-cash-coin me-2"></i>Total Amount:</span>
                        <span class="fs-4 fw-bold text-success">{{ currency_format($totalPrice) }}</span>
                    </div>
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
