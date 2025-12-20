<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Hash;
use App\Models\{Booking, Room, User, House};
use App\Enums\{BookingStatusEnum, RolesEnum};
use App\Notifications\WelcomeCustomerNotification;

new class extends Component {
    use Toast, WithPagination;

    public ?int $user_id = null;
    public string $customer_name = '';
    public string $customer_email = '';
    public bool $createCustomerModal = false;

    public ?int $house_id = null;
    public ?string $check_in = null;
    public ?string $check_out = null;
    public int $adults = 1;
    public int $children = 0;
    public array $adultNames = [];
    public array $childrenNames = [];
    public ?float $amount = null;
    public bool $amountManuallySet = false;
    public string $payment_method = 'cash';
    public string $payment_status = 'pending';
    public ?string $notes = null;
    public string $house_search = '';
    public int $perPage = 6;

    // Price breakdown properties
    public ?int $totalNights = null;
    public ?float $calculatedAmount = null;
    public ?float $baseCharges = null;
    public ?int $additionalNights = null;
    public ?float $additionalCharges = null;
    public ?float $discount = null;
    public ?float $raisedAmount = null;

    public function mount(): void
    {
        // Set default dates
        $this->check_in = Carbon::today()->format('Y-m-d\TH:i');
        $this->check_out = Carbon::tomorrow()->format('Y-m-d\TH:i');
    }

    public function updatedCheckIn(): void
    {
        $this->resetPage();
        $this->house_id = null;
        if (!$this->amountManuallySet) {
            $this->amount = null;
        }

        // Validate that check_in is not in the past
        if ($this->check_in) {
            $checkIn = Carbon::parse($this->check_in);
            $now = Carbon::now();

            if ($checkIn->lt($now)) {
                $this->error('Check-in date and time must be equal to or after the current date and time.');
                $this->check_in = $now->format('Y-m-d\TH:i');
                return;
            }
        }

        // Ensure check_out is after check_in
        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);

            if ($checkOut->lte($checkIn)) {
                $this->check_out = $checkIn->copy()->addHour()->format('Y-m-d\TH:i');
            }
        }

        // Recalculate price if house is selected
        if ($this->house_id && !$this->amountManuallySet) {
            $this->calculatePrice();
        }
    }

    public function updatedCheckOut(): void
    {
        $this->resetPage();
        $this->house_id = null;
        if (!$this->amountManuallySet) {
            $this->amount = null;
        }

        // Validate that check_out is after check_in
        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);

            if ($checkOut->lte($checkIn)) {
                $this->error('Check-out date and time must be after check-in date and time.');
                $this->check_out = $checkIn->copy()->addHour()->format('Y-m-d\TH:i');
            }
        }

        // Recalculate price if house is selected
        if ($this->house_id && !$this->amountManuallySet) {
            $this->calculatePrice();
        }
    }

    public function updatedHouseSearch(): void
    {
        $this->resetPage();
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

    public function updatedHouseId(): void
    {
        // When house changes, calculate price based on nights
        $this->amountManuallySet = false;

        if ($this->house_id) {
            $this->calculatePrice();
        } else {
            // Reset amount when house selection is cleared
            $this->amount = null;
            $this->calculatedAmount = null;
            $this->baseCharges = null;
            $this->additionalNights = null;
            $this->additionalCharges = null;
            $this->discount = null;
            $this->raisedAmount = null;
            $this->totalNights = null;
            $this->dispatch('amount-updated');
        }
    }

    public function calculatePrice(): void
    {
        if (!$this->house_id || !$this->check_in || !$this->check_out) {
            return;
        }

        $house = House::find($this->house_id);
        if (!$house) {
            return;
        }

        $checkIn = Carbon::parse($this->check_in);
        $checkOut = Carbon::parse($this->check_out);
        $nights = $checkIn->diffInDays($checkOut);

        if ($nights <= 0) {
            return;
        }

        $this->totalNights = $nights;
        $this->baseCharges = 0;
        $this->additionalNights = 0;
        $this->additionalCharges = 0;

        // Calculate base charges based on nights
        if ($nights == 1 && $house->price_per_night) {
            $this->baseCharges = $house->price_per_night;
        } elseif ($nights == 2 && $house->price_per_2night) {
            $this->baseCharges = $house->price_per_2night;
        } elseif ($nights == 3 && $house->price_per_3night) {
            $this->baseCharges = $house->price_per_3night;
        } elseif ($nights > 3 && $house->price_per_3night) {
            // Use 3-night price + additional nights
            $this->baseCharges = $house->price_per_3night;
            $this->additionalNights = $nights - 3;
            $this->additionalCharges = $this->additionalNights * ($house->additional_night_price ?? 0);
        } else {
            // Fallback: calculate based on price_per_night
            $this->baseCharges = $nights * ($house->price_per_night ?? 0);
        }

        // Calculate total
        $this->calculatedAmount = $this->baseCharges + $this->additionalCharges;
        $this->amount = $this->calculatedAmount;
        $this->dispatch('amount-updated');
    }

    public function updatedAmount(): void
    {
        // Validate and limit amount
        if ($this->amount !== null && $this->amount !== '') {
            // Limit to maximum 999,999,999.99 (999 million)
            $maxAmount = 999999999.99;
            if ($this->amount > $maxAmount) {
                $this->error('Amount cannot exceed ' . currency_format($maxAmount) . '.');
                $this->amount = $maxAmount;
                return;
            }
            // Ensure amount is not negative
            if ($this->amount < 0) {
                $this->amount = 0;
            }
            $this->amountManuallySet = true;

            // Calculate discount or raised amount if we have a calculated amount
            if ($this->calculatedAmount !== null && $this->amount != $this->calculatedAmount) {
                if ($this->amount < $this->calculatedAmount) {
                    $this->discount = $this->calculatedAmount - $this->amount;
                    $this->raisedAmount = null;
                } else {
                    $this->raisedAmount = $this->amount - $this->calculatedAmount;
                    $this->discount = null;
                }
            } else {
                $this->discount = null;
                $this->raisedAmount = null;
            }
        }
    }

    public function resetForm(): void
    {
        $this->user_id = null;
        $this->house_id = null;
        $this->check_in = Carbon::now()->format('Y-m-d\TH:i');
        $this->check_out = Carbon::tomorrow()->format('Y-m-d\TH:i');
        $this->adults = 1;
        $this->children = 0;
        $this->amount = null;
        $this->amountManuallySet = false;
        $this->payment_method = 'cash';
        $this->payment_status = 'pending';
        $this->notes = null;
        $this->house_search = '';
        $this->resetPage();
        $this->success('Form has been reset.');
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

    public function store(): void
    {
        $this->validate(
            [
                'user_id' => 'required|exists:users,id',
                'house_id' => 'required|exists:houses,id',
                'check_in' => 'required|date|after_or_equal:today',
                'check_out' => 'required|date|after:check_in',
                'adults' => 'required|integer|min:1',
                'children' => 'required|integer|min:0',
                'amount' => 'required|numeric|min:0|max:999999999.99',
                'payment_method' => 'required|in:cash,card',
                'payment_status' => 'required|in:paid,pending',
            ],
            [
                'amount.max' => 'Amount cannot exceed ' . currency_format(999999999.99) . '.',
                'amount.min' => 'Amount must be greater than or equal to 0.',
            ],
        );

        // Check if house is available for the selected dates
        $checkIn = Carbon::parse($this->check_in);
        $checkOut = Carbon::parse($this->check_out);

        $availableHouses = House::available($checkIn, $checkOut)->where('id', $this->house_id)->exists();

        if (!$availableHouses) {
            $this->error('Selected house is not available for the chosen dates. One or more rooms are already booked.');
            return;
        }

        $house = House::with('rooms')->findOrFail($this->house_id);

        if ($house->rooms->count() === 0) {
            $this->error('This house has no rooms available for booking.');
            return;
        }

        // Create a single booking for the entire house
        $guestDetails = [
            'adults' => array_values(array_filter($this->adultNames)),
            'children' => array_values(array_filter($this->childrenNames)),
        ];

        $booking = Booking::create([
            'bookingable_type' => House::class,
            'bookingable_id' => $this->house_id,
            'user_id' => $this->user_id,
            'adults' => $this->adults,
            'children' => $this->children,
            'guest_details' => $guestDetails,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'price' => $this->amount,
            'total_amount' => $this->amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'status' => BookingStatusEnum::BOOKED->value,
            'notes' => $this->notes,
        ]);

        $this->success('House booking created successfully with ' . $house->rooms->count() . ' room(s).', redirectTo: route('admin.bookings.house.index'));
    }

    public function rendering(View $view)
    {
        $checkIn = $this->check_in ? Carbon::parse($this->check_in) : null;
        $checkOut = $this->check_out ? Carbon::parse($this->check_out) : null;

        if ($checkIn && $checkOut && $checkIn->lt($checkOut)) {
            $query = House::active()->available($checkIn, $checkOut)->with('rooms');

            // Filter by search term
            if (!empty($this->house_search)) {
                $search = $this->house_search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")->orWhere('house_number', 'like', "%{$search}%");
                });
            }

            $view->availableHouses = $query->orderBy('name')->paginate($this->perPage);
        } else {
            $view->availableHouses = \Illuminate\Pagination\LengthAwarePaginator::empty();
        }

        $view->customers = User::role(RolesEnum::CUSTOMER->value)->orderBy('name')->get();

        // Pass parsed dates to view
        $view->checkInDate = $checkIn;
        $view->checkOutDate = $checkOut;

        // Set minimum date for check-in (current date/time)
        $view->minCheckInDate = Carbon::now()->format('Y-m-d\TH:i');

        // Pass breakdown data to view
        $view->totalNights = $this->totalNights;
        $view->baseCharges = $this->baseCharges;
        $view->additionalNights = $this->additionalNights;
        $view->additionalCharges = $this->additionalCharges;
        $view->calculatedAmount = $this->calculatedAmount;
        $view->discount = $this->discount;
        $view->raisedAmount = $this->raisedAmount;
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
                'link' => route('admin.bookings.house.index'),
                'label' => 'House Bookings',
            ],
            [
                'label' => 'Create Booking',
            ],
        ];
    @endphp

    <x-header title="Create House Booking" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Book an entire house with all its rooms</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.house.index') }}"
                class="btn-ghost btn-outline" />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="mx-auto">
        <x-form wire:submit="store">
            <div class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-3 lg:items-start">
                    <div class="space-y-6 lg:col-span-2">
                        {{-- Date Range Section --}}
                        <x-booking.date-range-section stepNumber="1" :minCheckInDate="$minCheckInDate" />

                        {{-- Customer Section --}}
                        <x-booking.customer-section stepNumber="2" :customers="$customers" />

                        @php
                            $selectedHouse =
                                $availableHouses->firstWhere('id', $house_id) ??
                                ($house_id ? House::with('rooms')->find($house_id) : null);
                            $maxAdults = $selectedHouse?->adults ?? 10;
                            $maxChildren = $selectedHouse?->children ?? 10;
                        @endphp

                        {{-- Guest Details Section --}}
                        <x-booking.guest-section stepNumber="3" :maxAdults="$maxAdults" :maxChildren="$maxChildren" :adults="$adults"
                            :children="$children" />

                        {{-- House Selection Section --}}
                        <x-card class="bg-base-200">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step 4</p>
                                    <h3 class="text-xl font-semibold text-base-content mt-1">House Selection</h3>
                                    <p class="text-sm text-base-content/60 mt-1">Choose from available houses for your
                                        booking
                                    </p>
                                </div>
                                <x-icon name="o-home-modern" class="w-8 h-8 text-primary/70" />
                            </div>

                            @if ($check_in && $check_out && Carbon::parse($check_in)->lt(Carbon::parse($check_out)))
                                {{-- Search Input --}}
                                <div class="mt-4">
                                    <x-input wire:model.live.debounce.300ms="house_search" label="Search Houses"
                                        placeholder="Search by house name or number..." icon="o-magnifying-glass"
                                        clearable hint="Filter houses by name or house number" />
                                </div>

                                {{-- Filter Info --}}
                                <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-base-content/70">
                                    {{-- Loading Indicator --}}
                                    <div wire:loading wire:target="check_in,check_out,house_search,perPage"
                                        class="flex items-center gap-2 text-primary">
                                        <x-loading class="loading-dots" />
                                        <span>Loading houses...</span>
                                    </div>

                                    <div wire:loading.remove wire:target="check_in,check_out,house_search,perPage"
                                        class="flex items-center gap-2">
                                        <x-icon name="o-funnel" class="w-4 h-4" />
                                        <span>
                                            <strong>{{ $availableHouses->total() }}</strong>
                                            {{ $availableHouses->total() === 1 ? 'house' : 'houses' }} available
                                            @if ($availableHouses->total() > $availableHouses->count())
                                                (Showing
                                                {{ $availableHouses->firstItem() }}-{{ $availableHouses->lastItem() }}
                                                of
                                                {{ $availableHouses->total() }})
                                            @endif
                                        </span>
                                    </div>
                                    @if (!empty($house_search))
                                        <div wire:loading.remove wire:target="check_in,check_out,house_search,perPage"
                                            class="flex items-center gap-2">
                                            <x-icon name="o-magnifying-glass" class="w-4 h-4" />
                                            <span>Search: "{{ $house_search }}"</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Loading Overlay for House Grid --}}
                                <div wire:loading wire:target="check_in,check_out,house_search,perPage" class="mt-4">
                                    <div
                                        class="flex items-center justify-center py-12 bg-base-200/50 rounded-xl border-2 border-dashed border-base-300">
                                        <div class="text-center">
                                            <x-loading class="loading-dots" />
                                            <p class="mt-4 text-sm text-base-content/70">Filtering available houses...
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div wire:loading.remove wire:target="check_in,check_out,house_search,perPage">
                                    @if ($availableHouses->count() > 0)
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                                            @foreach ($availableHouses as $house)
                                                @php
                                                    $isSelected = $house_id == $house->id;
                                                    $totalRooms = $house->rooms->count();
                                                    // Use house's price_per_night instead of summing room prices
                                                    $totalPrice = $house->price_per_night ?? 0;
                                                @endphp
                                                <label wire:click="$wire.house_id = {{ $house->id }}"
                                                    class="relative cursor-pointer group block">
                                                    <input type="radio" wire:model.live="house_id"
                                                        value="{{ $house->id }}" class="sr-only">
                                                    <div
                                                        class="bg-base-100 border-2 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 h-full flex flex-col {{ $isSelected ? 'border-primary ring-2 ring-primary/20 shadow-lg' : 'border-base-300' }}">
                                                        {{-- Header Section --}}
                                                        <div class="p-4 bg-base-200/50 border-b border-base-300">
                                                            <div class="flex items-start justify-between gap-2">
                                                                <div class="flex-1 min-w-0">
                                                                    <h4
                                                                        class="font-bold text-lg text-base-content mb-1 line-clamp-1">
                                                                        {{ $house->name }}
                                                                    </h4>
                                                                    @if ($house->house_number)
                                                                        <p
                                                                            class="text-xs text-base-content/60 line-clamp-1">
                                                                            House #{{ $house->house_number }}</p>
                                                                    @endif
                                                                </div>
                                                                {{-- Selection Indicator --}}
                                                                <div
                                                                    class="w-6 h-6 rounded-full border-2 border-base-100 bg-base-100/80 backdrop-blur-sm flex items-center justify-center transition-all shrink-0 {{ $isSelected ? 'bg-primary border-primary' : '' }}">
                                                                    @if ($isSelected)
                                                                        <svg class="w-4 h-4 text-primary-content"
                                                                            fill="currentColor" viewBox="0 0 20 20">
                                                                            <path fill-rule="evenodd"
                                                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                                                clip-rule="evenodd" />
                                                                        </svg>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Content Section --}}
                                                        <div class="p-4 flex-1 flex flex-col">
                                                            {{-- Details --}}
                                                            <div class="space-y-2 mb-4 flex-1">
                                                                <div
                                                                    class="flex items-center gap-2 text-sm text-base-content/70">
                                                                    <x-icon name="o-home"
                                                                        class="w-4 h-4 text-base-content/50" />
                                                                    <span><strong>{{ $totalRooms }}</strong>
                                                                        {{ $totalRooms === 1 ? 'room' : 'rooms' }}
                                                                        included</span>
                                                                </div>
                                                                @if ($house->description)
                                                                    <p
                                                                        class="text-xs text-base-content/60 line-clamp-2">
                                                                        {{ $house->description }}
                                                                    </p>
                                                                @endif
                                                            </div>

                                                            {{-- Price Section --}}
                                                            <div class="pt-3 border-t border-base-300">
                                                                <div class="flex items-baseline justify-between gap-2">
                                                                    <div class="flex-1">
                                                                        <div class="text-xs text-base-content/60 mb-1">
                                                                            Total Price</div>
                                                                        <div class="font-bold text-lg text-primary">
                                                                            {{ currency_format($totalPrice) }}
                                                                        </div>
                                                                        <div class="text-xs text-base-content/50 mt-1">
                                                                            All {{ $totalRooms }}
                                                                            {{ $totalRooms === 1 ? 'room' : 'rooms' }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>

                                        {{-- Pagination --}}
                                        @if ($availableHouses->hasPages())
                                            <div
                                                class="mt-6 flex items-center justify-between border-t border-base-300 pt-4">
                                                <div class="text-sm text-base-content/70">
                                                    Showing {{ $availableHouses->firstItem() }} to
                                                    {{ $availableHouses->lastItem() }} of
                                                    {{ $availableHouses->total() }} results
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    {{ $availableHouses->links() }}
                                                </div>
                                            </div>
                                        @endif

                                        @if ($house_id)
                                            <div class="mt-4 p-3 bg-primary/10 border border-primary/20 rounded-lg">
                                                <div class="flex items-center gap-2 text-sm text-primary">
                                                    <x-icon name="o-check-circle" class="w-5 h-5" />
                                                    <span class="font-medium">House selected:
                                                        {{ $availableHouses->firstWhere('id', $house_id)?->name ?? House::find($house_id)?->name }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <x-alert icon="o-exclamation-triangle" class="alert-warning mt-4">
                                            <div>
                                                <p class="font-semibold">No houses available</p>
                                                <p class="text-sm mt-1">
                                                    @if (!empty($house_search))
                                                        No houses match your search criteria or have rooms that are
                                                        already booked during the selected date range.
                                                    @else
                                                        No houses with fully available rooms for the selected dates. All
                                                        houses have at least one room already booked during this period.
                                                    @endif
                                                </p>
                                            </div>
                                        </x-alert>
                                    @endif
                                </div>
                            @else
                                <x-alert icon="o-information-circle" class="alert-info mt-4">
                                    <div>
                                        <p class="font-semibold">Select dates first</p>
                                        <p class="text-sm mt-1">Please select check-in and check-out dates to see
                                            available houses.</p>
                                    </div>
                                </x-alert>
                            @endif
                        </x-card>

                        {{-- Payment Section --}}
                        <x-booking.payment-section stepNumber="5" />

                        {{-- Notes Section --}}
                        <x-booking.notes-section stepNumber="6" />
                    </div>

                    {{-- Summary Column --}}
                    <div class="sticky top-24">
                        <x-booking.booking-summary :adults="$adults" :children="$children" :checkInDate="$checkInDate"
                            :checkOutDate="$checkOutDate" :amount="$amount" :paymentMethod="$payment_method" :paymentStatus="$payment_status"
                            :showChecklist="true" :customerSelected="!!$user_id" :selectionSelected="!!$house_id" :selectionLabel="'House'"
                            :amountFilled="!!$amount" :paymentMethodSelected="!!$payment_method" :paymentStatusSelected="!!$payment_status" :showInfoMessage="true"
                            :infoTitle="'Booking Entire House'" :infoMessage="'When you book a house, all rooms in that house will be reserved for your selected dates.'">
                            <x-slot:selection>
                                {{-- Selected House --}}
                                <div class="bg-base-100/80 rounded-lg p-2.5 border border-base-300/50">
                                    <div class="flex items-start gap-2">
                                        <div
                                            class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center shrink-0">
                                            <x-icon name="o-home-modern" class="w-4 h-4 text-primary" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-base-content/60 mb-0.5">Selected House
                                            </p>
                                            @if ($selectedHouse)
                                                <p class="text-xs font-bold text-base-content line-clamp-1">
                                                    {{ $selectedHouse->name }}</p>
                                                <p class="text-xs text-base-content/60">
                                                    {{ $selectedHouse->rooms->count() }}
                                                    {{ $selectedHouse->rooms->count() === 1 ? 'room' : 'rooms' }}
                                                    included
                                                </p>
                                            @else
                                                <p class="text-xs text-base-content/50 italic">No house selected</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </x-slot:selection>

                            {{-- Price Breakdown --}}
                            <x-slot:extraSections>
                                @if ($totalNights && $baseCharges !== null)
                                    <x-card class="p-4 bg-base-100 mb-4">
                                        <p class="text-xs uppercase tracking-wide text-base-content/60 mb-3">Price
                                            Breakdown</p>
                                        <div class="space-y-2 text-sm">
                                            {{-- Base Charges --}}
                                            <div class="flex justify-between items-center">
                                                <span class="text-base-content/70">
                                                    @if ($totalNights == 1)
                                                        1 Night Charge:
                                                    @elseif ($totalNights == 2)
                                                        2 Nights Charge:
                                                    @elseif ($totalNights == 3)
                                                        3 Nights Charge:
                                                    @else
                                                        3 Nights Charge:
                                                    @endif
                                                </span>
                                                <span
                                                    class="font-semibold text-base-content">{{ currency_format($baseCharges) }}</span>
                                            </div>

                                            {{-- Additional Charges --}}
                                            @if ($additionalNights > 0 && $additionalCharges > 0)
                                                <div class="flex justify-between items-center">
                                                    <span class="text-base-content/70">Additional Charges:
                                                        {{ $additionalNights }} x
                                                        {{ currency_format($additionalCharges / $additionalNights) }}</span>
                                                    <span
                                                        class="font-semibold text-base-content">{{ currency_format($additionalCharges) }}</span>
                                                </div>
                                            @endif

                                            {{-- Subtotal --}}
                                            @if ($calculatedAmount)
                                                <div
                                                    class="flex justify-between items-center pt-2 border-t border-base-300">
                                                    <span class="text-base-content/70">Calculated Total:</span>
                                                    <span
                                                        class="font-semibold text-base-content">{{ currency_format($calculatedAmount) }}</span>
                                                </div>
                                            @endif

                                            {{-- Discount --}}
                                            @if ($discount && $discount > 0)
                                                <div class="flex justify-between items-center text-success">
                                                    <span>Discount:</span>
                                                    <span class="font-semibold">-
                                                        {{ currency_format($discount) }}</span>
                                                </div>
                                            @endif

                                            {{-- Raised Amount --}}
                                            @if ($raisedAmount && $raisedAmount > 0)
                                                <div class="flex justify-between items-center text-warning">
                                                    <span>Raised by:</span>
                                                    <span class="font-semibold">+
                                                        {{ currency_format($raisedAmount) }}</span>
                                                </div>
                                            @endif

                                            {{-- Final Total --}}
                                            @if ($amount)
                                                <div
                                                    class="flex justify-between items-center pt-2 border-t border-base-300">
                                                    <span class="font-bold text-base-content">Final Total:</span>
                                                    <span
                                                        class="font-bold text-lg text-primary">{{ currency_format($amount) }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </x-card>
                                @endif
                            </x-slot:extraSections>
                        </x-booking.booking-summary>
                    </div>
                </div>
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:justify-end">
                    <x-button icon="o-arrow-path" label="Reset Form" type="button" wire:click="resetForm"
                        class="btn-outline w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Create Booking" type="submit"
                        class="btn-primary w-full sm:w-auto" spinner="store" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-card>

    {{-- Create Customer Modal --}}
    <x-booking.create-customer-modal />
</div>
