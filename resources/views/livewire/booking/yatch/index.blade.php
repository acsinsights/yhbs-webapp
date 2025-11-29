<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use App\Models\{Booking, Yatch};

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public int $perPage = 10;

    public function delete($id): void
    {
        $booking = Booking::where('bookingable_type', Yatch::class)->findOrFail($id);
        $booking->delete();

        $this->success('Booking deleted successfully.');
    }

    public function rendering(View $view)
    {
        $view->bookings = Booking::query()
            ->where('bookingable_type', Yatch::class)
            ->with(['bookingable', 'user'])
            ->when($this->search, function ($query) {
                return $query
                    ->whereHas('user', function ($q) {
                        $q->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%");
                    })
                    ->orWhereHas('bookingable', function ($q) {
                        $q->where('name', 'like', "%{$this->search}%")->orWhere('sku', 'like', "%{$this->search}%");
                    });
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);

        $view->headers = [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'user.name', 'label' => 'Customer', 'sortable' => false, 'class' => 'whitespace-nowrap'],
            ['key' => 'yatch', 'label' => 'Yacht', 'sortable' => false, 'class' => 'whitespace-nowrap'],
            ['key' => 'check_in', 'label' => 'Check In', 'sortable' => true, 'class' => 'whitespace-nowrap'],
            ['key' => 'check_out', 'label' => 'Check Out', 'sortable' => true, 'class' => 'whitespace-nowrap'],
            ['key' => 'adults', 'label' => 'Adults', 'sortable' => false, 'class' => 'whitespace-nowrap'],
            ['key' => 'children', 'label' => 'Children', 'sortable' => false, 'class' => 'whitespace-nowrap'],
            ['key' => 'price', 'label' => 'Amount', 'sortable' => true, 'class' => 'whitespace-nowrap'],
            ['key' => 'payment_status', 'label' => 'Payment Status', 'class' => 'whitespace-nowrap'],
            ['key' => 'payment_method', 'label' => 'Payment Method', 'class' => 'whitespace-nowrap'],
            ['key' => 'status', 'label' => 'Status', 'class' => 'whitespace-nowrap'],
        ];
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
                link="{{ route('admin.bookings.yatch.create') }}" />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-table :headers="$headers" :rows="$bookings" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[10, 25, 50, 100]">
            @scope('cell_user.name', $booking)
                <div class="font-semibold">{{ $booking->user->name ?? 'N/A' }}</div>
            @endscope

            @scope('cell_yatch', $booking)
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
                        <span>{{ Carbon::parse($booking->check_in)->format('M d, Y') }}</span>
                        <span
                            class="text-xs text-base-content/50">{{ Carbon::parse($booking->check_in)->format('h:i A') }}</span>
                    </div>
                @else
                    <span class="text-base-content/50">—</span>
                @endif
            @endscope

            @scope('cell_check_out', $booking)
                @if ($booking->check_out)
                    <div class="flex flex-col">
                        <span>{{ Carbon::parse($booking->check_out)->format('M d, Y') }}</span>
                        <span
                            class="text-xs text-base-content/50">{{ Carbon::parse($booking->check_out)->format('h:i A') }}</span>
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
                @php
                    $statusColors = [
                        'paid' => 'badge-success',
                        'pending' => 'badge-warning',
                        'failed' => 'badge-error',
                    ];
                    $color = $statusColors[$booking->payment_status] ?? 'badge-ghost';
                @endphp
                <x-badge :value="ucfirst($booking->payment_status)" class="badge-soft {{ $color }}" />
            @endscope

            @scope('cell_payment_method', $booking)
                <x-badge :value="ucfirst($booking->payment_method)" class="badge-soft badge-info" />
            @endscope

            @scope('cell_status', $booking)
                @php
                    $statusColors = [
                        'pending' => 'badge-warning',
                        'booked' => 'badge-primary',
                        'checked_in' => 'badge-info',
                        'cancelled' => 'badge-error',
                        'checked_out' => 'badge-success',
                    ];
                    $color = $statusColors[$booking->status] ?? 'badge-ghost';
                @endphp
                <x-badge :value="ucfirst(str_replace('_', ' ', $booking->status))" class="badge-soft {{ $color }}" />
            @endscope

            @scope('actions', $booking)
                <div class="flex items-center gap-2">
                    <x-button icon="o-eye" link="{{ route('admin.bookings.yatch.show', $booking->id) }}"
                        class="btn-ghost btn-sm" tooltip="View Details" />
                    <x-button icon="o-trash" wire:click="delete({{ $booking->id }})"
                        wire:confirm="Are you sure you want to delete this booking?" spinner
                        class="btn-ghost btn-sm text-error" tooltip="Delete" />
                </div>
            @endscope

            <x-slot:empty>
                <x-empty icon="o-sparkles" message="No bookings found" />
            </x-slot:empty>
        </x-table>
    </x-card>
</div>
