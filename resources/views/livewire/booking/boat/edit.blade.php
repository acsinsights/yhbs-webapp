<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use App\Models\{Booking, Boat};
use App\Enums\{BookingStatusEnum, PaymentMethodEnum, PaymentStatusEnum};

new class extends Component {
    use Toast;

    public Booking $booking;
    public string $check_in = '';
    public string $check_out = '';
    public int $adults = 1;
    public int $children = 0;
    public ?float $amount = null;
    public string $payment_method = 'cash';
    public string $payment_status = 'pending';
    public string $status = 'pending';
    public ?string $notes = null;

    public function mount(Booking $booking): void
    {
        if ($booking->bookingable_type !== Boat::class) {
            $this->error('Invalid booking type.', redirectTo: route('admin.bookings.boat.index'));
            return;
        }

        $this->booking = $booking->load(['bookingable', 'user']);
        $this->check_in = $booking->check_in->format('Y-m-d');
        $this->check_out = $booking->check_out?->format('Y-m-d') ?? '';
        $this->adults = $booking->adults;
        $this->children = $booking->children;
        $this->amount = $booking->total_amount ?? $booking->price;
        $this->payment_method = $booking->payment_method->value;
        $this->payment_status = $booking->payment_status->value;
        $this->status = $booking->status->value;
        $this->notes = $booking->notes;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'adults' => 'required|integer|min:1',
            'children' => 'required|integer|min:0',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,online,other',
            'payment_status' => 'required|in:pending,paid',
            'status' => 'required|in:pending,booked,cancelled',
        ]);

        $this->booking->update([
            'adults' => $validated['adults'],
            'children' => $validated['children'],
            'price' => $validated['amount'],
            'total_amount' => $validated['amount'],
            'payment_method' => $validated['payment_method'],
            'payment_status' => $validated['payment_status'],
            'status' => $validated['status'],
            'notes' => $this->notes,
        ]);

        $this->success('Booking updated successfully.', redirectTo: route('admin.bookings.boat.show', $this->booking->id));
    }

    public function with(): array
    {
        return [
            'breadcrumbs' => [['label' => 'Dashboard', 'url' => route('admin.index')], ['label' => 'Boat Bookings', 'link' => route('admin.bookings.boat.index')], ['label' => 'Booking #' . $this->booking->id, 'link' => route('admin.bookings.boat.show', $this->booking->id)], ['label' => 'Edit']],
            'paymentMethods' => [['id' => 'cash', 'name' => 'Cash'], ['id' => 'card', 'name' => 'Credit/Debit Card'], ['id' => 'online', 'name' => 'Online Payment'], ['id' => 'other', 'name' => 'Other']],
            'paymentStatuses' => [['id' => 'pending', 'name' => 'Pending'], ['id' => 'paid', 'name' => 'Paid']],
            'bookingStatuses' => [['id' => 'pending', 'name' => 'Pending'], ['id' => 'booked', 'name' => 'Booked'], ['id' => 'cancelled', 'name' => 'Cancelled']],
        ];
    }
}; ?>

<div>
    <x-header title="Edit Boat Booking #{{ $booking->id }}" separator>
        <x-slot:middle>
            <x-breadcrumbs :items="$breadcrumbs" class="text-sm text-gray-500" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.boat.show', $booking->id) }}"
                class="btn-outline" responsive />
        </x-slot:actions>
    </x-header>

    <form wire:submit="save">
        <div class="grid gap-5 lg:grid-cols-3">
            {{-- Main Form --}}
            <div class="lg:col-span-2 space-y-5">
                <x-card class="shadow-lg bg-base-200/30">
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-archive-box" class="w-5 h-5 text-primary" />
                            <span>Boat Information</span>
                        </div>
                    </x-slot:title>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="flex items-center gap-3">
                            <x-icon name="o-archive-box" class="w-8 h-8 text-primary" />
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Boat Name</div>
                                <div class="font-semibold text-lg">{{ $booking->bookingable->name }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-icon name="o-tag" class="w-8 h-8 text-info" />
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Service Type</div>
                                <x-badge :value="$booking->bookingable->service_type_label" class="badge-primary badge-lg" />
                            </div>
                        </div>
                    </div>
                </x-card>

                <x-card class="shadow-lg bg-base-200/30">
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-user-circle" class="w-5 h-5 text-success" />
                            <span>Customer Information</span>
                        </div>
                    </x-slot:title>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="flex items-center gap-3">
                            <x-icon name="o-user-circle" class="w-8 h-8 text-success" />
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Name</div>
                                <div class="font-semibold text-lg">{{ $booking->user->name }}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-icon name="o-envelope" class="w-8 h-8 text-warning" />
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Email</div>
                                <div class="text-sm">{{ $booking->user->email }}</div>
                            </div>
                        </div>
                    </div>
                </x-card>

                <x-card class="shadow-lg">
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-pencil-square" class="w-5 h-5 text-primary" />
                            <span>Edit Booking Details</span>
                        </div>
                    </x-slot:title>
                    <div class="grid gap-5">
                        <x-card class="bg-base-200/30">
                            <x-slot:title>
                                <div class="flex items-center gap-2">
                                    <x-icon name="o-calendar" class="w-5 h-5" />
                                    <span>Date & Time</span>
                                </div>
                            </x-slot:title>
                            <div class="grid md:grid-cols-2 gap-4">
                                <x-input label="Check-in Date *" icon="o-calendar" type="date"
                                    wire:model="check_in" />
                                <x-input label="Check-out Date *" icon="o-calendar" type="date"
                                    wire:model="check_out" />
                            </div>
                        </x-card>

                        <x-card class="bg-base-200/30">
                            <x-slot:title>
                                <div class="flex items-center gap-2">
                                    <x-icon name="o-users" class="w-5 h-5" />
                                    <span>Guests</span>
                                </div>
                            </x-slot:title>
                            <div class="grid md:grid-cols-2 gap-4">
                                <x-input label="Adults *" icon="o-user" type="number" wire:model="adults"
                                    min="1" />
                                <x-input label="Children" icon="o-user" type="number" wire:model="children"
                                    min="0" />
                            </div>
                        </x-card>

                        <x-input label="Total Amount (KD) *" icon="o-currency-dollar" type="number" step="0.01"
                            wire:model="amount" prefix="KD" />

                        <x-textarea label="Special Notes" icon="o-document-text" wire:model="notes" rows="3"
                            placeholder="Add any special notes or requirements..." />
                    </div>
                </x-card>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">
                <x-card class="shadow-lg bg-base-200/30">
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-flag" class="w-5 h-5 text-info" />
                            <span>Booking Status</span>
                        </div>
                    </x-slot:title>
                    <x-choices-offline label="Update Status *" icon="o-flag" :options="$bookingStatuses" wire:model="status"
                        hint="Current booking status" searchable single />
                </x-card>

                <x-card class="shadow-lg bg-base-200/30">
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-credit-card" class="w-5 h-5 text-success" />
                            <span>Payment Information</span>
                        </div>
                    </x-slot:title>
                    <div class="space-y-4">
                        <x-select label="Payment Method *" icon="o-credit-card" :options="$paymentMethods" option-value="id"
                            option-label="name" wire:model="payment_method" />

                        <x-select label="Payment Status *" icon="o-banknotes" :options="$paymentStatuses" option-value="id"
                            option-label="name" wire:model="payment_status" />
                    </div>
                </x-card>

                <x-card class="shadow-lg bg-base-200/30">
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-cog" class="w-5 h-5" />
                            <span>Actions</span>
                        </div>
                    </x-slot:title>
                    <div class="space-y-2">
                        <x-button label="Save Changes" type="submit" icon="o-check-circle"
                            class="btn-primary w-full btn-lg" spinner="save" />
                        <x-button label="Cancel" link="{{ route('admin.bookings.boat.show', $booking->id) }}"
                            icon="o-arrow-left" class="btn-ghost w-full" />
                    </div>
                </x-card>

                <x-card class="shadow-lg bg-base-200/30">
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-clock" class="w-5 h-5 text-info" />
                            <span>Last Updated</span>
                        </div>
                    </x-slot:title>
                    <div class="text-sm text-base-content/70">
                        <p class="mb-2">{{ $booking->updated_at->diffForHumans() }}</p>
                        <p class="text-xs">{{ $booking->updated_at->format('d M Y, H:i') }}</p>
                    </div>
                </x-card>
            </div>
        </div>
    </form>
</div>
