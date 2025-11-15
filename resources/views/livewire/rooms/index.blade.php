<?php

use App\Models\Room;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    // Delete action
    public function delete($id): void
    {
        $room = Room::findOrFail($id);
        $room->delete();

        $this->success('Room deleted successfully.', position: 'toast-bottom');
    }

    // Table headers
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'room_number', 'label' => 'Room Number', 'sortable' => true], ['key' => 'hotel.name', 'label' => 'Hotel', 'sortable' => false], ['key' => 'price', 'label' => 'Price', 'sortable' => true], ['key' => 'discount_price', 'label' => 'Discount Price', 'sortable' => true], ['key' => 'categories', 'label' => 'Categories', 'sortable' => false], ['key' => 'amenities', 'label' => 'Amenities', 'sortable' => false]];
    }

    public function rooms(): LengthAwarePaginator
    {
        return Room::query()
            ->with(['hotel', 'categories', 'amenities'])
            ->when($this->search, function ($query) {
                $query->where('room_number', 'like', "%{$this->search}%")->orWhereHas('hotel', function ($q) {
                    $q->where('name', 'like', "%{$this->search}%");
                });
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    public function with(): array
    {
        return [
            'rooms' => $this->rooms(),
            'headers' => $this->headers(),
        ];
    }
}; ?>

@php
    $headers = [
        ['key' => 'id', 'label' => '#'],
        ['key' => 'room_number', 'label' => 'Room Number'],
        ['key' => 'hotel.name', 'label' => 'Hotel'],
        ['key' => 'price', 'label' => 'Price'],
        ['key' => 'discount_price', 'label' => 'Discount Price'],
        ['key' => 'categories', 'label' => 'Categories'],
        ['key' => 'amenities', 'label' => 'Amenities'],
    ];
@endphp

<div>
    <x-header title="Rooms" subtitle="Manage all hotel rooms" separator>
        <x-slot:middle class="justify-end!">
            <x-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live.debounce="search" clearable />
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-funnel" label="Filters" responsive />
            <x-button icon="o-plus" class="btn-primary" label="Add Room" link="{{ route('admin.rooms.create') }}" />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-table :headers="$headers" :rows="$rooms" :sort-by="$sortBy">
            {{-- Custom ID cell --}}
            @scope('cell_id', $room)
                <strong>{{ $room->id }}</strong>
            @endscope

            {{-- Custom Room Number cell --}}
            @scope('cell_room_number', $room)
                <x-badge :value="$room->room_number" class="badge-soft badge-primary" />
            @endscope

            {{-- Nested Hotel name --}}
            @scope('cell_hotel.name', $room)
                <div class="flex items-center gap-2">
                    <i class="o-building-office-2 w-4 h-4"></i>
                    <span>{{ $room->hotel->name ?? 'N/A' }}</span>
                </div>
            @endscope

            {{-- Price cell --}}
            @scope('cell_price', $room)
                <div class="font-semibold">
                    ${{ number_format($room->price ?? 0, 2) }}
                </div>
            @endscope

            {{-- Discount Price cell --}}
            @scope('cell_discount_price', $room)
                @if ($room->discount_price)
                    <div class="font-semibold text-success">
                        ${{ number_format($room->discount_price, 2) }}
                    </div>
                @else
                    <span class="text-base-content/50">—</span>
                @endif
            @endscope

            {{-- Categories cell --}}
            @scope('cell_categories', $room)
                <div class="flex flex-wrap gap-1">
                    @forelse($room->categories as $category)
                        <x-badge :value="$category->name" class="badge-soft badge-sm" />
                    @empty
                        <span class="text-base-content/50 text-sm">No categories</span>
                    @endforelse
                </div>
            @endscope

            {{-- Amenities cell --}}
            @scope('cell_amenities', $room)
                <div class="flex flex-wrap gap-1">
                    @forelse($room->amenities->take(3) as $amenity)
                        <x-badge :value="$amenity->name" class="badge-soft badge-sm badge-outline" />
                    @empty
                        <span class="text-base-content/50 text-sm">No amenities</span>
                    @endforelse
                    @if ($room->amenities->count() > 3)
                        <x-badge :value="'+' . ($room->amenities->count() - 3) . ' more'" class="badge-soft badge-sm" />
                    @endif
                </div>
            @endscope

            {{-- Actions slot --}}
            @scope('actions', $room)
                <x-button icon="o-pencil" link="{{ route('admin.rooms.edit', $room->id) }}" class="btn-ghost btn-sm"
                    tooltip="Edit" />
                <x-button icon="o-trash" wire:click="delete({{ $room->id }})"
                    wire:confirm="Are you sure you want to delete this room?" spinner class="btn-ghost btn-sm text-error"
                    tooltip="Delete" />
            @endscope
        </x-table>
    </x-card>
</div>
