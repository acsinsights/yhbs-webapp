<?php

use Mary\Traits\Toast;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use App\Models\BoatServiceType;

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'name', 'direction' => 'asc'];
    public int $perPage = 10;

    public bool $showEditModal = false;
    public ?BoatServiceType $editingServiceType = null;
    public string $name = '';
    public bool $is_active = true;

    public bool $showDeleteModal = false;
    public ?BoatServiceType $deletingServiceType = null;

    public function create(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255|unique:boat_service_types,name',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        BoatServiceType::create($validated);

        $this->success('Service type created successfully!');
        $this->showCreateModal = false;
        $this->reset('name', 'is_active');
    }

    public function openEditModal(BoatServiceType $serviceType): void
    {
        $this->editingServiceType = $serviceType;
        $this->name = $serviceType->name;
        $this->is_active = $serviceType->is_active;
        $this->showEditModal = true;
    }

    public function update(): void
    {
        if (!$this->editingServiceType) {
            return;
        }

        $validated = $this->validate([
            'name' => 'required|string|max:255|unique:boat_service_types,name,' . $this->editingServiceType->id,
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);

        $this->editingServiceType->update($validated);

        $this->success('Service type updated successfully!');
        $this->showEditModal = false;
        $this->editingServiceType = null;
    }

    public function openDeleteModal(int $id): void
    {
        $this->deletingServiceType = BoatServiceType::findOrFail($id);
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingServiceType->boats()->count() > 0) {
            $this->error('Cannot delete service type with boats assigned to it.');
            $this->showDeleteModal = false;
            $this->deletingServiceType = null;
            return;
        }

        $this->deletingServiceType->delete();
        $this->success('Service type deleted successfully!');
        $this->showDeleteModal = false;
        $this->deletingServiceType = null;
    }

    public function rendering(View $view): void
    {
        $view->serviceTypes = BoatServiceType::query()->withCount('boats')->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))->orderBy(...array_values($this->sortBy))->paginate($this->perPage);

        $view->headers = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'name', 'label' => 'Name'], ['key' => 'slug', 'label' => 'Slug'], ['key' => 'boats_count', 'label' => 'Boats']];
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
                'label' => 'Boat Service Types',
                'icon' => 'o-tag',
            ],
        ];
    @endphp

    <x-header title="Boat Service Types" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Manage boat service types</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>

        <x-slot:actions>
            <x-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live.debounce="search" clearable />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-table :headers="$headers" :rows="$serviceTypes" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[10, 25, 50, 100]">

            @scope('cell_name', $serviceType)
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full {{ $serviceType->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    <span class="font-medium">
                        {{ $serviceType->name }}
                    </span>
                </div>
            @endscope

            @scope('cell_slug', $serviceType)
                <x-badge :value="$serviceType->slug" class="badge-soft badge-primary" />
            @endscope

            @scope('cell_boats_count', $serviceType)
                <x-badge :value="$serviceType->boats_count" class="badge-soft badge-info" />
            @endscope

            @scope('actions', $serviceType)
                <div class="flex items-center gap-2">
                    <x-button icon="o-pencil" wire:click="openEditModal({{ $serviceType->id }})" class="btn-ghost btn-sm"
                        tooltip="Edit" />
                    <x-button icon="o-trash" wire:click="openDeleteModal({{ $serviceType->id }})"
                        class="btn-ghost btn-sm text-error" tooltip="Delete" />
                </div>
            @endscope

            <x-slot:empty>
                <x-empty icon="o-tag" message="No service types found" />
            </x-slot:empty>
        </x-table>
    </x-card>

    {{-- Edit Modal --}}
    <x-modal wire:model="showEditModal" title="Edit Service Type" class="backdrop-blur" max-width="md">
        <x-form wire:submit="update">
            <div class="space-y-4">
                <x-input label="Service Type Name *" wire:model="name" icon="o-tag"
                    placeholder="e.g., Yacht, Water Taxi, Ferry..." hint="Display name (slug will be auto-generated)" />

                <x-checkbox label="Active" wire:model="is_active" />
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" @click="$wire.showEditModal = false"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Update Service Type" type="submit"
                        class="btn-primary w-full sm:w-auto" spinner="update" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Delete Confirmation Modal --}}
    <x-modal wire:model="showDeleteModal" title="Confirm Deletion" class="backdrop-blur" max-width="md">
        @if($deletingServiceType)
            <div class="space-y-4">
                <x-alert icon="o-exclamation-triangle" class="alert-warning">
                    Are you sure you want to delete <strong>{{ $deletingServiceType->name }}</strong>?
                </x-alert>
                <p class="text-sm text-base-content/70">
                    This action cannot be undone. This service type has <strong>{{ $deletingServiceType->boats()->count() }}</strong> boat(s) associated with it.
                </p>
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" @click="$wire.showDeleteModal = false"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-trash" label="Delete" wire:click="delete"
                        class="btn-error w-full sm:w-auto" spinner="delete" responsive />
                </div>
            </x-slot:actions>
        @endif
    </x-modal>
</div>
