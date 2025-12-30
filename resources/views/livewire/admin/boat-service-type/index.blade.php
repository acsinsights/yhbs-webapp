<?php

use function Livewire\Volt\{state, with, usesPagination, uses};

use App\Models\BoatServiceType;
use Mary\Traits\Toast;

uses([Toast::class]);
usesPagination(theme: 'tailwind');

state([
    'search' => '',
    'perPage' => 10,
    'showCreateModal' => false,
    'showEditModal' => false,
    'editingServiceType' => null,
    'name' => '',
    'is_active' => true,
]);

$headers = [['key' => 'id', 'label' => '#', 'class' => 'w-16'], ['key' => 'name', 'label' => 'Name', 'sortable' => true], ['key' => 'slug', 'label' => 'Slug'], ['key' => 'boats_count', 'label' => 'Boats'], ['key' => 'is_active', 'label' => 'Status'], ['key' => 'actions', 'label' => 'Actions', 'class' => 'w-32']];

$openCreateModal = function () {
    $this->showCreateModal = true;
    $this->name = '';
    $this->is_active = true;
};

$create = function () {
    $validated = $this->validate([
        'name' => 'required|string|max:255|unique:boat_service_types,name',
        'is_active' => 'boolean',
    ]);

    BoatServiceType::create($validated);

    $this->success('Service type created successfully!');
    $this->showCreateModal = false;
    $this->name = '';
};

$openEditModal = function (BoatServiceType $serviceType) {
    $this->editingServiceType = $serviceType;
    $this->name = $serviceType->name;
    $this->is_active = $serviceType->is_active;
    $this->showEditModal = true;
};

$update = function () {
    if (!$this->editingServiceType) {
        return;
    }

    $validated = $this->validate([
        'name' => 'required|string|max:255|unique:boat_service_types,name,' . $this->editingServiceType->id,
        'is_active' => 'boolean',
    ]);

    $this->editingServiceType->update($validated);

    $this->success('Service type updated successfully!');
    $this->showEditModal = false;
    $this->editingServiceType = null;
};

$delete = function (BoatServiceType $serviceType) {
    if ($serviceType->boats()->count() > 0) {
        $this->error('Cannot delete service type with boats assigned to it.');
        return;
    }

    $serviceType->delete();
    $this->success('Service type deleted successfully!');
};

with(
    fn() => [
        'serviceTypes' => BoatServiceType::query()->withCount('boats')->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))->orderBy('name')->paginate($this->perPage),
        'headers' => $headers,
    ],
);

?>

<div>
    @php
        $breadcrumbs = [
            [
                'link' => route('admin.index'),
                'icon' => 's-home',
            ],
            [
                'label' => 'Boat Service Types',
            ],
        ];
    @endphp

    <x-header title="Boat Service Types" separator progress-indicator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Manage boat service types</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button label="Add Service Type" icon="o-plus" wire:click="openCreateModal" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        {{-- Filters --}}
        <div class="mb-6 flex gap-4">
            <x-input placeholder="Search service types..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" class="flex-1" />

            <x-select wire:model.live="perPage" :options="[
                ['id' => 10, 'name' => '10 per page'],
                ['id' => 25, 'name' => '25 per page'],
                ['id' => 50, 'name' => '50 per page'],
            ]" />
        </div>

        {{-- Table --}}
        <x-table :headers="$headers" :rows="$serviceTypes" with-pagination>
            @scope('cell_name', $serviceType)
                <div class="font-semibold">{{ $serviceType->name }}</div>
            @endscope

            @scope('cell_slug', $serviceType)
                <code class="text-xs bg-base-200 px-2 py-1 rounded">{{ $serviceType->slug }}</code>
            @endscope

            @scope('cell_boats_count', $serviceType)
                <x-badge :value="$serviceType->boats_count" class="badge-ghost" />
            @endscope

            @scope('cell_is_active', $serviceType)
                <x-badge :value="$serviceType->is_active ? 'Active' : 'Inactive'" class="{{ $serviceType->is_active ? 'badge-success' : 'badge-error' }}" />
            @endscope

            @scope('cell_actions', $serviceType)
                <div class="flex gap-2">
                    <x-button icon="o-pencil" wire:click="openEditModal({{ $serviceType->id }})" class="btn-sm btn-ghost"
                        tooltip="Edit" />
                    <x-button icon="o-trash" wire:click="delete({{ $serviceType->id }})"
                        wire:confirm="Are you sure you want to delete this service type?"
                        class="btn-sm btn-ghost text-error" tooltip="Delete" spinner />
                </div>
            @endscope
        </x-table>
    </x-card>

    {{-- Create Modal --}}
    <x-modal wire:model="showCreateModal" title="Add Service Type" class="backdrop-blur">
        <x-form wire:submit="create">
            <div class="space-y-4">
                <x-input label="Service Type Name *" wire:model="name" icon="o-tag"
                    placeholder="e.g., Marina Trip, Taxi, Ferry..." />

                <x-checkbox label="Active" wire:model="is_active" />
            </div>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.showCreateModal = false" />
                <x-button label="Create" type="submit" icon="o-check" class="btn-primary" spinner="create" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Edit Modal --}}
    <x-modal wire:model="showEditModal" title="Edit Service Type" class="backdrop-blur">
        <x-form wire:submit="update">
            <div class="space-y-4">
                <x-input label="Service Type Name *" wire:model="name" icon="o-tag"
                    placeholder="e.g., Marina Trip, Taxi, Ferry..." />

                <x-checkbox label="Active" wire:model="is_active" />
            </div>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.showEditModal = false" />
                <x-button label="Update" type="submit" icon="o-check" class="btn-primary" spinner="update" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
