<?php

use App\Models\Hotel;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Illuminate\View\View;
use Illuminate\Support\Str;

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public int $perPage = 10;

    public function rendering(View $view)
    {
        $view->hotels = Hotel::query()
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
                'label' => 'Hotels',
                'icon' => 'o-building-office-2',
            ],
        ];
    @endphp

    <x-header title="Hotels" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Manage hotel information</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>

        <x-slot:actions>
            <x-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live.debounce="search" clearable />
            <x-button icon="o-funnel" tooltip-left="Filters" class="btn-info" responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-table :headers="$headers" :rows="$hotels" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[10, 25, 50, 100]">
            @scope('cell_name', $hotel)
                <x-badge :value="$hotel->name" class="badge-soft badge-primary" />
            @endscope

            @scope('cell_slug', $hotel)
                <code class="text-xs bg-base-200 px-2 py-1 rounded">{{ $hotel->slug }}</code>
            @endscope

            @scope('cell_image', $hotel)
                @if ($hotel->image)
                    <img src="{{ asset($hotel->image) }}" alt="{{ $hotel->name }}"
                        class="w-12 h-12 rounded object-cover" />
                @else
                    <img src="https://placehold.co/100x100" alt="{{ $hotel->name }}"
                        class="w-12 h-12 rounded object-cover" />
                @endif
            @endscope

            @scope('cell_description', $hotel)
                <div class="max-w-md">
                    <p class="text-sm line-clamp-2">{{ Str::limit($hotel->description ?? 'No description', 100) }}</p>
                </div>
            @endscope

            @scope('actions', $hotel)
                <div class="flex items-center gap-2">
                    <x-button icon="o-pencil" link="{{ route('admin.hotels.edit', $hotel->id) }}" class="btn-ghost btn-sm"
                        tooltip="Edit" />
                </div>
            @endscope

            <x-slot:empty>
                <x-empty icon="o-building-office-2" message="No hotels found" />
            </x-slot:empty>
        </x-table>
    </x-card>
</div>
