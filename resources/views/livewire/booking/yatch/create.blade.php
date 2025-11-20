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
    public int $adults = 1;
    public int $children = 0;
    public ?float $amount = null;
    public string $payment_method = 'cash';
    public string $payment_status = 'pending';
    public ?string $notes = null;

    public function mount(): void
    {
        $this->check_in = Carbon::now()->format('Y-m-d\TH:i');
        $this->check_out = Carbon::tomorrow()->format('Y-m-d\TH:i');
    }

    public function updatedCheckIn(): void
    {
        $this->yatch_id = null;

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
                $this->amount = $yatch->discount_price ?? ($yatch->price ?? 0);
            }
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

        $availableYatches = Yatch::available($checkIn, $checkOut)->where('id', $this->yatch_id)->exists();

        if (!$availableYatches) {
            $this->error('Selected yacht is not available for the chosen dates.');
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

        $view->availableYatches = $checkIn && $checkOut ? Yatch::available($checkIn, $checkOut)->orderBy('name')->get() : collect();

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

    <x-card shadow class="max-w-4xl mx-auto">
        <x-form wire:submit="store">
            <div class="space-y-6">
                {{-- Date Range Section --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Charter Dates</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-input wire:model="check_in" label="Departure" type="datetime-local" icon="o-calendar"
                            :min="$minDepartureDate" hint="Departure must be today or later" />
                        <x-input wire:model="check_out" label="Return" type="datetime-local" icon="o-calendar"
                            :min="$check_in" hint="Return must be after departure" />
                    </div>
                </div>

                <div class="divider my-4"></div>

                {{-- Yacht Selection Section --}}
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold">Yacht Selection</h3>
                    @if ($check_in && $check_out && Carbon::parse($check_in)->lt(Carbon::parse($check_out)))
                        @if ($availableYatches->count() > 0)
                            <x-select wire:model="yatch_id" label="Select Yacht" placeholder="Choose an available yacht"
                                :options="$availableYatches" option-value="id" option-label="name" icon="o-home-modern"
                                hint="Only available yachts are shown">
                                @scope('option', $yatch)
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <div class="font-semibold">{{ $yatch->name }}</div>
                                            <div class="text-xs text-base-content/50">SKU: {{ $yatch->sku ?? 'N/A' }}
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="font-semibold">
                                                {{ currency_format($yatch->discount_price ?? ($yatch->price ?? 0)) }}
                                            </div>
                                            <div class="text-xs text-base-content/50">
                                                Max guests: {{ $yatch->max_guests ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                @endscope
                            </x-select>
                        @else
                            <x-alert icon="o-exclamation-triangle" class="alert-warning">
                                No yachts available for the selected date range. Please choose different dates.
                            </x-alert>
                        @endif
                    @else
                        <x-alert icon="o-information-circle" class="alert-info">
                            Please select departure and return dates to see available yachts.
                        </x-alert>
                    @endif
                </div>

                <div class="divider my-4"></div>

                {{-- Customer Section --}}
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Customer Details</h3>
                        <x-button type="button" icon="o-plus" label="New Customer"
                            @click="$wire.createCustomerModal = true" class="btn-sm" />
                    </div>

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
                            icon="o-currency-dollar" hint="Total charter amount" />
                        <x-select wire:model="payment_method" label="Payment Method" :options="[['id' => 'cash', 'name' => 'Cash'], ['id' => 'card', 'name' => 'Card']]" option-value="id"
                            option-label="name" icon="o-credit-card" />
                        <x-select wire:model="payment_status" label="Payment Status" :options="[['id' => 'paid', 'name' => 'Paid'], ['id' => 'pending', 'name' => 'Pending']]" option-value="id"
                            option-label="name" icon="o-check-circle" />
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
