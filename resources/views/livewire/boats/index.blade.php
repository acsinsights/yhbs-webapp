<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Boat;
use Illuminate\Support\Str;

new class extends Component {
    use Toast, WithPagination;

    public string $search = '';
    public string $service_type_filter = '';
    public bool $showCreateModal = false;

    // Create form fields
    public string $name = '';

    public function with(): array
    {
        $query = Boat::query()->orderBy('sort_order')->orderBy('name');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->service_type_filter) {
            $query->where('service_type', $this->service_type_filter);
        }

        $boats = $query->paginate(10);

        return [
            'boats' => $boats,
            'headers' => [['key' => 'id', 'label' => '#'], ['key' => 'name', 'label' => 'Name'], ['key' => 'service_type', 'label' => 'Service Type'], ['key' => 'max_passengers', 'label' => 'Capacity'], ['key' => 'price', 'label' => 'Price'], ['key' => 'is_active', 'label' => 'Status'], ['key' => 'actions', 'label' => 'Actions']],
        ];
    }

    public function create(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $boat = Boat::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'service_type' => 'marina_trip', // Default
            'min_passengers' => 1,
            'max_passengers' => 10,
            'is_active' => false, // Inactive until fully configured
        ]);

        $this->reset(['name']);
        $this->showCreateModal = false;
        $this->success('Boat created successfully. Complete the details below.', redirectTo: route('admin.boats.edit', $boat));
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
    <x-header title="Boats & Marine Services" separator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search boats..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Add Boat" icon="o-plus" class="btn-primary" @click="$wire.showCreateModal = true" />
        </x-slot:actions>
    </x-header>

    {{-- Filters --}}
    <div class="grid gap-4 lg:grid-cols-4 mb-5">
        <x-select label="Service Type" :options="[
            ['value' => '', 'label' => 'All Types'],
            ['value' => 'marina_trip', 'label' => 'Marina Trips'],
            ['value' => 'taxi', 'label' => 'Private Taxi'],
            ['value' => 'ferry', 'label' => 'Ferry Services'],
            ['value' => 'limousine', 'label' => 'Limousine Services'],
        ]" wire:model.live="service_type_filter" />
    </div>

    {{-- Boats Table --}}
    <x-card>
        <x-table :headers="$headers" :rows="$boats" with-pagination>
            @scope('cell_name', $boat)
                <div class="flex items-center gap-3">
                    @if ($boat->image)
                        <x-avatar :image="asset('storage/' . $boat->image)" class="!w-12" />
                    @else
                        <x-avatar class="!w-12">
                            <x-icon name="o-building-office-2" class="w-6 h-6" />
                        </x-avatar>
                    @endif
                    <div>
                        <div class="font-bold">{{ $boat->name }}</div>
                        <div class="text-sm text-gray-500">{{ $boat->location }}</div>
                    </div>
                </div>
            @endscope

            @scope('cell_service_type', $boat)
                <x-badge :value="$boat->service_type_label" class="badge-primary" />
            @endscope

            @scope('cell_max_passengers', $boat)
                {{ $boat->min_passengers }}-{{ $boat->max_passengers }} passengers
            @endscope

            @scope('cell_price', $boat)
                <span class="font-semibold">{{ $boat->display_price }}</span>
            @endscope

            @scope('cell_is_active', $boat)
                @if ($boat->is_active)
                    <x-badge value="Active" class="badge-success" />
                @else
                    <x-badge value="Inactive" class="badge-error" />
                @endif
            @endscope

            @scope('cell_actions', $boat)
                <div class="flex gap-2">
                    <x-button icon="o-eye" link="{{ route('admin.boats.show', $boat->id) }}" class="btn-ghost btn-sm"
                        tooltip="View" />
                    <x-button icon="o-pencil" link="{{ route('admin.boats.edit', $boat->id) }}" class="btn-ghost btn-sm"
                        tooltip="Edit" />
                    <x-button icon="o-trash" wire:click="delete({{ $boat->id }})"
                        wire:confirm="Are you sure you want to delete this boat?" class="btn-ghost btn-sm text-error"
                        tooltip="Delete" />
                </div>
            @endscope
        </x-table>
    </x-card>

    {{-- Create Modal --}}
    <x-modal wire:model="showCreateModal" title="Add New Boat" class="backdrop-blur">
        <x-alert icon="o-information-circle" class="alert-info mb-4">
            Enter the boat name to get started. You'll be redirected to configure all details.
        </x-alert>

        <x-input label="Boat Name *" wire:model="name" placeholder="e.g., Marina 1, VIP Limousine" icon="o-archive-box"
            hint="You can edit all details in the next step" />

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.showCreateModal = false" />
            <x-button label="Create & Continue" class="btn-primary" wire:click="create" spinner="create" />
        </x-slot:actions>
    </x-modal>
</div>
