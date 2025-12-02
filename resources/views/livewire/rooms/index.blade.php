<?php

use Mary\Traits\Toast;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use App\Models\{House, Room};

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public int $perPage = 10;

    public bool $createModal = false;
    public int $house_id = 0;
    public string $name = '';
    public string $room_number = '';

    public function mount()
    {
        $this->house_id = House::first()->id;
    }

    // Delete action
    public function delete($id): void
    {
        $room = Room::findOrFail($id);
        $room->delete();

        $this->success('Room deleted successfully.');
    }

    public function createRoom(): void
    {
        $this->validate([
            'house_id' => 'required|exists:houses,id',
            'name' => 'required|string|max:255|unique:rooms,name,NULL,id,house_id,' . $this->house_id,
            'room_number' => 'required|string|max:255|unique:rooms,room_number,NULL,id,house_id,' . $this->house_id,
        ]);

        // Auto-generate slug from name
        $slug = Str::slug($this->name);

        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while (Room::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $room = Room::create([
            'house_id' => $this->house_id,
            'name' => $this->name,
            'slug' => $slug,
            'room_number' => $this->room_number,
        ]);

        $this->createModal = false;
        $this->reset('house_id', 'name', 'room_number');
        $this->success('Room created successfully.', redirectTo: route('admin.rooms.edit', $room->id));
    }

    public function rendering(View $view)
    {
        $view->rooms = Room::query()
            ->with(['house', 'categories', 'amenities'])
            ->search($this->search)
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);

        $view->headers = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'name', 'label' => 'Name'], ['key' => 'room_number', 'label' => 'Room Number'], ['key' => 'house.name', 'label' => 'House', 'sortable' => false], ['key' => 'adults', 'label' => 'Adults'], ['key' => 'children', 'label' => 'Children'], ['key' => 'price', 'label' => 'Price', 'class' => 'whitespace-nowrap'], ['key' => 'discount_price', 'label' => 'Discount Price', 'class' => 'whitespace-nowrap']];

        $view->houses = House::latest()->get();
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
                'label' => 'Rooms',
                'icon' => 'o-home-modern',
            ],
        ];
    @endphp

    <x-header title="Rooms" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Manage all hotel rooms</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>

        <x-slot:actions>
            <x-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live.debounce="search" clearable />
            <x-button icon="o-plus" class="btn-primary" tooltip="Add Room" @click="$wire.createModal = true" />
            <x-button icon="o-funnel" tooltip-left="Filters" class="btn-info" responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-table :headers="$headers" :rows="$rooms" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[10, 25, 50, 100]">

            @scope('cell_name', $room)
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full {{ $room->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    <span class="font-medium">
                        {{ $room->name }}
                    </span>
                </div>
            @endscope

            @scope('cell_room_number', $room)
                <x-badge :value="$room->room_number" class="badge-soft badge-primary" />
            @endscope

            @scope('cell_hotel.name', $room)
                <x-button tooltip="{{ $room->hotel->name }}" link="{{ route('admin.hotels.edit', $room->hotel->id) }}"
                    class="btn-circle btn-sm text-base-content/50">
                    <x-icon name="o-building-office-2" class="w-4 h-4" />
                </x-button>
            @endscope

            @scope('cell_adults', $room)
                <x-badge :value="$room->adults ?? 'N/A'" class="badge-soft badge-info badge-sm" />
            @endscope

            @scope('cell_children', $room)
                <x-badge :value="$room->children ?? 'N/A'" class="badge-soft badge-warning badge-sm" />
            @endscope

            @scope('cell_price', $room)
                <div class="font-semibold line-through">
                    {{ currency_format($room->price) }}
                </div>
            @endscope

            @scope('cell_discount_price', $room)
                @if ($room->discount_price)
                    <div class="flex items-center gap-2">
                        <div class="font-semibold text-success">
                            {{ currency_format($room->discount_price) }}
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
                    <x-button icon="o-eye" link="{{ route('admin.rooms.show', $room->id) }}" class="btn-ghost btn-sm"
                        tooltip="Show" />
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

    {{-- Create Room Modal --}}
    <x-modal wire:model="createModal" title="Create Room" class="backdrop-blur" max-width="md">
        <x-form wire:submit="createRoom">
            <div class="space-y-4">
                <x-select wire:model="house_id" label="House" placeholder="Select a house" :options="$houses"
                    option-value="id" option-label="name" icon="o-building-office-2"
                    hint="Select the house this room belongs to" />

                <x-input wire:model="name" label="Room Name" placeholder="e.g., Standard Room, Deluxe Suite"
                    icon="o-tag" hint="Display name for the room (slug will be auto-generated)" />

                <x-input wire:model="room_number" label="Room Number" placeholder="e.g., 101, 202, Suite A"
                    icon="o-hashtag" hint="Unique room identifier" />
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" @click="$wire.createModal = false"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Create Room" type="submit" class="btn-primary w-full sm:w-auto"
                        spinner="createRoom" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
