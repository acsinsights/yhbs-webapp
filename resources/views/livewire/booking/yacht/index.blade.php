<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use App\Models\{Booking, Yacht};

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public int $perPage = 10;

    public function checkin($id): void
    {
        $booking = Booking::where('bookingable_type', Yacht::class)->findOrFail($id);
        $booking->update(['status' => 'checked_in']);

        $this->success('Booking checked in successfully.');
    }

    public function checkout($id): void
    {
        $booking = Booking::where('bookingable_type', Yacht::class)->findOrFail($id);
        $booking->update(['status' => 'checked_out']);

        $this->success('Booking checked out successfully.');
    }

    public function rendering(View $view)
    {
        $view->bookings = Booking::query()
            ->where('bookingable_type', Yacht::class)
            ->with(['bookingable', 'user'])
            ->search($this->search, ['name', 'sku'])
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);

        $view->headers = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'user.name', 'label' => 'Customer', 'sortable' => false, 'class' => 'whitespace-nowrap'], ['key' => 'yacht', 'label' => 'Yacht', 'sortable' => false, 'class' => 'whitespace-nowrap'], ['key' => 'check_in', 'label' => 'Check In', 'sortable' => true, 'class' => 'whitespace-nowrap'], ['key' => 'check_out', 'label' => 'Check Out', 'sortable' => true, 'class' => 'whitespace-nowrap'], ['key' => 'price', 'label' => 'Amount', 'sortable' => true, 'class' => 'whitespace-nowrap'], ['key' => 'payment_status', 'label' => 'Payment Status', 'class' => 'whitespace-nowrap'], ['key' => 'payment_method', 'label' => 'Payment Method', 'class' => 'whitespace-nowrap'], ['key' => 'status', 'label' => 'Status', 'class' => 'whitespace-nowrap']];
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
                'label' => 'Yacht Bookings',
                'icon' => 'o-sparkles',
            ],
        ];
    @endphp

    <x-header title="Yacht Bookings" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Manage all yacht charter bookings</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live.debounce="search" clearable />
            <x-button icon="o-plus" class="btn-primary" label="New Booking"
                link="{{ route('admin.bookings.yacht.create') }}" />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-table :headers="$headers" :rows="$bookings" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[10, 25, 50, 100]">
            @scope('cell_user.name', $booking)
                <div class="font-semibold">{{ $booking->user->name ?? 'N/A' }}</div>
            @endscope

            @scope('cell_yacht', $booking)
                @if ($booking->bookingable)
                    <div class="flex flex-col">
                        <div class="font-semibold">{{ $booking->bookingable->name }}</div>
                        <div class="text-xs text-base-content/50">
                            SKU: {{ $booking->bookingable->sku ?? '—' }}
                        </div>
                    </div>
                @else
                    <span class="text-base-content/50">—</span>
                @endif
            @endscope

            @scope('cell_check_in', $booking)
                @if ($booking->check_in)
                    <div class="flex flex-col">
                        <span>{{ $booking->check_in->format('M d, Y') }}</span>
                        <span class="text-xs text-base-content/50">{{ $booking->check_in->format('h:i A') }}</span>
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

                        @if ($booking->canCheckIn())
                            <x-menu-item icon="o-pencil" title="Edit Booking" class="btn-ghost btn-sm"
                                link="{{ route('admin.bookings.yacht.edit', $booking->id) }}" />
                            <x-menu-item icon="o-arrow-right-end-on-rectangle" title="Check In"
                                wire:click="checkin({{ $booking->id }})" spinner class="text-info"
                                wire:confirm="Are you sure you want to check in this booking?" />
                        @elseif ($booking->canCheckOut())
                            <x-menu-item icon="o-arrow-right-start-on-rectangle" title="Check Out"
                                wire:click="checkout({{ $booking->id }})" class="btn-ghost btn-sm text-success"
                                wire:confirm="Are you sure you want to checkout this booking?" spinner />
                        @elseif ($booking->canBeEdited())
                            <x-menu-item icon="o-pencil" title="Edit Booking"
                                link="{{ route('admin.bookings.yacht.edit', $booking->id) }}" class="btn-ghost btn-sm" />
                        @endif
                        <x-menu-item icon="o-eye" link="{{ route('admin.bookings.yacht.show', $booking->id) }}"
                            class="btn-ghost btn-sm" title="View Details" />
                    </x-dropdown>
                </div>
            @endscope

            <x-slot:empty>
                <x-empty icon="o-sparkles" message="No bookings found" />
            </x-slot:empty>
        </x-table>
    </x-card>
</div>
