<?php

use App\Models\Yatch;
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

    public bool $createModal = false;
    public string $name = '';

    // Create yacht with just name
    public function createYacht(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
        ]);

        $yatch = Yatch::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
        ]);

        $this->createModal = false;
        $this->reset('name');
        $this->success('Yacht created successfully.', redirectTo: route('admin.yatch.edit', $yatch));
    }

    // Delete action
    public function delete($id): void
    {
        $yatch = Yatch::findOrFail($id);
        $yatch->delete();

        $this->success('Yacht deleted successfully.');
    }

    public function rendering(View $view)
    {
        $view->yatches = Yatch::query()
            ->when($this->search, function ($query) {
                return $query
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%")
                    ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);

        $view->headers = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'name', 'label' => 'Name', 'sortable' => true], ['key' => 'sku', 'label' => 'SKU', 'sortable' => true], ['key' => 'price', 'label' => 'Price', 'sortable' => true], ['key' => 'discount_price', 'label' => 'Discount Price', 'sortable' => true]];
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
                'label' => 'Yachts',
                'icon' => 'o-home-modern',
            ],
        ];
    @endphp

    <x-header title="Yachts" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Manage all yachts</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live.debounce="search" clearable />
            <x-button icon="o-plus" class="btn-primary" tooltip="Add Yacht" @click="$wire.createModal = true" />
            <x-button icon="o-funnel" label="Filters" responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-table :headers="$headers" :rows="$yatches" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[10, 25, 50, 100]">
            @scope('cell_name', $yatch)
                <x-badge :value="$yatch->name" class="badge-soft badge-primary" />
            @endscope

            @scope('cell_price', $yatch)
                <div class="font-semibold line-through">
                    {{ number_format($yatch->price ?? 0, 2) }}
                </div>
            @endscope

            @scope('cell_discount_price', $yatch)
                @if ($yatch->discount_price)
                    <div class="flex items-center gap-2">
                        <div class="font-semibold text-success">
                            {{ number_format($yatch->discount_price, 2) }}
                        </div>

                        <div>
                            <x-badge :value="number_format(
                                (($yatch->price - $yatch->discount_price) / $yatch->price) * 100,
                                2,
                            ) . '% off'" class="badge-soft badge-sm badge-error" />
                        </div>
                    </div>
                @else
                    <span class="text-base-content/50">—</span>
                @endif
            @endscope

            @scope('cell_sku', $yatch)
                <span class="font-mono text-sm">{{ $yatch->sku ?? 'N/A' }}</span>
            @endscope

            @scope('actions', $yatch)
                <div class="flex items-center gap-2">
                    <x-button icon="o-eye" link="{{ route('admin.yatch.show', $yatch->id) }}" class="btn-ghost btn-sm"
                        tooltip="Show" />
                    <x-button icon="o-pencil" link="{{ route('admin.yatch.edit', $yatch->id) }}" class="btn-ghost btn-sm"
                        tooltip="Edit" />
                    <x-button icon="o-trash" wire:click="delete({{ $yatch->id }})"
                        wire:confirm="Are you sure you want to delete this yacht?" spinner
                        class="btn-ghost btn-sm text-error" tooltip="Delete" />
                </div>
            @endscope

            <x-slot:empty>
                <x-empty icon="o-sparkles" message="No yachts found" />
            </x-slot:empty>
        </x-table>
    </x-card>

    {{-- Create Yacht Modal --}}
    <x-modal wire:model="createModal" title="Create New Yacht" class="backdrop-blur" max-width="md">
        <x-form wire:submit="createYacht">
            <div class="space-y-4">
                <x-input wire:model="name" label="Yacht Name" placeholder="Enter yacht name" icon="o-tag"
                    hint="The slug will be auto-generated from the name" />
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" @click="$wire.createModal = false"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Create Yacht" type="submit" class="btn-primary w-full sm:w-auto"
                        spinner="createYacht" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
