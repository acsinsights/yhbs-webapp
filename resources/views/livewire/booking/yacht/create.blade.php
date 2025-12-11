<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Hash;
use App\Models\{Booking, User, Yacht};
use App\Enums\{BookingStatusEnum, RolesEnum};
use App\Notifications\WelcomeCustomerNotification;

new class extends Component {
    use Toast, WithPagination;

    public ?int $user_id = null;
    public string $customer_name = '';
    public string $customer_email = '';
    public bool $createCustomerModal = false;

    public ?int $yacht_id = null;
    public ?string $check_in = null;
    public ?string $check_out = null;
    public ?int $adults = 1;
    public ?int $children = 0;
    public ?float $amount = null;
    public bool $amountManuallySet = false;
    public string $payment_method = 'cash';
    public string $payment_status = 'pending';
    public ?string $notes = null;
    public string $yacht_search = '';
    public int $perPage = 6;

    public function mount(): void
    {
        $this->check_in = Carbon::now()->format('Y-m-d\TH:i');
        $this->check_out = Carbon::tomorrow()->format('Y-m-d\TH:i');
    }

    public function updatedAdults(): void
    {
        $this->resetPage();
        $this->yacht_id = null;
        if (!$this->amountManuallySet) {
            $this->amount = null;
        }
    }

    public function updatedChildren(): void
    {
        $this->resetPage();
        $this->yacht_id = null;
        if (!$this->amountManuallySet) {
            $this->amount = null;
        }
    }

    public function updatedCheckIn(): void
    {
        $this->resetPage();
        $this->yacht_id = null;
        if (!$this->amountManuallySet) {
            $this->amount = null;
        }

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
        $this->resetPage();
        $this->yacht_id = null;
        if (!$this->amountManuallySet) {
            $this->amount = null;
        }

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

    public function updatedYachtSearch(): void
    {
        $this->resetPage();
    }

    public function updatedYachtId(): void
    {
        // When yacht changes, reset the manual flag so new price can auto-fill
        $this->amountManuallySet = false;

        if ($this->yacht_id) {
            $yacht = Yacht::find($this->yacht_id);
            if ($yacht) {
                $price = $yacht->discount_price ?? $yacht->price;
                $newAmount = $price !== null ? (float) $price : null;
                $this->amount = $newAmount;
                // Force update to ensure Live Summary refreshes
                $this->dispatch('amount-updated');
            } else {
                $this->amount = null;
                $this->dispatch('amount-updated');
            }
        } else {
            // Reset amount when yacht selection is cleared
            $this->amount = null;
            $this->dispatch('amount-updated');
        }
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
        }
    }

    public function resetForm(): void
    {
        $this->user_id = null;
        $this->yacht_id = null;
        $this->check_in = Carbon::now()->format('Y-m-d\TH:i');
        $this->check_out = Carbon::tomorrow()->format('Y-m-d\TH:i');
        $this->adults = 1;
        $this->children = 0;
        $this->amount = null;
        $this->amountManuallySet = false;
        $this->payment_method = 'cash';
        $this->payment_status = 'pending';
        $this->notes = null;
        $this->yacht_search = '';
        $this->resetPage();
        $this->success('Form has been reset.');
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
                'yacht_id' => 'required|exists:yachts,id',
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

        $checkIn = Carbon::parse($this->check_in);
        $checkOut = Carbon::parse($this->check_out);
        $totalGuests = $this->adults + $this->children;

        $yacht = Yacht::find($this->yacht_id);

        if (!$yacht) {
            $this->error('Selected yacht not found.');
            return;
        }

        // Check if yacht is available for the date range
        $isAvailable = Yacht::available($checkIn, $checkOut)->where('id', $this->yacht_id)->exists();
        if (!$isAvailable) {
            $this->error('Selected yacht is not available for the chosen dates.');
            return;
        }

        // Check guest capacity
        if ($yacht->max_guests && $totalGuests > $yacht->max_guests) {
            $this->error("Selected yacht can accommodate maximum {$yacht->max_guests} guests, but you have selected {$totalGuests} guests.");
            return;
        }

        $booking = Booking::create([
            'bookingable_type' => Yacht::class,
            'bookingable_id' => $this->yacht_id,
            'user_id' => $this->user_id,
            'adults' => $this->adults,
            'children' => $this->children,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'price' => $this->amount,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'status' => BookingStatusEnum::BOOKED->value,
            'notes' => $this->notes,
        ]);

        $this->success('Booking created successfully.', redirectTo: route('admin.bookings.yacht.show', $booking->id));
    }

    public function rendering(View $view)
    {
        $checkIn = $this->check_in ? Carbon::parse($this->check_in) : null;
        $checkOut = $this->check_out ? Carbon::parse($this->check_out) : null;

        $totalGuests = $this->adults + $this->children;

        // Only query if we have valid dates
        if ($checkIn && $checkOut && $checkIn->lt($checkOut)) {
            $query = Yacht::available($checkIn, $checkOut);

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

            $view->availableYachtes = $query->orderBy('name')->paginate($this->perPage);
        } else {
            $view->availableYachtes = collect();
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
                'link' => route('admin.bookings.yacht.index'),
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
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.yacht.index') }}"
                class="btn-ghost" />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="mx-auto">
        <x-form wire:submit="store">
            <div class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="space-y-6 lg:col-span-2">
                        {{-- Date Range Section --}}
                        <x-booking.date-range-section stepNumber="1" checkInLabel="Departure" checkOutLabel="Return"
                            checkInHint="Departure must be today or later" checkOutHint="Return must be after departure"
                            :minCheckInDate="$minDepartureDate" />

                        {{-- Customer Section --}}
                        <x-booking.customer-section stepNumber="2" :customers="$customers" />

                        @php
                            $selectedYacht =
                                $availableYachtes->firstWhere('id', $yacht_id) ??
                                ($yacht_id ? Yacht::find($yacht_id) : null);
                            $maxAdults = $selectedYacht?->max_guests ?? 20;
                            $maxChildren = $selectedYacht?->max_guests ?? 20;
                        @endphp

                        {{-- Guest Details Section --}}
                        <x-booking.guest-section stepNumber="3" :maxAdults="$maxAdults" :maxChildren="$maxChildren" />

                        {{-- Yacht Selection Section --}}
                        <x-card class="bg-base-200">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step 4</p>
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
                                    {{-- Loading Indicator --}}
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
                                                @endphp
                                                <label wire:click="$wire.yacht_id = {{ $yacht->id }}"
                                                    class="relative cursor-pointer group block">
                                                    <input type="radio" wire:model.live="yacht_id"
                                                        value="{{ $yacht->id }}" class="sr-only">
                                                    <div
                                                        class="bg-base-100 border-2 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 h-full flex flex-col {{ $isSelected ? 'border-primary ring-2 ring-primary/20 shadow-lg' : 'border-base-300' }}">
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

                                                            {{-- Discount Badge --}}
                                                            @if ($yacht->discount_price && $yacht->price && $yacht->discount_price < $yacht->price)
                                                                <div class="absolute top-3 left-3">
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
                        <x-booking.payment-section stepNumber="5" />

                        {{-- Notes Section --}}
                        <x-booking.notes-section stepNumber="6" />
                    </div>

                    {{-- Summary Column --}}
                    @php
                        $checkInDate = $check_in ? \Carbon\Carbon::parse($check_in) : null;
                        $checkOutDate = $check_out ? \Carbon\Carbon::parse($check_out) : null;
                    @endphp

                    <div class="sticky top-24">
                        <x-booking.booking-summary :adults="$adults" :children="$children" :checkInDate="$checkInDate"
                            :checkOutDate="$checkOutDate" checkInLabel="Departure" checkOutLabel="Return"
                            windowLabel="Charter Window" :amount="$amount" :paymentMethod="$payment_method" :paymentStatus="$payment_status">
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
                        </x-booking.booking-summary>
                    </div>

                    <div class="space-y-6">
                        <x-card class="bg-base-200 p-4 sticky top-[450px] backdrop-blur-sm">
                            <div class="p-4 rounded-xl bg-base-200/80 border border-dashed border-base-300">
                                <p class="text-xs uppercase tracking-wide text-base-content/60">Checklist</p>
                                <ul class="mt-2 space-y-2 text-sm">
                                    <li class="flex items-center gap-2" wire:key="checklist-customer">
                                        <span
                                            class="w-2.5 h-2.5 rounded-full transition-colors duration-200 {{ $user_id ? 'bg-success' : 'bg-base-400' }}"></span>
                                        <span
                                            class="{{ $user_id ? 'text-success font-medium' : 'text-base-content/70' }}">Customer
                                            selected</span>
                                    </li>
                                    <li class="flex items-center gap-2" wire:key="checklist-yacht">
                                        <span
                                            class="w-2.5 h-2.5 rounded-full transition-colors duration-200 {{ $yacht_id ? 'bg-success' : 'bg-base-400' }}"></span>
                                        <span
                                            class="{{ $yacht_id ? 'text-success font-medium' : 'text-base-content/70' }}">Yacht
                                            selected</span>
                                    </li>
                                    <li class="flex items-center gap-2" wire:key="checklist-amount">
                                        <span
                                            class="w-2.5 h-2.5 rounded-full transition-colors duration-200 {{ $amount ? 'bg-success' : 'bg-base-400' }}"></span>
                                        <span
                                            class="{{ $amount ? 'text-success font-medium' : 'text-base-content/70' }}">Amount
                                            filled</span>
                                    </li>
                                    <li class="flex items-center gap-2" wire:key="checklist-payment-method">
                                        <span
                                            class="w-2.5 h-2.5 rounded-full transition-colors duration-200 {{ $payment_method ? 'bg-success' : 'bg-base-400' }}"></span>
                                        <span
                                            class="{{ $payment_method ? 'text-success font-medium' : 'text-base-content/70' }}">Payment
                                            method selected</span>
                                    </li>
                                    <li class="flex items-center gap-2" wire:key="checklist-payment-status">
                                        <span
                                            class="w-2.5 h-2.5 rounded-full transition-colors duration-200 {{ $payment_status ? 'bg-success' : 'bg-base-400' }}"></span>
                                        <span
                                            class="{{ $payment_status ? 'text-success font-medium' : 'text-base-content/70' }}">Payment
                                            status selected</span>
                                    </li>
                                </ul>
                            </div>
                            <div class="rounded-2xl mt-6 border border-dashed border-base-300 bg-base-50/50 p-5">
                                <p class="text-sm font-semibold text-base-content">Need inspiration?</p>
                                <p class="text-sm text-base-content/60 mt-1">Use the notes section to capture special
                                    requests, catering preferences, or transfer details so the crew is prepared.</p>
                            </div>
                        </x-card>

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
