<?php

use App\Models\Booking;
use App\Models\Room;
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
    public bool $createNewCustomer = false;

    public ?int $room_id = null;
    public ?string $check_in = null;
    public ?string $check_out = null;
    public int $adults = 1;
    public int $children = 0;
    public ?float $amount = null;
    public string $payment_method = 'cash';
    public string $payment_status = 'pending';
    public ?string $notes = null;

    public function mount(): void
    {
        // Set default dates
        $this->check_in = Carbon::today()->format('Y-m-d\TH:i');
        $this->check_out = Carbon::tomorrow()->format('Y-m-d\TH:i');
    }

    public function updatedCheckIn(): void
    {
        // Reset room selection when dates change
        $this->room_id = null;
    }

    public function updatedCheckOut(): void
    {
        // Reset room selection when dates change
        $this->room_id = null;
    }

    public function updatedRoomId(): void
    {
        // Auto-fill amount from room price when room is selected
        if ($this->room_id) {
            $room = Room::find($this->room_id);
            if ($room) {
                $this->amount = $room->discount_price ?? ($room->price ?? 0);
            }
        }
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

        $this->user_id = $user->id;
        $this->createNewCustomer = false;
        $this->success('Customer created successfully.');
    }

    public function store(): void
    {
        $this->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card',
            'payment_status' => 'required|in:paid,pending',
        ]);

        // Check if room is available for the selected dates
        $checkIn = Carbon::parse($this->check_in);
        $checkOut = Carbon::parse($this->check_out);

        $availableRooms = Room::available($checkIn, $checkOut)->where('id', $this->room_id)->exists();

        if (!$availableRooms) {
            $this->error('Selected room is not available for the chosen dates.');
            return;
        }

        $room = Room::findOrFail($this->room_id);

        $booking = Booking::create([
            'bookingable_type' => Room::class,
            'bookingable_id' => $this->room_id,
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

        $this->success('Booking created successfully.', redirectTo: route('admin.bookings.hotel.show', $booking->id));
    }

    public function rendering(View $view)
    {
        $checkIn = $this->check_in ? Carbon::parse($this->check_in) : null;
        $checkOut = $this->check_out ? Carbon::parse($this->check_out) : null;

        $view->availableRooms = $checkIn && $checkOut ? Room::active()->available($checkIn, $checkOut)->with('hotel')->orderBy('room_number')->get() : collect();

        $view->customers = User::role(RolesEnum::CUSTOMER->value)->orderBy('name')->get();
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
                'link' => route('admin.bookings.hotel.index'),
                'label' => 'Hotel Bookings',
            ],
            [
                'label' => 'Create Booking',
            ],
        ];
    @endphp

    <x-header title="Create Hotel Booking" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Create a new hotel room booking</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.hotel.index') }}"
                class="btn-ghost" />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="max-w-4xl mx-auto">
        <x-form wire:submit="store">
            <div class="space-y-6">
                {{-- Date Range Section --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Date Range</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input wire:model="check_in" label="Check In" type="datetime-local" icon="o-calendar"
                            hint="Select check-in date and time" />
                        <x-input wire:model="check_out" label="Check Out" type="datetime-local" icon="o-calendar"
                            hint="Select check-out date and time" />
                    </div>
                </div>

                <div class="divider my-4"></div>

                {{-- Room Selection Section --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Room Selection</h3>
                    @if ($check_in && $check_out && Carbon::parse($check_in)->lt(Carbon::parse($check_out)))
                        @if ($availableRooms->count() > 0)
                            <x-select wire:model="room_id" label="Select Room" placeholder="Choose an available room"
                                :options="$availableRooms" option-value="id" option-label="room_number" icon="o-home-modern"
                                hint="Only available rooms are shown">
                                @scope('option', $room)
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="font-semibold">{{ $room->room_number }}</div>
                                            <div class="text-xs text-base-content/50">{{ $room->hotel->name ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-semibold">
                                                {{ currency_format($room->discount_price ?? ($room->price ?? 0)) }}</div>
                                            <div class="text-xs text-base-content/50">{{ $room->adults ?? 0 }} adults,
                                                {{ $room->children ?? 0 }} children</div>
                                        </div>
                                    </div>
                                @endscope
                            </x-select>
                        @else
                            <x-alert icon="o-exclamation-triangle" class="alert-warning">
                                No rooms available for the selected date range. Please choose different dates.
                            </x-alert>
                        @endif
                    @else
                        <x-alert icon="o-information-circle" class="alert-info">
                            Please select check-in and check-out dates to see available rooms.
                        </x-alert>
                    @endif
                </div>

                <div class="divider my-4"></div>

                {{-- Customer Section --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Customer Details</h3>
                        <x-button type="button" icon="{{ $createNewCustomer ? 'o-x-mark' : 'o-plus' }}"
                            :label="$createNewCustomer ? 'Cancel' : 'New Customer'" @click="$wire.createNewCustomer = !$wire.createNewCustomer"
                            class="btn-sm" />
                    </div>

                    @if ($createNewCustomer)
                        <x-card class="bg-base-200/50">
                            <div class="space-y-4">
                                <x-input wire:model="customer_name" label="Customer Name"
                                    placeholder="Enter customer name" icon="o-user" />
                                <x-input wire:model="customer_email" label="Email" type="email"
                                    placeholder="Enter email address" icon="o-envelope" />
                                <x-button type="button" icon="o-check" label="Create Customer"
                                    wire:click="createCustomer" class="btn-primary" spinner="createCustomer" />
                            </div>
                        </x-card>
                    @else
                        <x-select wire:model="user_id" label="Select Customer" placeholder="Choose a customer"
                            :options="$customers" option-value="id" option-label="name" icon="o-user"
                            hint="Select existing customer or create a new one">
                            @scope('option', $customer)
                                <div>
                                    <div class="font-semibold">{{ $customer->name }}</div>
                                    <div class="text-xs text-base-content/50">{{ $customer->email }}</div>
                                </div>
                            @endscope
                        </x-select>
                    @endif
                </div>

                <div class="divider my-4"></div>

                {{-- Guest Details Section --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Guest Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input wire:model="adults" label="Adults" type="number" min="1" icon="o-user-group" />
                        <x-input wire:model="children" label="Children" type="number" min="0"
                            icon="o-face-smile" />
                    </div>
                </div>

                <div class="divider my-4"></div>

                {{-- Payment Section --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Payment Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input wire:model="amount" label="Amount" type="number" step="0.01" min="0"
                            icon="o-currency-dollar" hint="Booking amount" />
                        <x-select wire:model="payment_method" label="Payment Method" :options="[['id' => 'cash', 'name' => 'Cash'], ['id' => 'card', 'name' => 'Card']]"
                            option-value="id" option-label="name" icon="o-credit-card" />
                        <x-select wire:model="payment_status" label="Payment Status" :options="[['id' => 'paid', 'name' => 'Paid'], ['id' => 'pending', 'name' => 'Pending']]"
                            option-value="id" option-label="name" icon="o-check-circle" />
                    </div>
                </div>

                <div class="divider my-4"></div>

                {{-- Notes Section --}}
                <div class="space-y-4">
                    <x-textarea wire:model="notes" label="Notes" placeholder="Additional notes (optional)"
                        icon="o-document-text" rows="3" />
                </div>
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" link="{{ route('admin.bookings.hotel.index') }}"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Create Booking" type="submit"
                        class="btn-primary w-full sm:w-auto" spinner="store" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
