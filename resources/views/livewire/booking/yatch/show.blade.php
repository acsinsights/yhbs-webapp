<?php

use App\Models\Booking;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Illuminate\View\View;
use Carbon\Carbon;

new class extends Component {
    use Toast;

    public Booking $booking;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['bookingable', 'user']);
    }

    public function checkout(): void
    {
        $this->booking->update([
            'status' => 'checked_out',
        ]);

        $this->success('Booking checked out successfully.', redirectTo: route('admin.bookings.yatch.index'));
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
                'link' => route('admin.bookings.yatch.index'),
                'label' => 'Yacht Bookings',
            ],
            [
                'label' => 'Booking Details',
            ],
        ];
    @endphp

    <x-header title="Yacht Booking Details" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">View yacht charter booking information</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.yatch.index') }}"
                class="btn-ghost" />
            @if ($booking->status !== 'checked_out' && $booking->status !== 'cancelled')
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
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Duration</div>
                            <div class="font-semibold">
                                {{ Carbon::parse($booking->check_in)->diffInHours(Carbon::parse($booking->check_out)) }}
                                hrs
                            </div>
                        </div>
                    @endif

                    @if ($booking->notes)
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Notes</div>
                            <div class="text-sm">{{ $booking->notes }}</div>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- Yacht Information --}}
            @if ($booking->bookingable)
                <x-card shadow>
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-ship" class="w-5 h-5" />
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
                                <div class="text-sm">{{ $booking->bookingable->description }}</div>
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

                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Amount</div>
                        <div class="font-semibold text-2xl">KD {{ number_format($booking->price ?? 0, 2) }}</div>
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
</div>
