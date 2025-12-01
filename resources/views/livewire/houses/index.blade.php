<?php

use Mary\Traits\Toast;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use App\Models\House;

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public int $perPage = 10;

    public bool $createModal = false;
    public string $name = '';

    // Create house with just name
    public function createHouse(): void
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:houses,name',
        ]);

        $slug = Str::slug($this->name);

        // Ensure slug is unique
        $originalSlug = $slug;
        $counter = 1;
        while (House::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $house = House::create([
            'name' => $this->name,
            'slug' => $slug,
        ]);

        $this->createModal = false;
        $this->reset('name');
        $this->success('House created successfully.', redirectTo: route('admin.houses.edit', $house));
    }

    public function rendering(View $view)
    {
        $view->houses = House::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);

        $view->headers = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'name', 'label' => 'Name', 'sortable' => true], ['key' => 'slug', 'label' => 'Slug', 'sortable' => true], ['key' => 'image', 'label' => 'Image', 'sortable' => false], ['key' => 'description', 'label' => 'Description', 'sortable' => false]];
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
                'label' => 'Houses',
                'icon' => 'o-building-office-2',
            ],
        ];
    @endphp

    <x-header title="Houses" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Manage house information</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>

        <x-slot:actions>
            <x-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live.debounce="search" clearable />
            <x-button icon="o-funnel" tooltip-left="Filters" class="btn-info" responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-table :headers="$headers" :rows="$houses" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[10, 25, 50, 100]">
            @scope('cell_name', $house)
                <x-badge :value="$house->name" class="badge-soft badge-primary" />
            @endscope

            @scope('cell_slug', $house)
                <code class="text-xs bg-base-200 px-2 py-1 rounded">{{ $house->slug }}</code>
            @endscope

            @scope('cell_image', $house)
                @if ($house->image)
                    <img src="{{ asset($house->image) }}" alt="{{ $house->name }}"
                        class="w-12 h-12 rounded object-cover" />
                @else
                    <img src="https://placehold.co/100x100" alt="{{ $house->name }}"
                        class="w-12 h-12 rounded object-cover" />
                @endif
            @endscope

            @scope('cell_description', $house)
                <div class="max-w-md">
                    <p class="text-sm line-clamp-2">{{ Str::limit($house->description ?? 'No description', 100) }}</p>
                </div>
            @endscope

            @scope('actions', $house)
                <div class="flex items-center gap-2">
                    <x-button icon="o-eye" link="{{ route('admin.houses.show', $house->id) }}" class="btn-ghost btn-sm"
                        tooltip="Show" />
                    <x-button icon="o-pencil" link="{{ route('admin.houses.edit', $house->id) }}" class="btn-ghost btn-sm"
                        tooltip="Edit" />
                </div>
            @endscope

            <x-slot:empty>
                <x-empty icon="o-building-office-2" message="No houses found" />
            </x-slot:empty>
        </x-table>
    </x-card>

    {{-- Create House Modal --}}
    <x-modal wire:model="createModal" title="Create New House" class="backdrop-blur" max-width="md">
        <x-form wire:submit="createHouse">
            <div class="space-y-4">
                <x-input wire:model="name" label="House Name" placeholder="Enter house name" icon="o-tag"
                    hint="The slug will be auto-generated from the name" />
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" @click="$wire.createModal = false"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Create House" type="submit" class="btn-primary w-full sm:w-auto"
                        spinner="createHouse" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
