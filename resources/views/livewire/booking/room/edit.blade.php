<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use Illuminate\View\View;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use App\Enums\RolesEnum;
use App\Models\{Booking, Room, User};

new class extends Component {
    use Toast, WithPagination;

    public Booking $booking;

    public ?int $room_id = null;
    public ?string $check_in = null;
    public ?string $check_out = null;
    public int $adults = 1;
    public int $children = 0;
    public ?float $amount = null;
    public bool $amountManuallySet = false;
    public string $payment_method = 'cash';
    public string $payment_status = 'pending';
    public ?string $notes = null;
    public string $room_search = '';
    public int $perPage = 6;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['bookingable', 'user']);

        // Check if booking can be edited
        if (!$booking->canBeEdited()) {
            $this->warning('This booking cannot be edited.', redirectTo: route('admin.bookings.room.index'));
            return;
        }

        // Pre-fill form with existing booking data
        $this->room_id = $booking->bookingable_id;
        $this->check_in = $booking->check_in ? $booking->check_in->format('Y-m-d\TH:i') : null;
        $this->check_out = $booking->check_out ? $booking->check_out->format('Y-m-d\TH:i') : null;
        $this->adults = $booking->adults ?? 1;
        $this->children = $booking->children ?? 0;
        $this->amount = $booking->price;
        $this->amountManuallySet = true; // Set to true since we're loading existing amount
        $this->payment_method = $booking->payment_method->value ?? 'cash';
        $this->payment_status = $booking->payment_status->value ?? 'pending';
        $this->notes = $booking->notes;
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
    }

    public function updatedRoomSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRoomId(): void
    {
        // When room changes, reset the manual flag so new price can auto-fill
        $this->amountManuallySet = false;

        if ($this->room_id) {
            $room = Room::find($this->room_id);
            if ($room) {
                $price = $room->discount_price ?? $room->price_per_night;
                $newAmount = $price !== null ? (float) $price : null;
                $this->amount = $newAmount;
                $this->dispatch('amount-updated');
            } else {
                $this->amount = null;
                $this->dispatch('amount-updated');
            }
        } else {
            $this->amount = null;
            $this->dispatch('amount-updated');
        }
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
        }
    }

    public function update(): void
    {
        $this->validate(
            [
                'room_id' => 'required|exists:rooms,id',
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

        $room = Room::find($this->room_id);

        if (!$room) {
            $this->error('Selected room not found.');
            return;
        }

        // Check if room is available for the date range (excluding current booking)
        // If it's the same room, we allow the update
        if ($this->room_id != $this->booking->bookingable_id) {
            // Check if the new room is available (excluding current booking)
            $hasConflict = Booking::where('bookingable_type', Room::class)
                ->where('bookingable_id', $this->room_id)
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
                $this->error('Selected room is not available for the chosen dates.');
                return;
            }
        }

        $this->booking->update([
            'bookingable_id' => $this->room_id,
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

        // Get current room to include it even if not available for new dates
        $currentRoom = $this->booking->bookingable;

        if ($checkIn && $checkOut && $checkIn->lt($checkOut)) {
            // Get available rooms excluding current booking
            $query = Room::active()
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
                ->with('house');

            // Filter by search term
            if (!empty($this->room_search)) {
                $search = $this->room_search;
                $query->where(function ($q) use ($search) {
                    $q->where('room_number', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhereHas('house', function ($houseQuery) use ($search) {
                            $houseQuery->where('name', 'like', "%{$search}%");
                        });
                });
            }

            $availableRooms = $query->orderBy('room_number')->get();

            // Include current room if it's not in the available list
            if ($currentRoom && !$availableRooms->contains('id', $currentRoom->id)) {
                $availableRooms->prepend($currentRoom);
            }

            // Manually paginate the collection
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $items = $availableRooms->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
            $view->availableRooms = new \Illuminate\Pagination\LengthAwarePaginator($items, $availableRooms->count(), $this->perPage, $currentPage, ['path' => request()->url(), 'query' => request()->query()]);
        } else {
            // If dates are invalid, still show current room
            $collection = $currentRoom ? collect([$currentRoom]) : collect();
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $items = $collection->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();
            $view->availableRooms = new \Illuminate\Pagination\LengthAwarePaginator($items, $collection->count(), $this->perPage, $currentPage, ['path' => request()->url(), 'query' => request()->query()]);
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
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.house.show', $booking->id) }}"
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
                        <div class="rounded-2xl border border-base-300/80 bg-base-100 p-6 shadow-sm opacity-75">
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
                        </div>

                        {{-- Guest Details Section --}}
                        <x-booking.guest-section stepNumber="2" />

                        {{-- Room Selection Section --}}
                        <div class="rounded-2xl border border-base-300/80 bg-base-100 p-6 shadow-sm">
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

                                    <div wire:loading.remove wire:target="check_in,check_out,room_search,perPage"
                                        class="flex items-center gap-2">
                                        <x-icon name="o-funnel" class="w-4 h-4" />
                                        <span>
                                            <strong>{{ $availableRooms->total() }}</strong>
                                            {{ $availableRooms->total() === 1 ? 'room' : 'rooms' }} available
                                            @if ($availableRooms->total() > $availableRooms->count())
                                                (Showing
                                                {{ $availableRooms->firstItem() }}-{{ $availableRooms->lastItem() }} of
                                                {{ $availableRooms->total() }})
                                            @endif
                                        </span>
                                    </div>
                                    @if (!empty($room_search))
                                        <div wire:loading.remove wire:target="check_in,check_out,room_search,perPage"
                                            class="flex items-center gap-2">
                                            <x-icon name="o-magnifying-glass" class="w-4 h-4" />
                                            <span>Search: "{{ $room_search }}"</span>
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

                                <div wire:loading.remove wire:target="check_in,check_out,room_search,perPage">
                                    @if ($availableRooms->count() > 0)
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mt-4">
                                            @foreach ($availableRooms as $room)
                                                @php
                                                    $isSelected = $room_id == $room->id;
                                                    $isCurrentRoom = $room->id == $booking->bookingable_id;
                                                @endphp
                                                <label wire:click="$wire.room_id = {{ $room->id }}"
                                                    class="relative cursor-pointer group block">
                                                    <input type="radio" wire:model.live="room_id"
                                                        value="{{ $room->id }}" class="sr-only">
                                                    <div
                                                        class="bg-base-100 border-2 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 h-full flex flex-col {{ $isSelected ? 'border-primary ring-2 ring-primary/20 shadow-lg' : 'border-base-300' }} {{ $isCurrentRoom ? 'ring-2 ring-info/30' : '' }}">
                                                        {{-- Header Section --}}
                                                        <div class="p-4 bg-base-200/50 border-b border-base-300">
                                                            <div class="flex items-start justify-between gap-2">
                                                                <div class="flex-1 min-w-0">
                                                                    <h4
                                                                        class="font-bold text-lg text-base-content mb-1 line-clamp-1">
                                                                        {{ $room->room_number }}
                                                                    </h4>
                                                                    @if ($room->name)
                                                                        <p
                                                                            class="text-xs text-base-content/60 line-clamp-1">
                                                                            {{ $room->name }}</p>
                                                                    @endif
                                                                    @if ($room->house)
                                                                        <p
                                                                            class="text-xs text-primary font-medium mt-1">
                                                                            {{ $room->house->name }}</p>
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
                                                            {{-- Current Room Badge --}}
                                                            @if ($isCurrentRoom)
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
                                                                @if ($room->adults || $room->children)
                                                                    <div
                                                                        class="flex items-center gap-2 text-sm text-base-content/70">
                                                                        <x-icon name="o-user-group"
                                                                            class="w-4 h-4 text-base-content/50" />
                                                                        <span>Max
                                                                            {{ ($room->adults ?? 0) + ($room->children ?? 0) }}
                                                                            guests</span>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            {{-- Price Section --}}
                                                            <div class="pt-3 border-t border-base-300">
                                                                <div class="flex items-baseline justify-between gap-2">
                                                                    <div class="flex-1">
                                                                        <div class="font-bold text-lg text-primary">
                                                                            {{ currency_format($room->discount_price ?? ($room->price_per_night ?? 0)) }}
                                                                        </div>
                                                                        @if ($room->discount_price && $room->price_per_night && $room->discount_price < $room->price_per_night)
                                                                            <div
                                                                                class="text-xs text-base-content/50 line-through">
                                                                                {{ currency_format($room->price_per_night) }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    @if ($room->discount_price && $room->price_per_night && $room->discount_price < $room->price_per_night)
                                                                        <div
                                                                            class="bg-primary text-primary-content px-2 py-1 rounded-md text-xs font-semibold shadow-md">
                                                                            {{ number_format((($room->price_per_night - $room->discount_price) / $room->price_per_night) * 100, 0) }}%
                                                                            OFF
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>

                                        {{-- Pagination --}}
                                        @if ($availableRooms->hasPages())
                                            <div
                                                class="mt-6 flex items-center justify-between border-t border-base-300 pt-4">
                                                <div class="text-sm text-base-content/70">
                                                    Showing {{ $availableRooms->firstItem() }} to
                                                    {{ $availableRooms->lastItem() }} of
                                                    {{ $availableRooms->total() }} results
                                                </div>
                                                <div class="flex items-center gap-2">
                                                    {{ $availableRooms->links() }}
                                                </div>
                                            </div>
                                        @endif

                                        @if ($room_id)
                                            <div class="mt-4 p-3 bg-primary/10 border border-primary/20 rounded-lg">
                                                <div class="flex items-center gap-2 text-sm text-primary">
                                                    <x-icon name="o-check-circle" class="w-5 h-5" />
                                                    <span class="font-medium">Room selected:
                                                        {{ $availableRooms->firstWhere('id', $room_id)?->room_number ?? Room::find($room_id)?->room_number }}</span>
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
                        </div>

                        {{-- Payment Section --}}
                        <x-booking.payment-section stepNumber="4" />

                        {{-- Notes Section --}}
                        <x-booking.notes-section stepNumber="5" />
                    </div>

                    {{-- Summary Column --}}
                    @php
                        $selectedRoom =
                            $availableRooms->firstWhere('id', $room_id) ?? ($room_id ? Room::find($room_id) : null);
                        $checkInDate = $check_in ? \Carbon\Carbon::parse($check_in) : null;
                        $checkOutDate = $check_out ? \Carbon\Carbon::parse($check_out) : null;
                    @endphp

                    <x-booking.booking-summary :adults="$adults" :children="$children" :checkInDate="$checkInDate" :checkOutDate="$checkOutDate"
                        :amount="$amount" :paymentMethod="$payment_method" :paymentStatus="$payment_status">
                        <x-slot:selection>
                            {{-- Selected Room --}}
                            <div class="bg-base-100/80 rounded-lg p-2.5 border border-base-300/50">
                                <div class="flex items-start gap-2">
                                    <div
                                        class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center shrink-0">
                                        <x-icon name="o-home-modern" class="w-4 h-4 text-primary" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-base-content/60 mb-0.5">Selected Room</p>
                                        @if ($selectedRoom)
                                            <p class="text-xs font-bold text-base-content line-clamp-1">
                                                {{ $selectedRoom->room_number }}</p>
                                            @if ($selectedRoom->house)
                                                <p class="text-xs text-base-content/60">
                                                    {{ $selectedRoom->house->name }}
                                                </p>
                                            @endif
                                        @else
                                            <p class="text-xs text-base-content/50 italic">No room selected</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </x-slot:selection>
                    </x-booking.booking-summary>
                </div>
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:justify-end">
                    <x-button icon="o-check" label="Update" type="submit" class="btn-primary w-full sm:w-auto"
                        spinner="update" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
