<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\{Booking, Boat, User};
use App\Enums\BookingStatusEnum;

new class extends Component {
    use Toast, WithPagination;

    public string $search = '';
    public string $status_filter = '';
    public string $payment_status_filter = '';
    public ?int $boat_id = null;

    public function mount(): void
    {
        $this->boat_id = request('boat_id');
    }

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public int $perPage = 10;

    public function rendering($view): void
    {
        $query = Booking::with(['user', 'bookingable'])->where('bookingable_type', Boat::class);

        // Filter by boat
        if ($this->boat_id) {
            $query->where('bookingable_id', $this->boat_id);
        }

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('user', function ($userQuery) {
                    $userQuery->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%");
                })
                    ->orWhereHasMorph('bookingable', [Boat::class], function ($boatQuery) {
                        $boatQuery->where('name', 'like', "%{$this->search}%");
                    })
                    ->orWhere('id', 'like', "%{$this->search}%");
            });
        }

        // Filter by status
        if ($this->status_filter) {
            $query->where('status', $this->status_filter);
        }

        // Filter by payment status
        if ($this->payment_status_filter) {
            $query->where('payment_status', $this->payment_status_filter);
        }

        $view->bookings = $query->orderBy(...array_values($this->sortBy))->paginate($this->perPage);
        $view->boats = Boat::active()->orderBy('name')->get();

        $view->headers = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'user.name', 'label' => 'Customer', 'sortable' => false, 'class' => 'whitespace-nowrap'], ['key' => 'boat_name', 'label' => 'Boat', 'sortable' => false, 'class' => 'w-64'], ['key' => 'check_in', 'label' => 'Check In', 'sortable' => true, 'class' => 'whitespace-nowrap'], ['key' => 'price', 'label' => 'Amount', 'sortable' => true, 'class' => 'whitespace-nowrap'], ['key' => 'payment_status', 'label' => 'Payment Status', 'class' => 'whitespace-nowrap'], ['key' => 'payment_method', 'label' => 'Payment Method', 'class' => 'whitespace-nowrap'], ['key' => 'status', 'label' => 'Status', 'class' => 'whitespace-nowrap']];
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
                'label' => 'Boat Bookings',
                'icon' => 'o-archive-box',
            ],
        ];
    @endphp

    <x-header title="Boat Bookings" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Manage all boat service bookings</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live.debounce="search" clearable />
            <x-button icon="o-plus" class="btn-primary" label="New Booking"
                link="{{ route('admin.bookings.boat.create') }}" />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-table :headers="$headers" :rows="$bookings" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[10, 25, 50, 100]">
            @scope('cell_user.name', $booking)
                <div class="font-semibold">{{ $booking->user->name ?? 'N/A' }}</div>
            @endscope

            @scope('cell_boat_name', $booking)
                @if ($booking->bookingable)
                    <div class="flex gap-2 items-center">
                        <x-badge value="{{ $booking->bookingable->name }}" class="badge-primary" />
                        <span class="text-xs text-base-content/50">{{ $booking->bookingable->service_type_label }}</span>
                    </div>
                @else
                    <span class="text-base-content/50">—</span>
                @endif
            @endscope

            @scope('cell_check_in', $booking)
                @if ($booking->check_in)
                    <div class="flex flex-col">
                        <span>{{ $booking->check_in->format('M d, Y') }}</span>
                        <span class="text-xs text-base-content/50">{{ $booking->check_in->format('H:i') }}</span>
                    </div>
                @else
                    <span class="text-base-content/50">—</span>
                @endif
            @endscope

            @scope('cell_price', $booking)
                <div class="font-semibold">
                    KD {{ number_format($booking->price ?? 0, 2) }}
                </div>
            @endscope

            @scope('cell_payment_status', $booking)
                <div class="text-center">
                    <x-badge :value="$booking->payment_status->label()" class="{{ $booking->payment_status->badgeColor() }}" />
                </div>
            @endscope

            @scope('cell_payment_method', $booking)
                <div class="text-center">
                    <x-badge :value="$booking->payment_method->label()" class="{{ $booking->payment_method->badgeColor() }}" />
                </div>
            @endscope

            @scope('cell_status', $booking)
                <x-badge :value="$booking->status->label()" class="{{ $booking->status->badgeColor() }}" />
            @endscope

            @scope('actions', $booking)
                <div class="flex items-center gap-2 justify-end">
                    <x-dropdown>
                        <x-slot:trigger>
                            <x-button icon="o-bars-arrow-down" class="btn-circle" />
                        </x-slot:trigger>

                        @if ($booking->status->value !== 'cancelled')
                            <x-menu-item icon="o-pencil" title="Edit Booking" class="btn-ghost btn-sm"
                                link="{{ route('admin.bookings.boat.edit', $booking->id) }}" />
                        @endif
                        <x-menu-item icon="o-eye" link="{{ route('admin.bookings.boat.show', $booking->id) }}"
                            class="btn-ghost btn-sm" title="View Details" />
                    </x-dropdown>
                </div>
            @endscope

            <x-slot:empty>
                <x-empty icon="o-archive-box" message="No bookings found" />
            </x-slot:empty>
        </x-table>
    </x-card>
</div>
