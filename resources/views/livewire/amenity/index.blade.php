<?php

use Mary\Traits\Toast;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use App\Models\Amenity;

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public int $perPage = 10;

    public bool $createModal = false;
    public string $name = '';
    public string $type = 'room';

    public bool $editModal = false;
    public ?Amenity $editingAmenity = null;
    public string $edit_name = '';
    public string $edit_type = 'room';

    // Delete action
    public function delete($id): void
    {
        $amenity = Amenity::findOrFail($id);
        $amenity->delete();

        $this->success('Amenity deleted successfully.');
    }

    public function createAmenity(): void
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:amenities,name',
            'type' => 'required|in:room,yacht',
        ]);

        Amenity::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'type' => $this->type,
        ]);

        $this->createModal = false;
        $this->reset('name', 'type');
        $this->success('Amenity created successfully.');
    }

    public function openEditModal($id): void
    {
        $this->editingAmenity = Amenity::findOrFail($id);
        $this->edit_name = $this->editingAmenity->name;
        $this->edit_type = $this->editingAmenity->type ?? 'room';
        $this->editModal = true;
    }

    public function updateAmenity(): void
    {
        $this->validate([
            'edit_name' => 'required|string|max:255|unique:amenities,name,' . $this->editingAmenity->id,
            'edit_type' => 'required|in:room,yacht',
        ]);

        $this->editingAmenity->update([
            'name' => $this->edit_name,
            'slug' => Str::slug($this->edit_name),
            'type' => $this->edit_type,
        ]);

        $this->editModal = false;
        $this->reset('edit_name', 'edit_type', 'editingAmenity');
        $this->success('Amenity updated successfully.');
    }

    public function rendering(View $view)
    {
        $view->amenities = Amenity::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);

        $view->headers = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'name', 'label' => 'Name', 'sortable' => true], ['key' => 'type', 'label' => 'Type', 'sortable' => true]];
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
                'label' => 'Amenities',
                'icon' => 'o-sparkles',
            ],
        ];
    @endphp

    <x-header title="Amenities" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Manage all amenities</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>

        <x-slot:actions>
            <x-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live.debounce="search" clearable />
            <x-button icon="o-plus" class="btn-primary" tooltip="Add Amenity" @click="$wire.createModal = true" />
            <x-button icon="o-funnel" tooltip-left="Filters" class="btn-info" responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-table :headers="$headers" :rows="$amenities" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[10, 25, 50, 100]">
            @scope('cell_name', $amenity)
                <x-badge :value="$amenity->name" class="badge-soft badge-primary" />
            @endscope

            @scope('cell_slug', $amenity)
                <code class="text-xs bg-base-200 px-2 py-1 rounded">{{ $amenity->slug }}</code>
            @endscope

            @scope('cell_icon', $amenity)
                @if ($amenity->icon)
                    @if (str_starts_with($amenity->icon, 'http'))
                        <img src="{{ $amenity->icon }}" alt="{{ $amenity->name }}" class="w-10 h-10 rounded object-cover" />
                    @else
                        <img src="{{ asset($amenity->icon) }}" alt="{{ $amenity->name }}"
                            class="w-10 h-10 rounded object-cover" />
                    @endif
                @else
                    <span class="text-base-content/50">—</span>
                @endif
            @endscope

            @scope('cell_type', $amenity)
                <x-badge :value="$amenity->type ?? 'room'" class="badge-soft badge-sm" />
            @endscope

            @scope('actions', $amenity)
                <div class="flex items-center gap-2">
                    <x-button icon="o-pencil" @click="$wire.openEditModal({{ $amenity->id }})" class="btn-ghost btn-sm"
                        tooltip="Edit" />
                    <x-button icon="o-trash" wire:click="delete({{ $amenity->id }})"
                        wire:confirm="Are you sure you want to delete this amenity?" spinner
                        class="btn-ghost btn-sm text-error" tooltip="Delete" />
                </div>
            @endscope

            <x-slot:empty>
                <x-empty icon="o-sparkles" message="No amenities found" />
            </x-slot:empty>
        </x-table>
    </x-card>

    {{-- Create Amenity Modal --}}
    <x-modal wire:model="createModal" title="Create Amenity" class="backdrop-blur" max-width="md">
        <x-form wire:submit="createAmenity">
            <div class="space-y-4">
                <x-input wire:model="name" label="Amenity Name" placeholder="Enter amenity name" icon="o-tag"
                    hint="Unique amenity name" />

                <x-select wire:model="type" label="Type" placeholder="Select type" :options="[['id' => 'room', 'name' => 'Room'], ['id' => 'yacht', 'name' => 'Yacht']]" option-value="id"
                    option-label="name" icon="o-sparkles" hint="Amenity type" />
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" @click="$wire.createModal = false"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Create Amenity" type="submit" class="btn-primary w-full sm:w-auto"
                        spinner="createAmenity" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Edit Amenity Modal --}}
    <x-modal wire:model="editModal" title="Edit Amenity" class="backdrop-blur" max-width="md">
        <x-form wire:submit="updateAmenity">
            <div class="space-y-4">
                <x-input wire:model="edit_name" label="Amenity Name" placeholder="Enter amenity name" icon="o-tag"
                    hint="Unique amenity name" />

                <x-select wire:model="edit_type" label="Type" placeholder="Select type" :options="[['id' => 'room', 'name' => 'Room'], ['id' => 'yacht', 'name' => 'Yacht']]"
                    option-value="id" option-label="name" icon="o-sparkles" hint="Amenity type" />
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" @click="$wire.editModal = false"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Update Amenity" type="submit" class="btn-primary w-full sm:w-auto"
                        spinner="updateAmenity" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
