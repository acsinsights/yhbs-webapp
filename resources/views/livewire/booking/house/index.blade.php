<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use App\Models\{Booking, Room, House};

new class extends Component {
    use Toast, WithPagination;
    #[Title('House Bookings')]
    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public int $perPage = 10;

    public function checkin($id): void
    {
        $booking = Booking::where('bookingable_type', House::class)->findOrFail($id);
        $booking->update(['status' => 'checked_in']);

        $this->success('Booking checked in successfully.');
    }

    public function checkout($id): void
    {
        $booking = Booking::where('bookingable_type', House::class)->findOrFail($id);
        $booking->update(['status' => 'checked_out']);

        $this->success('Booking checked out successfully.');
    }

    public function rendering(View $view)
    {
        $view->bookings = Booking::query()
            ->where('bookingable_type', House::class)
            ->with(['bookingable', 'user'])
            ->when($this->search, function ($query) {
                return $query->where(function ($q) {
                    // Search by booking ID
                    $q->where('booking_id', 'like', "%{$this->search}%")
                        // Search in user name and email
                        ->orWhereHas('user', function ($userQuery) {
                            $userQuery->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%");
                        })
                        // Search in house name
                        ->orWhereHasMorph('bookingable', [House::class], function ($houseQuery) {
                            $houseQuery->where('name', 'like', "%{$this->search}%")->orWhere('house_number', 'like', "%{$this->search}%");
                        })
                        // Search in guest details (guest names)
                        ->orWhereRaw("JSON_SEARCH(guest_details, 'one', ?) IS NOT NULL", ["%{$this->search}%"]);
                });
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);

        $view->headers = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'user.name', 'label' => 'Customer', 'sortable' => false, 'class' => 'whitespace-nowrap'], ['key' => 'guest_name', 'label' => 'Guest Name', 'sortable' => false, 'class' => 'whitespace-nowrap'], ['key' => 'house_name', 'label' => 'House', 'sortable' => false, 'class' => 'whitespace-nowrap'], ['key' => 'check_in', 'label' => 'Check In', 'sortable' => true, 'class' => 'whitespace-nowrap'], ['key' => 'check_out', 'label' => 'Check Out', 'sortable' => true, 'class' => 'whitespace-nowrap'], ['key' => 'price', 'label' => 'Amount', 'sortable' => true, 'class' => 'whitespace-nowrap'], ['key' => 'payment_status', 'label' => 'Payment Status', 'class' => 'whitespace-nowrap'], ['key' => 'payment_method', 'label' => 'Payment Method', 'class' => 'whitespace-nowrap'], ['key' => 'status', 'label' => 'Status', 'class' => 'whitespace-nowrap']];
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
                'label' => 'House Bookings',
                'icon' => 'o-building-office',
            ],
        ];
    @endphp

    <x-header title="House Bookings" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Manage all house bookings</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live.debounce="search" clearable />
            <x-button icon="o-plus" class="btn-primary" label="New Booking"
                link="{{ route('admin.bookings.house.create') }}" />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-table :headers="$headers" :rows="$bookings" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[10, 25, 50, 100]">
            @scope('cell_user.name', $booking)
                <div class="font-semibold">{{ $booking->user->name ?? 'N/A' }}</div>
            @endscope

            @scope('cell_guest_name', $booking)
                @php
                    $guestName = 'N/A';
                    if ($booking->guest_details && is_array($booking->guest_details)) {
                        // Try to get first guest name from 'guests' array
                        if (
                            isset($booking->guest_details['guests']) &&
                            is_array($booking->guest_details['guests']) &&
                            count($booking->guest_details['guests']) > 0
                        ) {
                            $firstGuest = $booking->guest_details['guests'][0];
                            $guestName = $firstGuest['name'] ?? 'N/A';
                        }
                        // Fallback to customer name
                        if ($guestName === 'N/A' && isset($booking->guest_details['customer']['first_name'])) {
                            $guestName =
                                $booking->guest_details['customer']['first_name'] .
                                ' ' .
                                ($booking->guest_details['customer']['last_name'] ?? '');
                        }
                        // Legacy fallback to adult_names
                        if (
                            $guestName === 'N/A' &&
                            isset($booking->guest_details['adult_names']) &&
                            is_array($booking->guest_details['adult_names'])
                        ) {
                            $guestName = $booking->guest_details['adult_names'][0] ?? 'N/A';
                        }
                    }
                @endphp
                <div class="text-sm">{{ $guestName }}</div>
            @endscope

            @scope('cell_house_name', $booking)
                <div class="flex gap-2 items-center">
                    <x-button tooltip="View House Details"
                        link="{{ route('admin.houses.show', $booking->bookingable->id) }}" class="btn-ghost btn-sm">
                        <x-icon name="o-building-office-2" class="w-4 h-4" />
                        <span class="font-semibold">{{ $booking->bookingable->name }}</span>
                    </x-button>
                </div>
            @endscope

            @scope('cell_check_in', $booking)
                @if ($booking->check_in)
                    <div class="flex flex-col">
                        <span>{{ $booking->check_in->format('M d, Y') }}</span>
                    </div>
                @else
                    <span class="text-base-content/50">—</span>
                @endif
            @endscope

            @scope('cell_check_out', $booking)
                @if ($booking->check_out)
                    <div class="flex flex-col">
                        <span>{{ $booking->check_out->format('M d, Y') }}</span>
                    </div>
                @else
                    <span class="text-base-content/50">—</span>
                @endif
            @endscope

            @scope('cell_adults', $booking)
                <x-badge :value="$booking->adults ?? 0" class="badge-soft badge-primary" />
            @endscope

            @scope('cell_children', $booking)
                <x-badge :value="$booking->children ?? 0" class="badge-soft badge-secondary" />
            @endscope

            @scope('cell_price', $booking)
                <div class="font-semibold">
                    {{ currency_format($booking->price ?? 0) }}
                </div>
            @endscope

            @scope('cell_payment_status', $booking)
                <x-badge :value="$booking->payment_status->label()" class="{{ $booking->payment_status->badgeColor() }}" />
            @endscope

            @scope('cell_payment_method', $booking)
                <x-badge :value="$booking->payment_method->label()" class="{{ $booking->payment_method->badgeColor() }}" />
            @endscope

            @scope('cell_status', $booking)
                <x-badge :value="$booking->status->label()" class="{{ $booking->status->badgeColor() }}" />
            @endscope

            @scope('actions', $booking)
                <div class="flex items-center gap-2 justify-end">
                    <x-dropdown label="Custom Scroll" scroll max-height="max-h-64">
                        <x-slot:trigger>
                            <x-button icon="o-bars-arrow-down" class="btn-circle" />
                        </x-slot:trigger>

                        @if ($booking->canCheckIn() && (now()->isSameDay($booking->check_in) || now()->isAfter($booking->check_in)))
                            <x-menu-item icon="o-pencil" title="Edit Booking" class="btn-ghost btn-sm"
                                link="{{ route('admin.bookings.house.edit', $booking->id) }}" />
                            <x-menu-item icon="o-arrow-right-end-on-rectangle" title="Check In"
                                wire:click="checkin({{ $booking->id }})" spinner class="text-info"
                                wire:confirm="Are you sure you want to check in this booking?" />
                        @elseif ($booking->canCheckOut())
                            <x-menu-item icon="o-arrow-right-start-on-rectangle" title="Check Out"
                                wire:click="checkout({{ $booking->id }})" class="btn-ghost btn-sm text-success"
                                wire:confirm="Are you sure you want to checkout this booking?" spinner />
                        @elseif ($booking->canBeEdited())
                            <x-menu-item icon="o-pencil" title="Edit Booking"
                                link="{{ route('admin.bookings.house.edit', $booking->id) }}" class="btn-ghost btn-sm" />
                        @endif
                        <x-menu-item icon="o-eye" link="{{ route('admin.bookings.house.show', $booking->id) }}"
                            class="btn-ghost btn-sm" title="View Details" />
                    </x-dropdown>
                </div>
            @endscope

            <x-slot:empty>
                <x-empty icon="o-building-office" message="No bookings found" />
            </x-slot:empty>
        </x-table>
    </x-card>
</div>
