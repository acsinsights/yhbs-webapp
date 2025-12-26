<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use Illuminate\View\View;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use App\Enums\RolesEnum;
use App\Models\{Booking, User, Yacht};

new class extends Component {
    use Toast, WithPagination;

    public Booking $booking;

    public ?int $yacht_id = null;
    public ?string $check_in = null;
    public ?string $check_out = null;
    public ?string $date_range = null;
    public ?int $adults = 1;
    public ?int $children = 0;
    public ?float $amount = null;
    public bool $amountManuallySet = false;
    public string $payment_method = 'cash';
    public string $payment_status = 'pending';
    public ?string $notes = null;
    public string $yacht_search = '';
    public int $perPage = 6;

    // Price breakdown properties
    public ?int $totalDays = null;
    public ?float $calculatedAmount = null;
    public ?float $basePrice = null;
    public ?float $discount = null;
    public ?float $raisedAmount = null;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['bookingable', 'user']);

        // Check if booking can be edited
        if (!$booking->canBeEdited()) {
            $this->warning('This booking cannot be edited.', redirectTo: route('admin.bookings.yacht.index'));
            return;
        }

        // Pre-fill form with existing booking data
        $this->yacht_id = $booking->bookingable_id;
        $this->check_in = $booking->check_in ? $booking->check_in->format('Y-m-d\TH:i') : null;
        $this->check_out = $booking->check_out ? $booking->check_out->format('Y-m-d\TH:i') : null;

        // Set date range from existing dates
        if ($booking->check_in && $booking->check_out) {
            $this->date_range = $booking->check_in->format('Y-m-d') . ' to ' . $booking->check_out->format('Y-m-d');
        }

        $this->adults = $booking->adults ?? 1;
        $this->children = $booking->children ?? 0;
        $this->amount = $booking->price;
        $this->amountManuallySet = true; // Set to true since we're loading existing amount
        $this->payment_method = $booking->payment_method->value ?? 'cash';
        $this->payment_status = $booking->payment_status->value ?? 'pending';
        $this->notes = $booking->notes;

        // Calculate price breakdown for existing booking
        $this->calculatePrice();
    }

    public function updatedDateRange(): void
    {
        if (!$this->date_range) {
            return;
        }

        // Parse date range (format: "2025-01-15 to 2025-01-20")
        $dates = explode(' to ', $this->date_range);

        if (count($dates) === 2) {
            // Set check-in at 12:00 PM and check-out at 12:00 PM
            $this->check_in = Carbon::parse(trim($dates[0]))
                ->setTime(12, 0)
                ->format('Y-m-d\TH:i');
            $this->check_out = Carbon::parse(trim($dates[1]))
                ->setTime(12, 0)
                ->format('Y-m-d\TH:i');

            $this->resetPage();
            $this->yacht_id = null;
            if (!$this->amountManuallySet) {
                $this->amount = null;
            }

            // Recalculate price if yacht is selected
            if ($this->yacht_id && !$this->amountManuallySet) {
                $this->calculatePrice();
            }
        }
    }

    public function updatedAdults(): void
    {
        $this->resetPage();
        if (!$this->amountManuallySet) {
            $this->amount = null;
        }
    }

    public function updatedChildren(): void
    {
        $this->resetPage();
        if (!$this->amountManuallySet) {
            $this->amount = null;
        }
    }

    public function updatedCheckIn(): void
    {
        $this->resetPage();

        // Validate that check_in is not in the past
        if ($this->check_in) {
            $checkIn = Carbon::parse($this->check_in);
            $now = Carbon::now();

            if ($checkIn->lt($now)) {
                $this->error('Departure date and time must be equal to or after the current date and time.');
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

        // Recalculate price if yacht is selected
        if ($this->yacht_id && !$this->amountManuallySet) {
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
                $this->error('Return date and time must be after departure date and time.');
                $this->check_out = $checkIn->copy()->addHour()->format('Y-m-d\TH:i');
            }
        }

        // Recalculate price if yacht is selected
        if ($this->yacht_id && !$this->amountManuallySet) {
            $this->calculatePrice();
        }
    }

    public function updatedYachtSearch(): void
    {
        $this->resetPage();
    }

    public function updatedYachtId(): void
    {
        // When yacht changes, calculate price based on days
        $this->amountManuallySet = false;

        if ($this->yacht_id) {
            $this->calculatePrice();
        } else {
            // Reset amount when yacht selection is cleared
            $this->amount = null;
            $this->calculatedAmount = null;
            $this->basePrice = null;
            $this->discount = null;
            $this->raisedAmount = null;
            $this->totalDays = null;
            $this->dispatch('amount-updated');
        }
    }

    public function calculatePrice(): void
    {
        if (!$this->yacht_id || !$this->check_in || !$this->check_out) {
            return;
        }

        $yacht = Yacht::find($this->yacht_id);
        if (!$yacht) {
            return;
        }

        $checkIn = Carbon::parse($this->check_in);
        $checkOut = Carbon::parse($this->check_out);
        $days = $checkIn->diffInDays($checkOut);

        if ($days <= 0) {
            $days = 1; // Minimum 1 day for yacht charters
        }

        $this->totalDays = $days;
        $this->basePrice = $yacht->discount_price ?? ($yacht->price ?? 0);

        // For yachts, multiply base price by number of days
        $this->calculatedAmount = $this->basePrice * $days;

        // Only update amount if not manually set
        if (!$this->amountManuallySet) {
            $this->amount = $this->calculatedAmount;
        }

        // Calculate discount or raised amount
        if ($this->amount && $this->calculatedAmount && $this->amount != $this->calculatedAmount) {
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
        }
    }

    public function update(): void
    {
        $this->validate(
            [
                'yacht_id' => 'required|exists:yachts,id',
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
        $totalGuests = $this->adults + $this->children;

        $yacht = Yacht::find($this->yacht_id);

        if (!$yacht) {
            $this->error('Selected yacht not found.');
            return;
        }

        // Check if yacht is available for the date range (excluding current booking)
        // If it's the same yacht, we allow the update
        if ($this->yacht_id != $this->booking->bookingable_id) {
            // Check if the new yacht is available (excluding current booking)
            $hasConflict = Booking::where('bookingable_type', Yacht::class)
                ->where('bookingable_id', $this->yacht_id)
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
                $this->error('Selected yacht is not available for the chosen dates.');
                return;
            }
        }

        // Check guest capacity
        if ($yacht->max_guests && $totalGuests > $yacht->max_guests) {
            $this->error("Selected yacht can accommodate maximum {$yacht->max_guests} guests, but you have selected {$totalGuests} guests.");
            return;
        }

        $this->booking->update([
            'bookingable_id' => $this->yacht_id,
            'adults' => $this->adults,
            'children' => $this->children,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'price' => $this->amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'notes' => $this->notes,
        ]);

        $this->success('Booking updated successfully.', redirectTo: route('admin.bookings.yacht.show', $this->booking->id));
    }

    public function rendering(View $view)
    {
        $checkIn = $this->check_in ? Carbon::parse($this->check_in) : null;
        $checkOut = $this->check_out ? Carbon::parse($this->check_out) : null;

        $totalGuests = $this->adults + $this->children;

        // Get current yacht to include it even if not available for new dates
        $currentYacht = $this->booking->bookingable;

        // Only query if we have valid dates
        if ($checkIn && $checkOut && $checkIn->lt($checkOut)) {
            // Get available yachts excluding current booking
            $query = Yacht::whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
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
            });

            // Filter by guest capacity
            if ($totalGuests > 0) {
                $query->where(function ($q) use ($totalGuests) {
                    $q->whereNull('max_guests')->orWhere('max_guests', '>=', $totalGuests);
                });
            }

            // Filter by search term
            if (!empty($this->yacht_search)) {
                $search = $this->yacht_search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $availableYachtes = $query->orderBy('name')->get();

            // Include current yacht if it's not in the available list
            if ($currentYacht && !$availableYachtes->contains('id', $currentYacht->id)) {
                $availableYachtes->prepend($currentYacht);
            }

            // Manually paginate the collection
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $items = $availableYachtes->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
            $view->availableYachtes = new \Illuminate\Pagination\LengthAwarePaginator($items, $availableYachtes->count(), $this->perPage, $currentPage, ['path' => request()->url(), 'query' => request()->query()]);
        } else {
            // If dates are invalid, still show current yacht
            $collection = $currentYacht ? collect([$currentYacht]) : collect();
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $items = $collection->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
            $view->availableYachtes = new \Illuminate\Pagination\LengthAwarePaginator($items, $collection->count(), $this->perPage, $currentPage, ['path' => request()->url(), 'query' => request()->query()]);
        }

        // Pass parsed dates to view
        $view->checkInDate = $checkIn;
        $view->checkOutDate = $checkOut;

        // Set minimum date for departure (current date/time)
        $view->minDepartureDate = Carbon::now()->format('Y-m-d\TH:i');

        // Pass breakdown data to view
        $view->totalDays = $this->totalDays;
        $view->basePrice = $this->basePrice;
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
                'link' => route('admin.bookings.yacht.index'),
                'label' => 'Yacht Bookings',
            ],
            [
                'link' => route('admin.bookings.yacht.show', $booking->id),
                'label' => 'Booking Details',
            ],
            [
                'label' => 'Edit Booking',
            ],
        ];
    @endphp

    <x-header title="Edit Yacht Booking" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Update booking information</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.yacht.show', $booking->id) }}"
                class="btn-ghost btn-outline" />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="mx-auto">
        <x-form wire:submit="update">
            <div class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="space-y-6 lg:col-span-2">
                        {{-- Date Range Section --}}
                        <x-booking.date-range-section stepNumber="1" checkInLabel="Departure" checkOutLabel="Return"
                            :minCheckInDate="$minCheckInDate" dateRangeModel="date_range" />

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
                            $selectedYacht =
                                $availableYachtes->firstWhere('id', $yacht_id) ??
                                ($yacht_id ? Yacht::find($yacht_id) : null);
                            $maxAdults = $selectedYacht?->max_guests ?? 20;
                            $maxChildren = $selectedYacht?->max_guests ?? 20;
                        @endphp

                        {{-- Guest Details Section --}}
                        <x-booking.guest-section stepNumber="2" :maxAdults="$maxAdults" :maxChildren="$maxChildren" />

                        {{-- Yacht Selection Section --}}
                        <x-card class="bg-base-200">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step 3</p>
                                    <h3 class="text-xl font-semibold text-base-content mt-1">Yacht Selection</h3>
                                    <p class="text-sm text-base-content/60 mt-1">Choose from available yachts for your
                                        charter
                                    </p>
                                </div>
                                <x-icon name="o-sparkles" class="w-8 h-8 text-primary/70" />
                            </div>

                            @if ($check_in && $check_out && Carbon::parse($check_in)->lt(Carbon::parse($check_out)))
                                {{-- Search Input --}}
                                <div class="mt-4">
                                    <x-input wire:model.live.debounce.300ms="yacht_search" label="Search Yachts"
                                        placeholder="Search by name, SKU, or description..." icon="o-magnifying-glass"
                                        clearable hint="Filter yachts by name, SKU, or description" />
                                </div>

                                {{-- Filter Info --}}
                                @php
                                    $totalGuests = $adults + $children;
                                @endphp
                                <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-base-content/70">
                                    <div wire:loading wire:target="check_in,check_out,adults,children,yacht_search"
                                        class="flex items-center gap-2 text-primary">
                                        <x-loading class="loading-dots" />
                                        <span>Loading yachts...</span>
                                    </div>

                                    <div wire:loading.remove
                                        wire:target="check_in,check_out,adults,children,yacht_search,perPage"
                                        class="flex items-center gap-2">
                                        <x-icon name="o-funnel" class="w-4 h-4" />
                                        <span>
                                            <strong>{{ $availableYachtes->total() }}</strong>
                                            {{ $availableYachtes->total() === 1 ? 'yacht' : 'yachts' }} available
                                            @if ($availableYachtes->total() > $availableYachtes->count())
                                                (Showing
                                                {{ $availableYachtes->firstItem() }}-{{ $availableYachtes->lastItem() }}
                                                of {{ $availableYachtes->total() }})
                                            @endif
                                        </span>
                                    </div>
                                    @if ($totalGuests > 0)
                                        <div wire:loading.remove
                                            wire:target="check_in,check_out,adults,children,yacht_search,perPage"
                                            class="flex items-center gap-2">
                                            <x-icon name="o-user-group" class="w-4 h-4" />
                                            <span>Filtered for {{ $totalGuests }}
                                                {{ $totalGuests === 1 ? 'guest' : 'guests' }}</span>
                                        </div>
                                    @endif
                                    @if (!empty($yacht_search))
                                        <div wire:loading.remove
                                            wire:target="check_in,check_out,adults,children,yacht_search,perPage"
                                            class="flex items-center gap-2">
                                            <x-icon name="o-magnifying-glass" class="w-4 h-4" />
                                            <span>Search: "{{ $yacht_search }}"</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Loading Overlay for Yacht Grid --}}
                                <div wire:loading wire:target="check_in,check_out,adults,children,yacht_search,perPage"
                                    class="mt-4">
                                    <div
                                        class="flex items-center justify-center py-12 bg-base-200/50 rounded-xl border-2 border-dashed border-base-300">
                                        <div class="text-center">
                                            <x-loading class="loading-dots" />
                                            <p class="mt-4 text-sm text-base-content/70">Filtering available yachts...
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div wire:loading.remove
                                    wire:target="check_in,check_out,adults,children,yacht_search,perPage">
                                    @if ($availableYachtes->count() > 0)
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                                            @foreach ($availableYachtes as $yacht)
                                                @php
                                                    $isSelected = $yacht_id == $yacht->id;
                                                    $isCurrentYacht = $yacht->id == $booking->bookingable_id;
                                                @endphp
                                                <label wire:click="$wire.yacht_id = {{ $yacht->id }}"
                                                    class="relative cursor-pointer group block">
                                                    <input type="radio" wire:model.live="yacht_id"
                                                        value="{{ $yacht->id }}" class="sr-only">
                                                    <div
                                                        class="bg-base-100 border-2 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 h-full flex flex-col {{ $isSelected ? 'border-primary ring-2 ring-primary/20 shadow-lg' : 'border-base-300' }} {{ $isCurrentYacht ? 'ring-2 ring-info/30' : '' }}">
                                                        {{-- Image Section --}}
                                                        <div class="relative aspect-4/3 bg-base-200 overflow-hidden">
                                                            @if ($yacht->image)
                                                                <img src="{{ asset($yacht->image) }}"
                                                                    alt="{{ $yacht->name }}"
                                                                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                                            @else
                                                                <div
                                                                    class="w-full h-full flex items-center justify-center bg-linear-to-br from-base-200 to-base-300">
                                                                    <x-icon name="o-photo"
                                                                        class="w-16 h-16 text-base-content/30" />
                                                                </div>
                                                            @endif

                                                            {{-- Selection Indicator --}}
                                                            <div class="absolute top-3 right-3">
                                                                <div
                                                                    class="w-6 h-6 rounded-full border-2 border-base-100 bg-base-100/80 backdrop-blur-sm flex items-center justify-center transition-all {{ $isSelected ? 'bg-primary border-primary' : '' }}">
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

                                                            {{-- Current Yacht Badge --}}
                                                            @if ($isCurrentYacht)
                                                                <div class="absolute top-3 left-3">
                                                                    <div
                                                                        class="bg-info text-info-content px-2 py-1 rounded-md text-xs font-semibold shadow-md">
                                                                        CURRENT
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            {{-- Discount Badge --}}
                                                            @if ($yacht->discount_price && $yacht->price && $yacht->discount_price < $yacht->price)
                                                                <div
                                                                    class="absolute {{ $isCurrentYacht ? 'top-12' : 'top-3' }} left-3">
                                                                    <div
                                                                        class="bg-primary text-primary-content px-2 py-1 rounded-md text-xs font-semibold shadow-md">
                                                                        {{ number_format((($yacht->price - $yacht->discount_price) / $yacht->price) * 100, 0) }}%
                                                                        OFF
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        {{-- Content Section --}}
                                                        <div class="p-4 flex-1 flex flex-col">
                                                            {{-- Name and SKU --}}
                                                            <div class="mb-3">
                                                                <h4
                                                                    class="font-semibold text-base text-base-content mb-1 line-clamp-1">
                                                                    {{ $yacht->name }}
                                                                </h4>
                                                                @if ($yacht->sku)
                                                                    <p class="text-xs text-base-content/50 font-mono">
                                                                        SKU: {{ $yacht->sku }}
                                                                    </p>
                                                                @endif
                                                            </div>

                                                            {{-- Details Grid --}}
                                                            <div class="space-y-2 mb-4 flex-1">
                                                                @if ($yacht->max_guests)
                                                                    <div
                                                                        class="flex items-center gap-2 text-sm text-base-content/70">
                                                                        <x-icon name="o-user-group"
                                                                            class="w-4 h-4 text-base-content/50" />
                                                                        <span>Max {{ $yacht->max_guests }}
                                                                            guests</span>
                                                                    </div>
                                                                @endif

                                                                @if ($yacht->length)
                                                                    <div
                                                                        class="flex items-center gap-2 text-sm text-base-content/70">
                                                                        <x-icon name="o-arrows-pointing-out"
                                                                            class="w-4 h-4 text-base-content/50" />
                                                                        <span>{{ $yacht->length }}m length</span>
                                                                    </div>
                                                                @endif

                                                                @if ($yacht->max_crew)
                                                                    <div
                                                                        class="flex items-center gap-2 text-sm text-base-content/70">
                                                                        <x-icon name="o-user"
                                                                            class="w-4 h-4 text-base-content/50" />
                                                                        <span>Up to {{ $yacht->max_crew }} crew</span>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            {{-- Price Section --}}
                                                            <div class="pt-3 border-t border-base-300">
                                                                <div class="flex items-baseline justify-between gap-2">
                                                                    <div class="flex-1">
                                                                        <div class="font-bold text-lg text-primary">
                                                                            {{ currency_format($yacht->discount_price ?? ($yacht->price ?? 0)) }}
                                                                        </div>
                                                                        @if ($yacht->discount_price && $yacht->price && $yacht->discount_price < $yacht->price)
                                                                            <div
                                                                                class="text-xs text-base-content/50 line-through">
                                                                                {{ currency_format($yacht->price) }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>

                                        {{-- Pagination --}}
                                        @if ($availableYachtes->hasPages())
                                            <div
                                                class="mt-6 flex items-center justify-between border-t border-base-300 pt-4">
                                                <div class="text-sm text-base-content/70">
                                                    Showing {{ $availableYachtes->firstItem() }} to
                                                    {{ $availableYachtes->lastItem() }} of
                                                    {{ $availableYachtes->total() }} results
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    {{ $availableYachtes->links() }}
                                                </div>
                                            </div>
                                        @endif

                                        @if ($yacht_id)
                                            <div class="mt-4 p-3 bg-primary/10 border border-primary/20 rounded-lg">
                                                <div class="flex items-center gap-2 text-sm text-primary">
                                                    <x-icon name="o-check-circle" class="w-5 h-5" />
                                                    <span class="font-medium">Yacht selected:
                                                        {{ $availableYachtes->firstWhere('id', $yacht_id)?->name ?? Yacht::find($yacht_id)?->name }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    @else
                                        <x-alert icon="o-exclamation-triangle" class="alert-warning mt-4">
                                            <div>
                                                <p class="font-semibold">No yachts available</p>
                                                <p class="text-sm mt-1">
                                                    @if (!empty($yacht_search))
                                                        No yachts match your search criteria or are not available for
                                                        the
                                                        selected
                                                        date range and guest count.
                                                    @else
                                                        No yachts are available for the selected date range and guest
                                                        count.
                                                        Please
                                                        choose different dates or adjust guest count.
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
                                        <p class="text-sm mt-1">Please select departure and return dates to see
                                            available
                                            yachts.</p>
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
                        $selectedYacht =
                            $availableYachtes->firstWhere('id', $yacht_id) ??
                            ($yacht_id ? Yacht::find($yacht_id) : null);
                        $checkInDate = $check_in ? \Carbon\Carbon::parse($check_in) : null;
                        $checkOutDate = $check_out ? \Carbon\Carbon::parse($check_out) : null;
                    @endphp

                    <div class="sticky top-24">
                        <x-booking.booking-summary :adults="$adults" :children="$children" :checkInDate="$checkInDate"
                            :checkOutDate="$checkOutDate" checkInLabel="Departure" checkOutLabel="Return"
                            windowLabel="Charter Window" :amount="$amount" :paymentMethod="$payment_method" :paymentStatus="$payment_status"
                            :showChecklist="true" :customerSelected="true" :selectionSelected="!!$yacht_id" :selectionLabel="'Yacht'"
                            :amountFilled="!!$amount" :paymentMethodSelected="!!$payment_method" :paymentStatusSelected="!!$payment_status">
                            <x-slot:selection>
                                {{-- Selected Yacht --}}
                                <div class="bg-base-100/80 rounded-lg p-2.5 border border-base-300/50">
                                    <div class="flex items-start gap-2">
                                        <div
                                            class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center shrink-0">
                                            <x-icon name="o-sparkles" class="w-4 h-4 text-primary" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-base-content/60 mb-0.5">Selected Yacht
                                            </p>
                                            @if ($selectedYacht)
                                                <p class="text-xs font-bold text-base-content line-clamp-1">
                                                    {{ $selectedYacht->name }}</p>
                                                @if ($selectedYacht->sku)
                                                    <p class="text-xs text-base-content/60 font-mono">SKU:
                                                        {{ $selectedYacht->sku }}</p>
                                                @endif
                                            @else
                                                <p class="text-xs text-base-content/50 italic">No yacht selected</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </x-slot:selection>

                            {{-- Price Breakdown --}}
                            <x-slot:extraSections>
                                @if ($totalDays && $basePrice !== null)
                                    <x-card class="p-4 bg-base-100 mb-4">
                                        <p class="text-xs uppercase tracking-wide text-base-content/60 mb-3">Price
                                            Breakdown</p>
                                        <div class="space-y-2 text-sm">
                                            {{-- Base Price per Day --}}
                                            <div class="flex justify-between items-center">
                                                <span class="text-base-content/70">Base Price per Day:</span>
                                                <span
                                                    class="font-semibold text-base-content">{{ currency_format($basePrice) }}</span>
                                            </div>

                                            {{-- Number of Days --}}
                                            <div class="flex justify-between items-center">
                                                <span class="text-base-content/70">Number of Days:</span>
                                                <span class="font-semibold text-base-content">{{ $totalDays }}
                                                    {{ $totalDays === 1 ? 'day' : 'days' }}</span>
                                            </div>

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
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <x-button icon="o-arrow-left" label="Back"
                            link="{{ route('admin.bookings.yacht.show', $booking->id) }}"
                            class="btn-ghost w-full sm:w-auto" responsive />
                    </div>
                    <x-button icon="o-check" label="Update Booking" type="submit"
                        class="btn-primary w-full sm:w-auto" spinner="update" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
