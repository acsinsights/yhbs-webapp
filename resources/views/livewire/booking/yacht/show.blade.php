<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use Illuminate\View\View;
use Livewire\Volt\Component;
use App\Models\{Booking, Yacht};

new class extends Component {
    use Toast;

    public Booking $booking;
    public bool $showPaymentModal = false;
    public string $payment_status = '';
    public string $payment_method = '';
    public bool $showCancelModal = false;
    public string $cancellation_reason = '';

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['bookingable', 'user']);
        $this->payment_status = $booking->payment_status;
        $this->payment_method = $booking->payment_method;
    }

    public function checkout(): void
    {
        $this->booking->update([
            'status' => 'checked_out',
        ]);

        $this->success('Booking checked out successfully.', redirectTo: route('admin.bookings.yacht.index'));
    }

    public function updatePayment(): void
    {
        $this->validate([
            'payment_status' => 'required|in:pending,paid,failed',
            'payment_method' => 'required|in:cash,card,online',
        ]);

        $this->booking->update([
            'payment_status' => $this->payment_status,
            'payment_method' => $this->payment_method,
        ]);

        $this->showPaymentModal = false;
        $this->success('Payment details updated successfully.');
    }

    public function cancelBooking(): void
    {
        $this->validate([
            'cancellation_reason' => 'required|min:10',
        ]);

        $this->booking->update([
            'status' => 'cancelled',
            'notes' => ($this->booking->notes ? $this->booking->notes . "\n\n" : '') . 'Cancellation Reason: ' . $this->cancellation_reason,
        ]);

        $this->showCancelModal = false;
        $this->success('Booking cancelled successfully.', redirectTo: route('admin.bookings.yacht.index'));
    }

    public function rendering(View $view)
    {
        $view->booking = $this->booking;
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
                'label' => 'Booking Details',
            ],
        ];
    @endphp

    <x-header title="Booking Details" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">View booking information</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.yacht.index') }}"
                class="btn-ghost" />
            @if ($booking->status !== 'checked_out' && $booking->status !== 'cancelled')
                <x-button icon="o-pencil" label="Edit" link="{{ route('admin.bookings.yacht.edit', $booking->id) }}"
                    class="btn-primary" />
                <x-button icon="o-x-circle" label="Cancel Booking" wire:click="$set('showCancelModal', true)"
                    class="btn-error" />
                <x-button icon="o-check-circle" label="Checkout" wire:click="checkout"
                    wire:confirm="Are you sure you want to checkout this booking?" class="btn-success"
                    spinner="checkout" />
            @endif
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Booking Information --}}
            <x-card shadow>
                <x-slot:title>
                    <div class="flex items-center gap-2">
                        <x-icon name="o-document-text" class="w-5 h-5" />
                        <span>Booking Information</span>
                    </div>
                </x-slot:title>
                <x-slot:menu>
                    <x-badge :value="ucfirst(str_replace('_', ' ', $booking->status))"
                        class="badge-soft {{ match ($booking->status) {
                            'pending' => 'badge-warning',
                            'booked' => 'badge-primary',
                            'checked_in' => 'badge-info',
                            'cancelled' => 'badge-error',
                            'checked_out' => 'badge-success',
                            default => 'badge-ghost',
                        } }}" />
                </x-slot:menu>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Booking ID</div>
                            <div class="font-semibold">#{{ $booking->id }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Booking Date</div>
                            <div class="font-semibold">{{ $booking->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                    </div>

                    @if ($booking->check_in)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Departure</div>
                                <div class="font-semibold">
                                    {{ Carbon::parse($booking->check_in)->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-base-content/50">
                                    {{ Carbon::parse($booking->check_in)->format('h:i A') }}
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Return</div>
                                <div class="font-semibold">
                                    {{ Carbon::parse($booking->check_out)->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-base-content/50">
                                    {{ Carbon::parse($booking->check_out)->format('h:i A') }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if ($booking->check_in && $booking->check_out)
                        @php
                            $checkIn = Carbon::parse($booking->check_in);
                            $checkOut = Carbon::parse($booking->check_out);
                            $days = round($checkIn->diffInDays($checkOut));
                            $durationText = $days . ' ' . ($days === 1 ? 'night' : 'nights');
                        @endphp
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Duration</div>
                            <div class="font-semibold">{{ $durationText }}</div>
                        </div>
                    @endif

                    @if ($booking->notes)
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Notes</div>
                            <div class="text-sm">{{ strip_tags($booking->notes) }}</div>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- Yacht Information --}}
            @if ($booking->bookingable)
                <x-card shadow>
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-sparkles" class="w-5 h-5" />
                            <span>Yacht Information</span>
                        </div>
                    </x-slot:title>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Yacht Name</div>
                                <div class="font-semibold text-lg">{{ $booking->bookingable->name }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">SKU</div>
                                <div class="font-mono">{{ $booking->bookingable->sku ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <div class="text-xs text-base-content/50 mb-1 uppercase tracking-wide">Length</div>
                                <div class="font-semibold">{{ $booking->bookingable->length ?? 'N/A' }} m</div>
                            </div>
                            <div>
                                <div class="text-xs text-base-content/50 mb-1 uppercase tracking-wide">Guests</div>
                                <div class="font-semibold">{{ $booking->bookingable->max_guests ?? 'N/A' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-base-content/50 mb-1 uppercase tracking-wide">Crew</div>
                                <div class="font-semibold">{{ $booking->bookingable->max_crew ?? 'N/A' }}</div>
                            </div>
                        </div>

                        @if ($booking->bookingable->description)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Description</div>
                                <div class="text-sm">{{ strip_tags($booking->bookingable->description) }}</div>
                            </div>
                        @endif
                    </div>
                </x-card>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Customer Information --}}
            <x-card shadow>
                <x-slot:title>
                    <div class="flex items-center gap-2">
                        <x-icon name="o-user" class="w-5 h-5" />
                        <span>Customer</span>
                    </div>
                </x-slot:title>

                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Name</div>
                        <div class="font-semibold">{{ $booking->user->name ?? 'N/A' }}</div>
                    </div>

                    @if ($booking->user)
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Email</div>
                            <div class="text-sm">{{ $booking->user->email }}</div>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Adults</div>
                            <x-badge :value="$booking->adults ?? 0" class="badge-soft badge-primary" />
                        </div>

                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Children</div>
                            <x-badge :value="$booking->children ?? 0" class="badge-soft badge-secondary" />
                        </div>
                    </div>
                </div>
            </x-card>

            {{-- Payment Information --}}
            <x-card shadow>
                <x-slot:title>
                    <div class="flex items-center gap-2">
                        <x-icon name="o-currency-dollar" class="w-5 h-5" />
                        <span>Payment</span>
                    </div>
                </x-slot:title>
                <x-slot:menu>
                    @if ($booking->status !== 'checked_out' && $booking->status !== 'cancelled')
                        <x-button icon="o-pencil" label="Update" wire:click="$set('showPaymentModal', true)"
                            class="btn-ghost btn-sm" />
                    @endif
                </x-slot:menu>

                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Amount</div>
                        <div class="font-semibold text-2xl">{{ currency_format($booking->price ?? 0) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Payment Method</div>
                        <x-badge :value="ucfirst($booking->payment_method)" class="badge-soft badge-info" />
                    </div>
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Payment Status</div>
                        <x-badge :value="ucfirst($booking->payment_status)"
                            class="badge-soft {{ match ($booking->payment_status) {
                                'paid' => 'badge-success',
                                'pending' => 'badge-warning',
                                'failed' => 'badge-error',
                                default => 'badge-ghost',
                            } }}" />
                    </div>
                </div>
            </x-card>
        </div>
    </div>

    {{-- Payment Update Modal --}}
    <x-modal wire:model="showPaymentModal" title="Update Payment Details" class="backdrop-blur">
        <div class="space-y-4">
            <x-select label="Payment Status" wire:model="payment_status" :options="[
                ['id' => 'pending', 'name' => 'Pending'],
                ['id' => 'paid', 'name' => 'Paid'],
                ['id' => 'failed', 'name' => 'Failed'],
            ]" icon="o-credit-card" />

            <x-select label="Payment Method" wire:model="payment_method" :options="[
                ['id' => 'cash', 'name' => 'Cash'],
                ['id' => 'card', 'name' => 'Card'],
                ['id' => 'online', 'name' => 'Online'],
            ]" icon="o-banknotes" />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showPaymentModal = false" />
            <x-button label="Update" wire:click="updatePayment" class="btn-primary" spinner="updatePayment" />
        </x-slot:actions>
    </x-modal>

    {{-- Cancel Booking Modal --}}
    <x-modal wire:model="showCancelModal" title="Cancel Booking" class="backdrop-blur">
        <div class="space-y-4">
            <x-alert title="Warning!"
                description="This action cannot be undone. Please provide a reason for cancellation."
                icon="o-exclamation-triangle" class="alert-warning" />

            <x-textarea label="Cancellation Reason" wire:model="cancellation_reason"
                placeholder="Please provide a detailed reason for cancellation..." rows="4"
                hint="Minimum 10 characters required" />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showCancelModal = false" />
            <x-button label="Confirm Cancellation" wire:click="cancelBooking" class="btn-error"
                spinner="cancelBooking" />
        </x-slot:actions>
    </x-modal>
</div>
