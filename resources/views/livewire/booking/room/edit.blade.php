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
        $this->booking = $booking->load(['bookingable.house', 'user']);

        // Pre-fill form with existing booking data
        $this->room_id = $booking->bookingable_id;
        $this->check_in = $booking->check_in ? Carbon::parse($booking->check_in)->format('Y-m-d\TH:i') : null;
        $this->check_out = $booking->check_out ? Carbon::parse($booking->check_out)->format('Y-m-d\TH:i') : null;
        $this->adults = $booking->adults ?? 1;
        $this->children = $booking->children ?? 0;
        $this->amount = $booking->price;
        $this->amountManuallySet = true; // Set to true since we're loading existing amount
        $this->payment_method = $booking->payment_method ?? 'cash';
        $this->payment_status = $booking->payment_status ?? 'pending';
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
                $price = $room->discount_price ?? $room->price;
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
                class="btn-ghost" />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="mx-auto">
        <x-form wire:submit="update">
            <div class="space-y-6">
                <div class="grid gap-6 lg:grid-cols-3">
                    <div class="space-y-6 lg:col-span-2">
                        {{-- Date Range Section --}}
                        <div class="rounded-2xl border border-base-300/80 bg-base-100 p-6 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step 1</p>
                                    <h3 class="text-xl font-semibold text-base-content mt-1">Booking Dates</h3>
                                    <p class="text-sm text-base-content/60 mt-1">Select your check-in and check-out
                                        dates
                                    </p>
                                </div>
                                <x-icon name="o-calendar" class="w-8 h-8 text-primary/70" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                                <x-input wire:model.live.debounce.300ms="check_in" label="Check In"
                                    type="datetime-local" icon="o-calendar" :min="$minCheckInDate"
                                    hint="Check-in must be today or later" />
                                <x-input wire:model.live.debounce.300ms="check_out" label="Check Out"
                                    type="datetime-local" icon="o-calendar" :min="$check_in"
                                    hint="Check-out must be after check-in" />
                            </div>
                        </div>

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
                        <div class="rounded-2xl border border-base-300/80 bg-base-100 p-6 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step 2</p>
                                    <h3 class="text-xl font-semibold text-base-content mt-1">Guest Details</h3>
                                    <p class="text-sm text-base-content/60 mt-1">Number of guests for this booking</p>
                                </div>
                                <x-icon name="o-user-group" class="w-8 h-8 text-primary/70" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                                <x-input wire:model.live.debounce.300ms="adults" label="Adults" type="number"
                                    min="1" icon="o-user-group" />
                                <x-input wire:model.live.debounce.300ms="children" label="Children" type="number"
                                    min="0" icon="o-face-smile" />
                            </div>
                        </div>

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
                                        <span class="loading loading-spinner loading-sm"></span>
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
                                            <span class="loading loading-spinner loading-lg text-primary"></span>
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
                                                                            {{ currency_format($room->discount_price ?? ($room->price ?? 0)) }}
                                                                        </div>
                                                                        @if ($room->discount_price && $room->price && $room->discount_price < $room->price)
                                                                            <div
                                                                                class="text-xs text-base-content/50 line-through">
                                                                                {{ currency_format($room->price) }}
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    @if ($room->discount_price && $room->price && $room->discount_price < $room->price)
                                                                        <div
                                                                            class="bg-primary text-primary-content px-2 py-1 rounded-md text-xs font-semibold shadow-md">
                                                                            {{ number_format((($room->price - $room->discount_price) / $room->price) * 100, 0) }}%
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
                        <div class="rounded-2xl border border-base-300/80 bg-base-100 p-6 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step 4</p>
                                    <h3 class="text-xl font-semibold text-base-content mt-1">Payment Details</h3>
                                    <p class="text-sm text-base-content/60 mt-1">Payment information for this booking
                                    </p>
                                </div>
                                <x-icon name="o-credit-card" class="w-8 h-8 text-primary/70" />
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                                <x-input wire:model.live.debounce.500ms="amount" wire:change="$wire.updatedAmount()"
                                    label="Amount" type="number" step="0.01" min="0" max="999999999.99"
                                    icon="o-currency-dollar"
                                    hint="Total booking amount (auto-filled from room price, max: 999,999,999.99)" />
                                <x-select wire:model.live="payment_method" label="Payment Method" :options="[['id' => 'cash', 'name' => 'Cash'], ['id' => 'card', 'name' => 'Card']]"
                                    option-value="id" option-label="name" icon="o-credit-card" />
                                <x-select wire:model.live="payment_status" label="Payment Status" :options="[
                                    ['id' => 'paid', 'name' => 'Paid'],
                                    ['id' => 'pending', 'name' => 'Pending'],
                                ]"
                                    option-value="id" option-label="name" icon="o-check-circle" />
                            </div>
                        </div>

                        {{-- Notes Section --}}
                        <div class="rounded-2xl border border-base-300/80 bg-base-100 p-6 shadow-sm">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step 5</p>
                                    <h3 class="text-xl font-semibold text-base-content mt-1">Additional Notes</h3>
                                    <p class="text-sm text-base-content/60 mt-1">Any special requests or additional
                                        information</p>
                                </div>
                                <x-icon name="o-document-text" class="w-8 h-8 text-primary/70" />
                            </div>
                            <div class="mt-6">
                                <x-textarea wire:model="notes" label="Notes"
                                    placeholder="Additional notes (optional)" icon="o-document-text"
                                    rows="3" />
                            </div>
                        </div>
                    </div>

                    {{-- Summary Column --}}
                    <div class="space-y-6">
                        @php
                            $selectedRoom =
                                $availableRooms->firstWhere('id', $room_id) ?? ($room_id ? Room::find($room_id) : null);
                        @endphp
                        <div
                            class="rounded-2xl border border-base-300/80 bg-gradient-to-br from-base-100 to-base-200/50 p-4 shadow-lg sticky top-24 backdrop-blur-sm">
                            <div class="flex items-center justify-between mb-4 pb-3 border-b border-base-300/60">
                                <div>
                                    <p class="text-xs uppercase tracking-wider text-primary font-bold">Live Summary</p>
                                    <h4 class="text-lg font-bold text-base-content">Booking Overview</h4>
                                </div>
                                <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                                    <x-icon name="o-clipboard-document-check" class="w-5 h-5 text-primary" />
                                </div>
                            </div>

                            <div class="space-y-2.5">
                                {{-- Booking Window --}}
                                <div class="bg-base-100/80 rounded-lg p-2.5 border border-base-300/50">
                                    <div class="flex items-start gap-2">
                                        <div
                                            class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center shrink-0">
                                            <x-icon name="o-calendar" class="w-4 h-4 text-primary" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-base-content/60 mb-1">Booking Window
                                            </p>
                                            @if ($check_in && $check_out)
                                                <div class="space-y-1 flex justify-between">
                                                    <div>
                                                        <p class="text-xs font-semibold text-primary mb-0.5">Check In
                                                        </p>
                                                        <p class="text-xs font-semibold text-base-content">
                                                            {{ \Carbon\Carbon::parse($check_in)->format('M d, Y') }} |
                                                            {{ \Carbon\Carbon::parse($check_in)->format('g:i A') }}
                                                        </p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs font-semibold text-primary mb-0.5">Check Out
                                                        </p>
                                                        <p class="text-xs font-semibold text-base-content">
                                                            {{ \Carbon\Carbon::parse($check_out)->format('M d, Y') }} |
                                                            {{ \Carbon\Carbon::parse($check_out)->format('g:i A') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="text-xs text-base-content/50 italic">Select dates</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                {{-- Guests --}}
                                <div class="bg-base-100/80 rounded-lg p-2.5 border border-base-300/50">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center shrink-0">
                                            <x-icon name="o-user-group" class="w-4 h-4 text-primary" />
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-base-content/60 mb-0.5">Guests</p>
                                            <p class="text-sm font-bold text-base-content">
                                                {{ $adults + $children }}
                                                <span class="text-xs font-normal text-base-content/70">
                                                    ({{ $adults }}A{{ $children > 0 ? ', ' . $children . 'C' : '' }})
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Selected Room --}}
                                <div class="bg-base-100/80 rounded-lg p-2.5 border border-base-300/50">
                                    <div class="flex items-start gap-2">
                                        <div
                                            class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center shrink-0">
                                            <x-icon name="o-home-modern" class="w-4 h-4 text-primary" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-base-content/60 mb-0.5">Selected Room
                                            </p>
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

                                {{-- Amount --}}
                                <div class="bg-gradient-to-br from-primary/10 to-primary/5 rounded-lg p-2.5 border-2 border-primary/20"
                                    wire:key="summary-amount-{{ $amount }}">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-7 h-7 rounded-md bg-primary/20 flex items-center justify-center shrink-0">
                                            <x-icon name="o-currency-dollar" class="w-4 h-4 text-primary" />
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-primary/80 mb-0.5">Total Amount</p>
                                            <p class="text-lg font-bold text-primary">
                                                {{ $amount ? currency_format($amount) : '—' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Payment Details --}}
                                <div class="bg-base-100/80 rounded-lg p-2.5 border border-base-300/50">
                                    <div class="flex items-start gap-2">
                                        <div
                                            class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center shrink-0">
                                            <x-icon name="o-credit-card" class="w-4 h-4 text-primary" />
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-xs font-semibold text-base-content/60 mb-1.5">Payment</p>
                                            <div class="space-y-1">
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs text-base-content/70">Method</span>
                                                    <span
                                                        class="text-xs font-semibold text-base-content capitalize">{{ $payment_method }}</span>
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="text-xs text-base-content/70">Status</span>
                                                    <span
                                                        class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $payment_status === 'paid' ? 'bg-success/20 text-success border border-success/30' : 'bg-warning/20 text-warning border border-warning/30' }}">
                                                        {{ ucfirst($payment_status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:justify-between">
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3">
                        <x-button icon="o-arrow-left" label="Back"
                            link="{{ route('admin.bookings.house.show', $booking->id) }}"
                            class="btn-ghost w-full sm:w-auto" responsive />
                    </div>
                    <x-button icon="o-check" label="Update Booking" type="submit"
                        class="btn-primary w-full sm:w-auto" spinner="update" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
