<?php

use function Livewire\Volt\{state, usesFileUploads};

use App\Models\BoatServiceType;
use Mary\Traits\Toast;

usesFileUploads();

state(['name', 'description', 'icon', 'is_active' => true, 'sort_order' => 0]);

$save = function () {
    $validated = $this->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'icon' => 'nullable|string|max:255',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ]);

    BoatServiceType::create($validated);

    $this->success('Service type created successfully!', redirectTo: route('admin.boats.service-types.index'));
};

?>

<div>
    @php
        $breadcrumbs = [
            [
                'link' => route('admin.index'),
                'icon' => 's-home',
            ],
            [
                'link' => route('admin.boats.service-types.index'),
                'label' => 'Boat Service Types',
            ],
            [
                'label' => 'Create Service Type',
            ],
        ];
    @endphp

    <x-header title="Create Service Type" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Add a new boat service type</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.boats.service-types.index') }}"
                class="btn-ghost btn-outline" />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-form wire:submit="save">
            <div class="grid gap-6 lg:grid-cols-3">
                <div class="lg:col-span-2 space-y-4">
                    <x-input label="Service Type Name *" wire:model="name" icon="o-tag"
                        placeholder="e.g., Marina Trip, Taxi, Ferry..." />

                    <x-textarea label="Description" wire:model="description" rows="4"
                        placeholder="Brief description of this service type..." />

                    <x-input label="Icon" wire:model="icon" icon="o-sparkles" placeholder="e.g., o-archive-box"
                        hint="Use Heroicons outline icon name" />

                    <x-input label="Sort Order" type="number" wire:model="sort_order" icon="o-arrows-up-down"
                        hint="Lower numbers appear first" />
                </div>

                <div class="space-y-4">
                    <x-card title="Status" shadow>
                        <x-checkbox label="Active" wire:model="is_active" />
                    </x-card>
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Cancel" link="{{ route('admin.boats.service-types.index') }}" />
                <x-button label="Create Service Type" type="submit" icon="o-check" class="btn-primary"
                    spinner="save" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
