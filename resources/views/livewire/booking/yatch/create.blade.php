<?php

use App\Models\Booking;
use App\Models\Yatch;
use App\Models\User;
use App\Enums\RolesEnum;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

new class extends Component {
    use Toast;

    public ?int $user_id = null;
    public string $customer_name = '';
    public string $customer_email = '';
    public bool $createCustomerModal = false;

    public ?int $yatch_id = null;
    public ?string $check_in = null;
    public ?string $check_out = null;
    public ?int $adults = 1;
    public ?int $children = 0;
    public ?float $amount = null;
    public string $payment_method = 'cash';
    public string $payment_status = 'pending';
    public ?string $notes = null;
    public string $yatch_search = '';

    public function mount(): void
    {
        $this->check_in = Carbon::now()->format('Y-m-d\TH:i');
        $this->check_out = Carbon::tomorrow()->format('Y-m-d\TH:i');
    }

    public function updatedAdults(): void
    {
        $this->yatch_id = null;
        $this->amount = null;
    }

    public function updatedChildren(): void
    {
        $this->yatch_id = null;
        $this->amount = null;
    }

    public function updatedCheckIn(): void
    {
        $this->yatch_id = null;
        $this->amount = null;

        // Validate that check_in is not in the past
        if ($this->check_in) {
            $checkIn = Carbon::parse($this->check_in);
            $now = Carbon::now();

            if ($checkIn->lt($now)) {
                $this->error('Departure date and time must be equal to or after the current date and time.');
                // Set check_in to current date/time
                $this->check_in = $now->format('Y-m-d\TH:i');
                return;
            }
        }

        // Ensure check_out is after check_in
        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);

            if ($checkOut->lte($checkIn)) {
                // Set check_out to 1 hour after check_in
                $this->check_out = $checkIn->copy()->addHour()->format('Y-m-d\TH:i');
            }
        }
    }

    public function updatedCheckOut(): void
    {
        $this->yatch_id = null;
        $this->amount = null;

        // Validate that check_out is after check_in
        if ($this->check_in && $this->check_out) {
            $checkIn = Carbon::parse($this->check_in);
            $checkOut = Carbon::parse($this->check_out);

            if ($checkOut->lte($checkIn)) {
                $this->error('Return date and time must be after departure date and time.');
                // Set check_out to 1 hour after check_in
                $this->check_out = $checkIn->copy()->addHour()->format('Y-m-d\TH:i');
            }
        }
    }

    public function updatedYatchId(): void
    {
        if ($this->yatch_id) {
            $yatch = Yatch::find($this->yatch_id);
            if ($yatch) {
                $price = $yatch->discount_price ?? $yatch->price;
                $this->amount = $price !== null ? (float) $price : null;
            } else {
                $this->amount = null;
            }
        } else {
            // Reset amount when yacht selection is cleared
            $this->amount = null;
        }
    }

    public function createCustomer(): void
    {
        $this->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|unique:users,email',
        ]);

        $password = Hash::make(Str::random(12));

        $user = User::create([
            'name' => $this->customer_name,
            'email' => $this->customer_email,
            'password' => $password,
        ]);

        $user->assignRole(RolesEnum::CUSTOMER->value);

        $this->user_id = $user->id;
        $this->createCustomerModal = false;
        $this->customer_name = '';
        $this->customer_email = '';
        $this->success('Customer created successfully.');
    }

    public function store(): void
    {
        $this->validate([
            'user_id' => 'required|exists:users,id',
            'yatch_id' => 'required|exists:yatches,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card',
            'payment_status' => 'required|in:paid,pending',
        ]);

        $checkIn = Carbon::parse($this->check_in);
        $checkOut = Carbon::parse($this->check_out);
        $totalGuests = $this->adults + $this->children;

        $yatch = Yatch::find($this->yatch_id);

        if (!$yatch) {
            $this->error('Selected yacht not found.');
            return;
        }

        // Check if yacht is available for the date range
        $isAvailable = Yatch::available($checkIn, $checkOut)->where('id', $this->yatch_id)->exists();
        if (!$isAvailable) {
            $this->error('Selected yacht is not available for the chosen dates.');
            return;
        }

        // Check guest capacity
        if ($yatch->max_guests && $totalGuests > $yatch->max_guests) {
            $this->error("Selected yacht can accommodate maximum {$yatch->max_guests} guests, but you have selected {$totalGuests} guests.");
            return;
        }

        $booking = Booking::create([
            'bookingable_type' => Yatch::class,
            'bookingable_id' => $this->yatch_id,
            'user_id' => $this->user_id,
            'adults' => $this->adults,
            'children' => $this->children,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'price' => $this->amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'status' => 'pending',
            'notes' => $this->notes,
        ]);

        $this->success('Booking created successfully.', redirectTo: route('admin.bookings.yatch.show', $booking->id));
    }

    public function rendering(View $view)
    {
        $checkIn = $this->check_in ? Carbon::parse($this->check_in) : null;
        $checkOut = $this->check_out ? Carbon::parse($this->check_out) : null;

        $totalGuests = $this->adults + $this->children;

        // Only query if we have valid dates
        if ($checkIn && $checkOut && $checkIn->lt($checkOut)) {
            $query = Yatch::available($checkIn, $checkOut);

            // Filter by guest capacity
            if ($totalGuests > 0) {
                $query->where(function ($q) use ($totalGuests) {
                    $q->whereNull('max_guests')->orWhere('max_guests', '>=', $totalGuests);
                });
            }

            // Filter by search term
            if (!empty($this->yatch_search)) {
                $search = $this->yatch_search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            $view->availableYatches = $query->orderBy('name')->get();
        } else {
            $view->availableYatches = collect();
        }

        $view->customers = User::role(RolesEnum::CUSTOMER->value)->orderBy('name')->get();

        // Set minimum date for departure (current date/time)
        $view->minDepartureDate = Carbon::now()->format('Y-m-d\TH:i');
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
                'link' => route('admin.bookings.yatch.index'),
                'label' => 'Yacht Bookings',
            ],
            [
                'label' => 'Create Booking',
            ],
        ];
    @endphp

    <x-header title="Create Yacht Booking" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Create a new yacht charter booking</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.yatch.index') }}"
                class="btn-ghost" />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="mx-auto">
        <x-form wire:submit="store">
            <div class="space-y-8">
                {{-- Date Range Section --}}
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-base-content">Charter Dates</h3>
                        <p class="text-sm text-base-content/60 mt-1">Select your departure and return dates</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input wire:model.live.debounce.300ms="check_in" label="Departure" type="datetime-local"
                            icon="o-calendar" :min="$minDepartureDate" hint="Departure must be today or later" />
                        <x-input wire:model.live.debounce.300ms="check_out" label="Return" type="datetime-local"
                            icon="o-calendar" :min="$check_in" hint="Return must be after departure" />
                    </div>
                </div>

                <div class="divider"></div>

                {{-- Customer Section --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-base-content">Customer Details</h3>
                            <p class="text-sm text-base-content/60 mt-1">Select or create a customer for this booking
                            </p>
                        </div>
                        <x-button type="button" icon="o-plus" label="New Customer"
                            @click="$wire.createCustomerModal = true" class="btn-sm btn-primary" />
                    </div>

                    <x-choices-offline wire:model="user_id" label="Select Customer" placeholder="Choose a customer"
                        :options="$customers" icon="o-user" hint="Select existing customer or create a new one" single
                        clearable searchable>
                        @scope('item', $customer)
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200/50 transition-colors">
                                <div class="shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-base mb-1 truncate">{{ $customer->name }}</div>
                                    <div class="text-xs text-base-content/60 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        <span class="truncate">{{ $customer->email }}</span>
                                    </div>
                                </div>
                            </div>
                        @endscope
                        @scope('selection', $customer)
                            {{ $customer->name }}
                        @endscope
                    </x-choices-offline>
                </div>

                <div class="divider"></div>

                {{-- Guest Details Section --}}
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-base-content">Guest Details</h3>
                        <p class="text-sm text-base-content/60 mt-1">Number of guests for this charter</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input wire:model.live.debounce.300ms="adults" label="Adults" type="number" min="1"
                            icon="o-user-group" />
                        <x-input wire:model.live.debounce.300ms="children" label="Children" type="number"
                            min="0" icon="o-face-smile" />
                    </div>
                </div>

                <div class="divider"></div>

                {{-- Yacht Selection Section --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-base-content">Yacht Selection</h3>
                            <p class="text-sm text-base-content/60 mt-1">Choose from available yachts for your charter
                            </p>
                        </div>
                    </div>

                    @if ($check_in && $check_out && Carbon::parse($check_in)->lt(Carbon::parse($check_out)))
                        {{-- Search Input --}}
                        <div class="mt-4">
                            <x-input wire:model.live.debounce.300ms="yatch_search" label="Search Yachts"
                                placeholder="Search by name, SKU, or description..." icon="o-magnifying-glass" clearable
                                hint="Filter yachts by name, SKU, or description" />
                        </div>

                        {{-- Filter Info --}}
                        @php
                            $totalGuests = $adults + $children;
                        @endphp
                        <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-base-content/70">
                            {{-- Loading Indicator --}}
                            <div wire:loading wire:target="check_in,check_out,adults,children,yatch_search"
                                class="flex items-center gap-2 text-primary">
                                <span class="loading loading-spinner loading-sm"></span>
                                <span>Loading yachts...</span>
                            </div>

                            <div wire:loading.remove wire:target="check_in,check_out,adults,children,yatch_search"
                                class="flex items-center gap-2">
                                <x-icon name="o-funnel" class="w-4 h-4" />
                                <span>
                                    <strong>{{ $availableYatches->count() }}</strong>
                                    {{ $availableYatches->count() === 1 ? 'yacht' : 'yachts' }} available
                                </span>
                            </div>
                            @if ($totalGuests > 0)
                                <div wire:loading.remove wire:target="check_in,check_out,adults,children,yatch_search"
                                    class="flex items-center gap-2">
                                    <x-icon name="o-user-group" class="w-4 h-4" />
                                    <span>Filtered for {{ $totalGuests }}
                                        {{ $totalGuests === 1 ? 'guest' : 'guests' }}</span>
                                </div>
                            @endif
                            @if (!empty($yatch_search))
                                <div wire:loading.remove wire:target="check_in,check_out,adults,children,yatch_search"
                                    class="flex items-center gap-2">
                                    <x-icon name="o-magnifying-glass" class="w-4 h-4" />
                                    <span>Search: "{{ $yatch_search }}"</span>
                                </div>
                            @endif
                        </div>

                        {{-- Loading Overlay for Yacht Grid --}}
                        <div wire:loading wire:target="check_in,check_out,adults,children,yatch_search"
                            class="mt-4">
                            <div
                                class="flex items-center justify-center py-12 bg-base-200/50 rounded-xl border-2 border-dashed border-base-300">
                                <div class="text-center">
                                    <span class="loading loading-spinner loading-lg text-primary"></span>
                                    <p class="mt-4 text-sm text-base-content/70">Filtering available yachts...</p>
                                </div>
                            </div>
                        </div>

                        <div wire:loading.remove wire:target="check_in,check_out,adults,children,yatch_search">
                            @if ($availableYatches->count() > 0)
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mt-4">
                                    @foreach ($availableYatches as $yatch)
                                        @php
                                            $isSelected = $yatch_id == $yatch->id;
                                        @endphp
                                        <label wire:click="$wire.yatch_id = {{ $yatch->id }}"
                                            class="relative cursor-pointer group block">
                                            <input type="radio" wire:model.live="yatch_id"
                                                value="{{ $yatch->id }}" class="sr-only">
                                            <div
                                                class="bg-base-100 border-2 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 h-full flex flex-col {{ $isSelected ? 'border-primary ring-2 ring-primary/20 shadow-lg' : 'border-base-300' }}">
                                                {{-- Image Section --}}
                                                <div class="relative aspect-[4/3] bg-base-200 overflow-hidden">
                                                    @if ($yatch->image)
                                                        <img src="{{ asset($yatch->image) }}"
                                                            alt="{{ $yatch->name }}"
                                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" />
                                                    @else
                                                        <div
                                                            class="w-full h-full flex items-center justify-center bg-gradient-to-br from-base-200 to-base-300">
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

                                                    {{-- Discount Badge --}}
                                                    @if ($yatch->discount_price && $yatch->price && $yatch->discount_price < $yatch->price)
                                                        <div class="absolute top-3 left-3">
                                                            <div
                                                                class="bg-error text-error-content px-2 py-1 rounded-md text-xs font-semibold shadow-md">
                                                                {{ number_format((($yatch->price - $yatch->discount_price) / $yatch->price) * 100, 0) }}%
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
                                                            {{ $yatch->name }}
                                                        </h4>
                                                        @if ($yatch->sku)
                                                            <p class="text-xs text-base-content/50 font-mono">
                                                                SKU: {{ $yatch->sku }}
                                                            </p>
                                                        @endif
                                                    </div>

                                                    {{-- Details Grid --}}
                                                    <div class="space-y-2 mb-4 flex-1">
                                                        @if ($yatch->max_guests)
                                                            <div
                                                                class="flex items-center gap-2 text-sm text-base-content/70">
                                                                <x-icon name="o-user-group"
                                                                    class="w-4 h-4 text-base-content/50" />
                                                                <span>Max {{ $yatch->max_guests }} guests</span>
                                                            </div>
                                                        @endif

                                                        @if ($yatch->length)
                                                            <div
                                                                class="flex items-center gap-2 text-sm text-base-content/70">
                                                                <x-icon name="o-arrows-pointing-out"
                                                                    class="w-4 h-4 text-base-content/50" />
                                                                <span>{{ $yatch->length }}m length</span>
                                                            </div>
                                                        @endif

                                                        @if ($yatch->max_crew)
                                                            <div
                                                                class="flex items-center gap-2 text-sm text-base-content/70">
                                                                <x-icon name="o-user"
                                                                    class="w-4 h-4 text-base-content/50" />
                                                                <span>Up to {{ $yatch->max_crew }} crew</span>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- Price Section --}}
                                                    <div class="pt-3 border-t border-base-300">
                                                        <div class="flex items-baseline justify-between gap-2">
                                                            <div class="flex-1">
                                                                <div class="font-bold text-lg text-primary">
                                                                    {{ currency_format($yatch->discount_price ?? ($yatch->price ?? 0)) }}
                                                                </div>
                                                                @if ($yatch->discount_price && $yatch->price && $yatch->discount_price < $yatch->price)
                                                                    <div
                                                                        class="text-xs text-base-content/50 line-through">
                                                                        {{ currency_format($yatch->price) }}
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

                                @if ($yatch_id)
                                    <div class="mt-4 p-3 bg-primary/10 border border-primary/20 rounded-lg">
                                        <div class="flex items-center gap-2 text-sm text-primary">
                                            <x-icon name="o-check-circle" class="w-5 h-5" />
                                            <span class="font-medium">Yacht selected:
                                                {{ $availableYatches->firstWhere('id', $yatch_id)?->name }}</span>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <x-alert icon="o-exclamation-triangle" class="alert-warning mt-4">
                                    <div>
                                        <p class="font-semibold">No yachts available</p>
                                        <p class="text-sm mt-1">
                                            @if (!empty($yatch_search))
                                                No yachts match your search criteria or are not available for the
                                                selected
                                                date range and guest count.
                                            @else
                                                No yachts are available for the selected date range and guest count.
                                                Please
                                                choose different dates or adjust guest count.
                                            @endif
                                        </p>
                                    </div>
                                </x-alert>
                            @endif
                        </div>
                    @else
                        <x-alert icon="o-information-circle" class="alert-info">
                            <div>
                                <p class="font-semibold">Select dates first</p>
                                <p class="text-sm mt-1">Please select departure and return dates to see available
                                    yachts.</p>
                            </div>
                        </x-alert>
                    @endif
                </div>
                <div class="divider"></div>

                {{-- Payment Section --}}
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-base-content">Payment Details</h3>
                        <p class="text-sm text-base-content/60 mt-1">Payment information for this booking</p>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input wire:model.live="amount" label="Amount" type="number" step="0.01"
                            min="0" icon="o-currency-dollar"
                            hint="Total charter amount (auto-filled from yacht price)" />
                        <x-select wire:model="payment_method" label="Payment Method" :options="[['id' => 'cash', 'name' => 'Cash'], ['id' => 'card', 'name' => 'Card']]"
                            option-value="id" option-label="name" icon="o-credit-card" />
                        <x-select wire:model="payment_status" label="Payment Status" :options="[['id' => 'paid', 'name' => 'Paid'], ['id' => 'pending', 'name' => 'Pending']]"
                            option-value="id" option-label="name" icon="o-check-circle" />
                    </div>
                </div>

                <div class="divider"></div>

                {{-- Notes Section --}}
                <div class="space-y-4">
                    <div>
                        <h3 class="text-lg font-semibold text-base-content">Additional Notes</h3>
                        <p class="text-sm text-base-content/60 mt-1">Any special requests or additional information</p>
                    </div>
                    <x-textarea wire:model="notes" label="Notes" placeholder="Additional notes (optional)"
                        icon="o-document-text" rows="3" />
                </div>
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" link="{{ route('admin.bookings.yatch.index') }}"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Create Booking" type="submit"
                        class="btn-primary w-full sm:w-auto" spinner="store" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-card>

    {{-- Create Customer Modal --}}
    <x-modal wire:model="createCustomerModal" title="Create New Customer" class="backdrop-blur" max-width="md">
        <x-form wire:submit="createCustomer">
            <div class="space-y-4">
                <x-input wire:model="customer_name" label="Customer Name" placeholder="Enter customer name"
                    icon="o-user" hint="Full name of the customer" />
                <x-input wire:model="customer_email" label="Email" type="email" placeholder="Enter email address"
                    icon="o-envelope" hint="Unique email address" />
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" @click="$wire.createCustomerModal = false"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Create Customer" type="submit"
                        class="btn-primary w-full sm:w-auto" spinner="createCustomer" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
