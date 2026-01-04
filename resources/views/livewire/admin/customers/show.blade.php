<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Locked;
use App\Models\{User, Booking};
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    #[Locked]
    public User $customer;

    public function mount($id): void
    {
        $this->customer = User::role('customer')
            ->with([
                'bookings' => function ($query) {
                    $query->latest();
                },
            ])
            ->withCount(['bookings'])
            ->findOrFail($id);
    }

    public function getCustomerStats()
    {
        return [
            'total_bookings' => $this->customer->bookings_count,
            'pending_bookings' => $this->customer->bookings()->where('status', 'pending')->count(),
            'completed_bookings' => $this->customer->bookings()->where('status', 'checked_out')->count(),
            'cancelled_bookings' => $this->customer->bookings()->where('status', 'cancelled')->count(),
            'total_spent' => $this->customer->bookings()->where('payment_status', 'paid')->sum(\DB::raw('CASE WHEN discount_price IS NOT NULL AND discount_price < price THEN discount_price ELSE price END')),
            'last_booking' => $this->customer->bookings()->latest()->first(),
        ];
    }

    public function with(): array
    {
        return [
            'stats' => $this->getCustomerStats(),
            'bookings' => $this->customer->bookings,
        ];
    }
}; ?>

<div>
    {{-- Header Section --}}
    <x-header title="Customer Details" separator>
        <x-slot:actions>
            <x-button label="Back to Customers" icon="o-arrow-left" link="{{ route('admin.customers.index') }}" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Customer Profile Card --}}
        <div class="lg:col-span-1">
            <x-card title="Profile Information">
                <div class="flex flex-col items-center gap-4">
                    @if ($customer->avatar)
                        <div class="avatar">
                            <div class="w-32 rounded-full ring ring-primary ring-offset-base-100 ring-offset-2">
                                <img src="{{ asset('storage/' . $customer->avatar) }}" alt="{{ $customer->name }}" />
                            </div>
                        </div>
                    @else
                        <div class="avatar placeholder">
                            <div
                                class="bg-primary text-primary-content rounded-full w-32 h-32 flex items-center justify-center">
                                <span class="text-5xl font-bold">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="text-center">
                        <h3 class="text-2xl font-bold">{{ $customer->name }}</h3>
                        @if ($customer->email_verified_at)
                            <x-badge value="Email Verified" class="badge-success mt-2" />
                        @else
                            <x-badge value="Email Not Verified" class="badge-warning mt-2" />
                        @endif
                    </div>

                    <div class="w-full space-y-3 mt-4">
                        <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                            <x-icon name="o-envelope" class="w-5 h-5 text-primary" />
                            <div class="flex-1">
                                <div class="text-xs text-gray-500">Email</div>
                                <div class="font-medium">{{ $customer->email }}</div>
                            </div>
                        </div>

                        @if ($customer->phone)
                            <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                <x-icon name="o-phone" class="w-5 h-5 text-primary" />
                                <div class="flex-1">
                                    <div class="text-xs text-gray-500">Phone</div>
                                    <div class="font-medium">{{ $customer->phone }}</div>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                            <x-icon name="o-calendar" class="w-5 h-5 text-primary" />
                            <div class="flex-1">
                                <div class="text-xs text-gray-500">Member Since</div>
                                <div class="font-medium">{{ $customer->created_at->format('d M, Y') }}</div>
                            </div>
                        </div>

                        @if ($stats['last_booking'])
                            <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                                <x-icon name="o-clock" class="w-5 h-5 text-primary" />
                                <div class="flex-1">
                                    <div class="text-xs text-gray-500">Last Booking</div>
                                    <div class="font-medium">{{ $stats['last_booking']->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Stats and Bookings --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Stats Cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <x-stat title="Total Bookings" value="{{ $stats['total_bookings'] }}" icon="o-calendar-days"
                    color="text-primary" />

                <x-stat title="Pending" value="{{ $stats['pending_bookings'] }}" icon="o-clock" color="text-warning" />

                <x-stat title="Completed" value="{{ $stats['completed_bookings'] }}" icon="o-check-circle"
                    color="text-success" />

                <x-stat title="Cancelled" value="{{ $stats['cancelled_bookings'] }}" icon="o-x-circle"
                    color="text-error" />
            </div>

            {{-- Revenue Card --}}
            <x-card>
                <div
                    class="flex items-center justify-between p-4 bg-gradient-to-r from-primary/10 to-primary/5 rounded-lg">
                    <div>
                        <div class="text-sm text-gray-600">Total Revenue Generated</div>
                        <div class="text-3xl font-bold text-primary">
                            KD {{ number_format($stats['total_spent'], 2) }}
                        </div>
                    </div>
                    <x-icon name="o-currency-dollar" class="w-16 h-16 text-primary/30" />
                </div>
            </x-card>

            {{-- Bookings History --}}
            <x-card title="Booking History">
                @if ($bookings->count() > 0)
                    <div class="space-y-3">
                        @foreach ($bookings as $booking)
                            <div class="p-4 border border-base-300 rounded-lg hover:bg-base-200 transition">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="font-semibold text-lg">#{{ $booking->booking_number }}</span>
                                            <x-badge :value="ucfirst($booking->status->value)"
                                                class="{{ match ($booking->status->value) {
                                                    'pending' => 'badge-warning',
                                                    'booked' => 'badge-info',
                                                    'checked_in' => 'badge-primary',
                                                    'checked_out' => 'badge-success',
                                                    'cancelled' => 'badge-error',
                                                    default => 'badge-ghost',
                                                } }}" />

                                            <x-badge :value="ucfirst($booking->payment_status->value)"
                                                class="{{ $booking->payment_status->value === 'paid' ? 'badge-success' : 'badge-warning' }}" />
                                        </div>

                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-500">Type:</span>
                                                <span
                                                    class="font-medium ml-2">{{ class_basename($booking->bookingable_type) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Check-in:</span>
                                                <span
                                                    class="font-medium ml-2">{{ $booking->check_in->format('d M, Y') }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Amount:</span>
                                                <span class="font-medium ml-2">KD
                                                    {{ number_format($booking->discount_price ?? $booking->price, 2) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Booked on:</span>
                                                <span
                                                    class="font-medium ml-2">{{ $booking->created_at->format('d M, Y') }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex gap-2">
                                        @if ($booking->bookingable_type === 'App\Models\Room')
                                            <x-button icon="o-eye"
                                                link="{{ route('admin.bookings.room.show', $booking->id) }}"
                                                class="btn-sm btn-ghost" tooltip="View Booking" />
                                        @elseif($booking->bookingable_type === 'App\Models\House')
                                            <x-button icon="o-eye"
                                                link="{{ route('admin.bookings.house.show', $booking->id) }}"
                                                class="btn-sm btn-ghost" tooltip="View Booking" />
                                        @elseif($booking->bookingable_type === 'App\Models\Boat')
                                            <x-button icon="o-eye"
                                                link="{{ route('admin.bookings.boat.show', $booking->id) }}"
                                                class="btn-sm btn-ghost" tooltip="View Booking" />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <x-alert icon="o-information-circle" class="alert-info">
                        No bookings found for this customer yet.
                    </x-alert>
                @endif
            </x-card>
        </div>
    </div>
</div>
