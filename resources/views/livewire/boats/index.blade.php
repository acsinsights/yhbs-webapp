<?php

use Mary\Traits\Toast;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use App\Models\Boat;

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public int $perPage = 10;

    public bool $createModal = false;
    public string $name = '';
    public string $service_type = '';

    public bool $addServiceTypeModal = false;
    public string $service_type_name = '';

    public function createBoat(): void
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:boats,name',
            'service_type' => 'required|string|exists:boat_service_types,slug',
        ]);

        // Auto-generate slug from name
        $slug = Str::slug($this->name);

        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while (Boat::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $boat = Boat::create([
            'name' => $this->name,
            'slug' => $slug,
            'service_type' => $this->service_type,
            'min_passengers' => 1,
            'max_passengers' => 10,
            'buffer_time' => 0,
            'is_active' => false,
        ]);

        $this->createModal = false;
        $this->reset(['name', 'service_type']);
        $this->success('Boat created successfully.', redirectTo: route('admin.boats.edit', $boat->id));
    }

    public function saveServiceType(): void
    {
        $this->validate([
            'service_type_name' => 'required|string|max:255|unique:boat_service_types,name',
        ]);

        $serviceType = \App\Models\BoatServiceType::create([
            'name' => $this->service_type_name,
            'slug' => Str::slug($this->service_type_name),
            'is_active' => true,
        ]);

        $this->success('Service type created successfully.');
        $this->addServiceTypeModal = false;
        $this->reset('service_type_name');
        $this->service_type = $serviceType->slug;
    }

    public function rendering(View $view)
    {
        $view->boats = Boat::query()->when($this->search, fn($q) => $q->search($this->search))->orderBy(...array_values($this->sortBy))->paginate($this->perPage);

        $view->headers = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'name', 'label' => 'Name'], ['key' => 'service_type', 'label' => 'Service Type'], ['key' => 'max_passengers', 'label' => 'Capacity'], ['key' => 'price', 'label' => 'Price']];

        $view->serviceTypes = \App\Models\BoatServiceType::where('is_active', true)->get()->map(fn($type) => ['id' => $type->slug, 'name' => $type->name])->toArray();
    }

    public function delete(int $id): void
    {
        $boat = Boat::findOrFail($id);

        // Check if boat has any bookings
        if ($boat->bookings()->count() > 0) {
            $this->error('Cannot delete boat with existing bookings.');
            return;
        }

        $boat->delete();
        $this->success('Boat deleted successfully.');
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
                'label' => 'Boats',
                'icon' => 'o-circle-stack',
            ],
        ];
    @endphp

    <x-header title="Boats" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Manage all boats and marine services</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>

        <x-slot:actions>
            <x-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live.debounce="search" clearable />
            <x-button icon="o-plus" class="btn-primary" tooltip="Add Boat" @click="$wire.createModal = true" />
            <x-button icon="o-funnel" tooltip-left="Filters" class="btn-info" responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-table :headers="$headers" :rows="$boats" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[10, 25, 50, 100]">

            @scope('cell_name', $boat)
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full {{ $boat->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    <span class="font-medium">
                        {{ $boat->name }}
                    </span>
                </div>
            @endscope

            @scope('cell_service_type', $boat)
                <x-badge :value="$boat->service_type_label" class="badge-soft badge-primary" />
            @endscope

            @scope('cell_max_passengers', $boat)
                <x-badge :value="$boat->min_passengers . '-' . $boat->max_passengers" class="badge-soft badge-info badge-sm" />
            @endscope

            @scope('cell_price', $boat)
                <div class="font-semibold">
                    {{ $boat->display_price }}
                </div>
            @endscope

            @scope('actions', $boat)
                <div class="flex items-center gap-2">
                    <x-button icon="o-eye" link="{{ route('admin.boats.show', $boat->id) }}" class="btn-ghost btn-sm"
                        tooltip="Show" />
                    <x-button icon="o-pencil" link="{{ route('admin.boats.edit', $boat->id) }}" class="btn-ghost btn-sm"
                        tooltip="Edit" />
                    <x-button icon="o-trash" wire:click="delete({{ $boat->id }})"
                        wire:confirm="Are you sure you want to delete this boat?" spinner
                        class="btn-ghost btn-sm text-error" tooltip="Delete" />
                </div>
            @endscope

            <x-slot:empty>
                <x-empty icon="o-circle-stack" message="No boats found" />
            </x-slot:empty>
        </x-table>
    </x-card>

    {{-- Create Boat Modal --}}
    <x-modal wire:model="createModal" title="Create Boat" class="backdrop-blur" max-width="md">
        <x-form wire:submit="createBoat">
            <div class="space-y-4">
                <x-input wire:model="name" label="Boat Name *" placeholder="e.g., Marina 1, VIP Limousine"
                    icon="o-tag" hint="Display name for the boat (slug will be auto-generated)" />

                <x-choices-offline wire:model.live="service_type" label="Service Type *" icon="o-squares-2x2"
                    :options="$serviceTypes" placeholder="Select service type..." single searchable />
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" @click="$wire.createModal = false"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Create Boat" type="submit" class="btn-primary w-full sm:w-auto"
                        spinner="createBoat" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Add Service Type Modal --}}
    <x-modal wire:model="addServiceTypeModal" title="Add Service Type" class="backdrop-blur" max-width="md">
        <x-form wire:submit="saveServiceType">
            <x-input label="Service Type Name *" wire:model="service_type_name" icon="o-tag"
                placeholder="e.g., Yacht, Water Taxi, Ferry..." />

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" @click="$wire.addServiceTypeModal = false"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Add Service Type" type="submit"
                        class="btn-primary w-full sm:w-auto" spinner="saveServiceType" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
