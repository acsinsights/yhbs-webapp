<?php

use Mary\Traits\Toast;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use App\Models\Room;

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public int $perPage = 10;

    public bool $createModal = false;
    public string $name = '';
    public string $room_number = '';

    public function createRoom(): void
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:rooms,name',
            'room_number' => 'required|string|max:255|unique:rooms,room_number',
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
            'name' => $this->name,
            'slug' => $slug,
            'room_number' => $this->room_number,
        ]);

        $this->createModal = false;
        $this->reset('name', 'room_number');
        $this->success('Room created successfully.', redirectTo: route('admin.rooms.edit', $room->id));
    }

    public function rendering(View $view)
    {
        $view->rooms = Room::query()
            ->with(['categories', 'amenities'])
            ->search($this->search)
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);

        $view->headers = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'name', 'label' => 'Name'], ['key' => 'room_number', 'label' => 'Room Number'], ['key' => 'adults', 'label' => 'Adults'], ['key' => 'children', 'label' => 'Children'], ['key' => 'price_per_night', 'label' => 'Price Per Night', 'class' => 'whitespace-nowrap']];
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

            @scope('cell_adults', $room)
                <x-badge :value="$room->adults ?? 'N/A'" class="badge-soft badge-info badge-sm" />
            @endscope

            @scope('cell_children', $room)
                <x-badge :value="$room->children ?? 'N/A'" class="badge-soft badge-warning badge-sm" />
            @endscope

            @scope('cell_price_per_night', $room)
                <div class="font-semibold">
                    {{ currency_format($room->price_per_night) }}
                </div>
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
