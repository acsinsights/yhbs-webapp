<?php

use App\Models\Room;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Illuminate\View\View;

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public int $perPage = 10;

    // Delete action
    public function delete($id): void
    {
        $room = Room::findOrFail($id);
        $room->delete();

        $this->success('Room deleted successfully.');
    }

    public function rendering(View $view)
    {
        $view->rooms = Room::query()
            ->with(['hotel', 'categories', 'amenities'])
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);

        $view->headers = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'room_number', 'label' => 'Room Number', 'sortable' => true], ['key' => 'hotel.name', 'label' => 'Hotel', 'sortable' => false], ['key' => 'price', 'label' => 'Price', 'sortable' => true], ['key' => 'discount_price', 'label' => 'Discount Price', 'sortable' => true], ['key' => 'categories', 'label' => 'Categories', 'sortable' => false], ['key' => 'amenities', 'label' => 'Amenities', 'sortable' => false]];
    }
}; ?>

<div>
    <x-header title="Rooms" subtitle="Manage all hotel rooms" separator>
        <x-slot:actions>
            <x-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live.debounce="search" clearable />
            <x-button icon="o-plus" class="btn-primary" tooltip="Add Room" link="{{ route('admin.rooms.create') }}" />
            <x-button icon="o-funnel" label="Filters" responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-table :headers="$headers" :rows="$rooms" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[10, 25, 50, 100]">
            @scope('cell_room_number', $room)
                <x-badge :value="$room->room_number" class="badge-soft badge-primary" />
            @endscope

            @scope('cell_price', $room)
                <div class="font-semibold line-through">
                    {{ number_format($room->price ?? 0, 2) }}
                </div>
            @endscope

            @scope('cell_discount_price', $room)
                @if ($room->discount_price)
                    <div class="flex items-center gap-2">
                        <div class="font-semibold text-success">
                            {{ number_format($room->discount_price, 2) }}
                        </div>

                        <div>
                            <x-badge :value="number_format((($room->price - $room->discount_price) / $room->price) * 100, 2) .
                                '% off'" class="badge-soft badge-sm badge-error" />
                        </div>
                    </div>
                @else
                    <span class="text-base-content/50">—</span>
                @endif
            @endscope

            @scope('cell_categories', $room)
                <div class="flex flex-wrap gap-1">
                    <x-badge :value="$room->categories->count()" class="badge-soft badge-sm" />
                </div>
            @endscope

            @scope('cell_amenities', $room)
                <div class="flex flex-wrap gap-1">
                    <x-badge :value="$room->amenities->count()" class="badge-soft badge-sm" />
                </div>
            @endscope

            @scope('actions', $room)
                <div class="flex items-center gap-2">
                    <x-button icon="o-pencil" link="{{ route('admin.rooms.edit', $room->id) }}" class="btn-ghost btn-sm"
                        tooltip="Edit" />
                    <x-button icon="o-trash" wire:click="delete({{ $room->id }})"
                        wire:confirm="Are you sure you want to delete this room?" spinner
                        class="btn-ghost btn-sm text-error" tooltip="Delete" />
                </div>
            @endscope

            <x-slot:empty>
                <x-empty icon="o-home-modern" message="No rooms found" />
            </x-slot:empty>
        </x-table>
    </x-card>
</div>
