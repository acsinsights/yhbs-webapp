<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use Illuminate\View\View;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use App\Enums\RolesEnum;
use App\Models\{Booking, Room, User, House, Yacht};

new class extends Component {
    use Toast, WithPagination;

    public Booking $booking;

    public ?int $house_id = null;
    public ?string $check_in = null;
    public ?string $check_out = null;
    public int $adults = 1;
    public int $children = 0;
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

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['bookingable', 'user']);

        // Redirect if booking is not for a House
        if ($booking->bookingable_type === Room::class) {
            $this->warning('This is a room booking. Redirecting to room booking edit page.', redirectTo: route('admin.bookings.room.edit', $booking->id));
            return;
        }

        if ($booking->bookingable_type === Yacht::class) {
            $this->warning('This is a yacht booking. Redirecting to yacht booking edit page.', redirectTo: route('admin.bookings.yacht.edit', $booking->id));
            return;
        }

        if ($booking->bookingable_type !== House::class) {
            $this->error('Invalid booking type.', redirectTo: route('admin.bookings.house.index'));
            return;
        }

        // Check if booking can be edited
        if (!$booking->canBeEdited()) {
            $this->warning('This booking cannot be edited.', redirectTo: route('admin.bookings.house.index'));
            return;
        }

        // Pre-fill form with existing booking data
        $this->house_id = $booking->bookingable_id;
        $this->check_in = $booking->check_in ? $booking->check_in->format('Y-m-d\TH:i') : null;
        $this->check_out = $booking->check_out ? $booking->check_out->format('Y-m-d\TH:i') : null;
        $this->adults = $booking->adults ?? 1;
        $this->children = $booking->children ?? 0;
        $this->amount = $booking->price;
        $this->amountManuallySet = true; // Set to true since we're loading existing amount
        $this->payment_method = $booking->payment_method->value ?? 'cash';
        $this->payment_status = $booking->payment_status->value ?? 'pending';
        $this->notes = $booking->notes;

        // Calculate initial price breakdown
        $this->calculatePrice();
    }

    public function updatedCheckIn(): void
    {
        $this->resetPage();

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

        // Recalculate price breakdown
        if ($this->house_id) {
            $this->calculatePrice();
        }
    }

    public function updatedCheckOut(): void
    {
        $this->resetPage();

        // Validate that check_out is after check_in
        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);

            if ($checkOut->lte($checkIn)) {
                $this->error('Check-out date and time must be after check-in date and time.');
                $this->check_out = $checkIn->copy()->addHour()->format('Y-m-d\TH:i');
            }
        }

        // Recalculate price breakdown
        if ($this->house_id) {
            $this->calculatePrice();
        }
    }

    public function updatedHouseSearch(): void
    {
        $this->resetPage();
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

        // If amount wasn't manually set, use calculated amount
        if (!$this->amountManuallySet) {
            $this->amount = $this->calculatedAmount;
        } else {
            // Recalculate discount/raised amount with new calculated amount
            if ($this->amount != $this->calculatedAmount) {
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

        $this->dispatch('amount-updated');
    }

    public function updatedAmount(): void
    {
        // Validate and limit amount
        if ($this->amount !== null && $this->amount !== '') {
            $maxAmount = 999999999.99;
            if ($this->amount > $maxAmount) {
                $this->error('Amount cannot exceed ' . currency_format($maxAmount) . '.');
                $this->amount = $maxAmount;
                return;
            }
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
            $this->amountManuallySet = true;
        }
    }

    public function update(): void
    {
        $this->validate(
            [
                'house_id' => 'required|exists:houses,id',
                'check_in' => 'required|date',
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

        $checkIn = Carbon::parse($this->check_in);
        $checkOut = Carbon::parse($this->check_out);

        $house = House::find($this->house_id);

        if (!$house) {
            $this->error('Selected house not found.');
            return;
        }

        // Check if house is available for the date range (excluding current booking)
        // If it's the same house, we allow the update
        if ($this->house_id != $this->booking->bookingable_id) {
            // Check if the new house is available (excluding current booking)
            $hasConflict = Booking::where('bookingable_type', House::class)
                ->where('bookingable_id', $this->house_id)
                ->where('id', '!=', $this->booking->id)
                ->whereIn('status', ['pending', 'booked', 'checked_in'])
                ->where(function ($q) use ($checkIn, $checkOut) {
                    $q->whereBetween('check_in', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out', [$checkIn, $checkOut])
                        ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                            $q2->where('check_in', '<=', $checkIn)->where('check_out', '>=', $checkOut);
                        });
                })
                ->exists();

            if ($hasConflict) {
                $this->error('Selected house is not available for the chosen dates.');
                return;
            }
        }

        $this->booking->update([
            'bookingable_id' => $this->house_id,
            'adults' => $this->adults,
            'children' => $this->children,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'price' => $this->amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'notes' => $this->notes,
        ]);

        $this->success('Booking updated successfully.', redirectTo: route('admin.bookings.house.show', $this->booking->id));
    }

    public function rendering(View $view)
    {
        $checkIn = $this->check_in ? Carbon::parse($this->check_in) : null;
        $checkOut = $this->check_out ? Carbon::parse($this->check_out) : null;

        // Get current house to include it even if not available for new dates
        $currentHouse = $this->booking->bookingable;

        if ($checkIn && $checkOut && $checkIn->lt($checkOut)) {
            // Get available houses excluding current booking
            $query = House::active()
                ->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                    $q->where('id', '!=', $this->booking->id)
                        ->whereIn('status', ['pending', 'booked', 'checked_in'])
                        ->where(function ($query) use ($checkIn, $checkOut) {
                            $query
                                ->whereBetween('check_in', [$checkIn, $checkOut])
                                ->orWhereBetween('check_out', [$checkIn, $checkOut])
                                ->orWhere(function ($q) use ($checkIn, $checkOut) {
                                    $q->where('check_in', '<=', $checkIn)->where('check_out', '>=', $checkOut);
                                });
                        });
                })
                ->with('rooms');

            // Filter by search term
            if (!empty($this->house_search)) {
                $search = $this->house_search;
                $query->where(function ($q) use ($search) {
                    $q->where('house_number', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%");
                });
            }

            $availableHouses = $query->orderBy('name')->get();

            // Include current house if it's not in the available list
            if ($currentHouse && !$availableHouses->contains('id', $currentHouse->id)) {
                $availableHouses->prepend($currentHouse);
            }

            // Manually paginate the collection
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $items = $availableHouses->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
            $view->availableHouses = new \Illuminate\Pagination\LengthAwarePaginator($items, $availableHouses->count(), $this->perPage, $currentPage, ['path' => request()->url(), 'query' => request()->query()]);
        } else {
            // If dates are invalid, still show current house
            $collection = $currentHouse ? collect([$currentHouse]) : collect();
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $items = $collection->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
            $view->availableHouses = new \Illuminate\Pagination\LengthAwarePaginator($items, $collection->count(), $this->perPage, $currentPage, ['path' => request()->url(), 'query' => request()->query()]);
        }

        // Set minimum date for check-in (current date/time)
        $view->minCheckInDate = Carbon::now()->format('Y-m-d\TH:i');
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
                'label' => 'Room Bookings',
            ],
            [
                'link' => route('admin.bookings.house.show', $booking->id),
                'label' => 'Booking Details',
            ],
            [
                'label' => 'Edit Booking',
            ],
        ];
    @endphp

    <x-header title="Edit Room Booking" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Update booking information</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.house.index') }}"
                class="btn-ghost btn-outline" />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="mx-auto">
        <x-form wire:submit="update">
            <div class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="space-y-6 lg:col-span-2">
                        {{-- Date Range Section --}}
                        <x-booking.date-range-section stepNumber="1" checkInLabel="Check In" checkOutLabel="Check Out"
                            :minCheckInDate="$minCheckInDate" checkIn="check_in" checkOut="check_out" />

                        {{-- Customer Section (Read-only) --}}
                        <x-card class="bg-base-200 opacity-75">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-base-content/50 font-semibold">
                                        Customer</p>
                                    <h3 class="text-xl font-semibold text-base-content mt-1">Customer Details</h3>
                                    <p class="text-sm text-base-content/60 mt-1">Customer cannot be changed</p>
                                </div>
                                <x-icon name="o-user" class="w-8 h-8 text-base-content/50" />
                            </div>
                            <div class="mt-6">
                                <div class="p-4 bg-base-200/50 rounded-lg">
                                    <div class="font-semibold text-base">{{ $booking->user->name ?? 'N/A' }}</div>
                                    <div class="text-sm text-base-content/60 mt-1">{{ $booking->user->email ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </x-card>

                        @php
                            $selectedHouse =
                                $availableHouses->firstWhere('id', $house_id) ??
                                ($house_id ? House::with('rooms')->find($house_id) : null);
                            $maxAdults = $selectedHouse?->adults ?? 10;
                            $maxChildren = $selectedHouse?->children ?? 10;
                        @endphp

                        {{-- Guest Details Section --}}
                        <x-booking.guest-section stepNumber="2" :maxAdults="$maxAdults" :maxChildren="$maxChildren" />

                        {{-- Room Selection Section --}}
                        <x-card class="bg-base-200">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step 3</p>
                                    <h3 class="text-xl font-semibold text-base-content mt-1">Room Selection</h3>
                                    <p class="text-sm text-base-content/60 mt-1">Choose from available rooms for your
                                        booking
                                    </p>
                                </div>
                                <x-icon name="o-home-modern" class="w-8 h-8 text-primary/70" />
                            </div>

                            @if ($check_in && $check_out && Carbon::parse($check_in)->lt(Carbon::parse($check_out)))
                                {{-- Search Input --}}
                                <div class="mt-4">
                                    <x-input wire:model.live.debounce.300ms="room_search" label="Search Rooms"
                                        placeholder="Search by room number, name, or house..." icon="o-magnifying-glass"
                                        clearable hint="Filter rooms by room number, name, or house" />
                                </div>

                                {{-- Filter Info --}}
                                <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-base-content/70">
                                    <div wire:loading wire:target="check_in,check_out,room_search,perPage"
                                        class="flex items-center gap-2 text-primary">
                                        <x-loading class="loading-dots" />
                                        <span>Loading rooms...</span>
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

                                {{-- Loading Overlay for Room Grid --}}
                                <div wire:loading wire:target="check_in,check_out,room_search,perPage" class="mt-4">
                                    <div
                                        class="flex items-center justify-center py-12 bg-base-200/50 rounded-xl border-2 border-dashed border-base-300">
                                        <div class="text-center">
                                            <x-loading class="loading-dots" />
                                            <p class="mt-4 text-sm text-base-content/70">Filtering available rooms...
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
                                                    $isCurrentHouse = $house->id == $booking->bookingable_id;
                                                @endphp
                                                <label wire:click="$wire.house_id = {{ $house->id }}"
                                                    class="relative cursor-pointer group block">
                                                    <input type="radio" wire:model.live="house_id"
                                                        value="{{ $house->id }}" class="sr-only">
                                                    <div
                                                        class="bg-base-100 border-2 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 h-full flex flex-col {{ $isSelected ? 'border-primary ring-2 ring-primary/20 shadow-lg' : 'border-base-300' }} {{ $isCurrentHouse ? 'ring-2 ring-info/30' : '' }}">
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
                                                                    @if ($house->rooms->count() > 0)
                                                                        <p
                                                                            class="text-xs text-primary font-medium mt-1">
                                                                            {{ $house->rooms->count() }}
                                                                            {{ $house->rooms->count() === 1 ? 'room' : 'rooms' }}
                                                                        </p>
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
                                                            {{-- Current House Badge --}}
                                                            @if ($isCurrentHouse)
                                                                <div class="mt-2">
                                                                    <div
                                                                        class="bg-info text-info-content px-2 py-1 rounded-md text-xs font-semibold shadow-md inline-block">
                                                                        CURRENT
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        {{-- Content Section --}}
                                                        <div class="p-4 flex-1 flex flex-col">
                                                            {{-- Details --}}
                                                            <div class="space-y-2 mb-4 flex-1">
                                                                @if ($house->adults || $house->children)
                                                                    <div
                                                                        class="flex items-center gap-2 text-sm text-base-content/70">
                                                                        <x-icon name="o-user-group"
                                                                            class="w-4 h-4 text-base-content/50" />
                                                                        <span>Max
                                                                            {{ ($house->adults ?? 0) + ($house->children ?? 0) }}
                                                                            guests</span>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            {{-- Price Section --}}
                                                            <div class="pt-3 border-t border-base-300">
                                                                <div class="flex items-baseline justify-between gap-2">
                                                                    <div class="flex-1">
                                                                        <div class="font-bold text-lg text-primary">
                                                                            {{ currency_format($house->price_per_night ?? 0) }}
                                                                        </div>
                                                                        <div class="text-xs text-base-content/50 mt-1">
                                                                            per night
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
                                                <p class="font-semibold">No rooms available</p>
                                                <p class="text-sm mt-1">
                                                    @if (!empty($room_search))
                                                        No rooms match your search criteria or are not available for
                                                        the
                                                        selected
                                                        date range.
                                                    @else
                                                        No rooms are available for the selected date range.
                                                        Please
                                                        choose different dates.
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
                                            available
                                            rooms.</p>
                                    </div>
                                </x-alert>
                            @endif
                        </x-card>

                        {{-- Payment Section --}}
                        <x-booking.payment-section stepNumber="4" />

                        {{-- Notes Section --}}
                        <x-booking.notes-section stepNumber="5" />
                    </div>

                    {{-- Summary Column --}}
                    @php
                        $selectedHouse =
                            $availableHouses->firstWhere('id', $house_id) ??
                            ($house_id ? House::find($house_id) : null);
                        $checkInDate = $check_in ? \Carbon\Carbon::parse($check_in) : null;
                        $checkOutDate = $check_out ? \Carbon\Carbon::parse($check_out) : null;
                    @endphp

                    <div class="sticky top-24">
                        <x-booking.booking-summary :adults="$adults" :children="$children" :checkInDate="$checkInDate"
                            :checkOutDate="$checkOutDate" :amount="$amount" :paymentMethod="$payment_method" :paymentStatus="$payment_status" :showChecklist="true"
                            :customerSelected="true" :selectionSelected="!!$house_id" :selectionLabel="'House'" :amountFilled="!!$amount"
                            :paymentMethodSelected="!!$payment_method" :paymentStatusSelected="!!$payment_status" :showInfoMessage="true" :infoTitle="'Booking Entire House'"
                            :infoMessage="'When you book a house, all rooms in that house will be reserved for your selected dates.'">
                            <x-slot:selection>
                                {{-- Selected Room --}}
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
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:justify-between">
                    <x-button icon="o-check" label="Update Booking" type="submit"
                        class="btn-primary w-full sm:w-auto" spinner="update" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
