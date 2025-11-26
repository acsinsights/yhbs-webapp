<?php

use App\Models\Booking;
use App\Models\Room;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Illuminate\View\View;
use Carbon\Carbon;

new class extends Component {
    use Toast;

    public Booking $booking;

    public function mount(Booking $booking): void
    {
        $this->booking = $booking->load(['bookingable.hotel', 'user']);
    }

    public function checkout(): void
    {
        $this->booking->update([
            'status' => 'checked_out',
        ]);

        $this->success('Booking checked out successfully.', redirectTo: route('admin.bookings.hotel.index'));
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
                'link' => route('admin.bookings.hotel.index'),
                'label' => 'Hotel Bookings',
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
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.bookings.hotel.index') }}"
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
                                <div class="text-sm text-base-content/50 mb-1">Check In</div>
                                <div class="font-semibold">
                                    {{ Carbon::parse($booking->check_in)->format('M d, Y') }}
                                </div>
                                <div class="text-xs text-base-content/50">
                                    {{ Carbon::parse($booking->check_in)->format('h:i A') }}
                                </div>
                            </div>
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Check Out</div>
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
                            $hours = round($checkIn->diffInHours($checkOut)) % 24;

                            $durationText = '';
                            if ($days > 0) {
                                $durationText = $days . ' ' . ($days === 1 ? 'night' : 'nights');
                            }
                            if ($hours > 0) {
                                if ($durationText) {
                                    $durationText .= ', ';
                                }
                                $durationText .= $hours . ' ' . ($hours === 1 ? 'hr' : 'hrs');
                            }
                            if (!$durationText) {
                                $durationText = 'Less than 1 day';
                            }
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

            {{-- Room Information --}}
            @if ($booking->bookingable)
                <x-card shadow>
                    <x-slot:title>
                        <div class="flex items-center gap-2">
                            <x-icon name="o-home-modern" class="w-5 h-5" />
                            <span>Room Information</span>
                        </div>
                    </x-slot:title>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Room Number</div>
                                <div class="font-semibold text-lg">{{ $booking->bookingable->room_number }}</div>
                            </div>
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Room Name</div>
                                <div class="font-semibold">{{ $booking->bookingable->name }}</div>
                            </div>
                        </div>
                        <div>
                            <div class="text-sm text-base-content/50 mb-1">Hotel</div>
                            <div class="font-semibold">{{ $booking->bookingable->hotel->name ?? 'N/A' }}</div>
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
</div>
