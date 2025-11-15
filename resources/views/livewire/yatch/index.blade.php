<?php

use App\Models\Yatch;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;
use Illuminate\Support\Str;

new class extends Component {
    use Toast;

    #[Title('Yachts')]
    public string $search = '';
    public bool $drawer = false;
    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    // Delete action
    public function delete($id): void
    {
        $yatch = Yatch::findOrFail($id);

        // Delete image if exists
        if ($yatch->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('/storage/', '', $yatch->image));
        }

        $yatch->delete();
        $this->success('Yacht deleted successfully.', position: 'toast-bottom');
    }

    // Table headers
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#', 'class' => 'w-16'], ['key' => 'image', 'label' => 'Image', 'class' => 'w-24', 'sortable' => false], ['key' => 'name', 'label' => 'Name', 'class' => 'w-64'], ['key' => 'sku', 'label' => 'SKU', 'class' => 'w-32'], ['key' => 'price', 'label' => 'Price', 'class' => 'w-32'], ['key' => 'discount_price', 'label' => 'Discount Price', 'class' => 'w-32'], ['key' => 'created_at', 'label' => 'Created', 'class' => 'w-40']];
    }

    public function yatches()
    {
        return Yatch::query()
            ->when($this->search, function ($query) {
                return $query
                    ->where('name', 'like', "%{$this->search}%")
                    ->orWhere('sku', 'like', "%{$this->search}%")
                    ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10)
            ->through(function ($yatch) {
                return [
                    'id' => $yatch->id,
                    'image' => $yatch->image,
                    'name' => $yatch->name,
                    'sku' => $yatch->sku ?? 'N/A',
                    'price' => $yatch->price ? '$' . number_format($yatch->price, 2) : 'N/A',
                    'discount_price' => $yatch->discount_price ? '$' . number_format($yatch->discount_price, 2) : 'N/A',
                    'created_at' => $yatch->created_at->format('M d, Y'),
                ];
            });
    }

    public function stats()
    {
        return [
            'total' => Yatch::count(),
            'with_discount' => Yatch::whereNotNull('discount_price')->count(),
            'total_value' => Yatch::sum('price') ?? 0,
        ];
    }

    public function with(): array
    {
        return [
            'yatches' => $this->yatches(),
            'headers' => $this->headers(),
            'stats' => $this->stats(),
        ];
    }
}; ?>

<div>
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
        <x-card class="bg-gradient-to-br from-primary/10 to-primary/5 border-primary/20">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-base-content/60 mb-1">Total Yachts</div>
                    <div class="text-3xl font-bold text-primary">{{ $stats['total'] }}</div>
                </div>
                <div class="p-3 rounded-full bg-primary/20">
                    <x-icon name="o-sparkles" class="w-8 h-8 text-primary" />
                </div>
            </div>
        </x-card>

        <x-card class="bg-gradient-to-br from-success/10 to-success/5 border-success/20">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-base-content/60 mb-1">On Discount</div>
                    <div class="text-3xl font-bold text-success">{{ $stats['with_discount'] }}</div>
                </div>
                <div class="p-3 rounded-full bg-success/20">
                    <x-icon name="o-tag" class="w-8 h-8 text-success" />
                </div>
            </div>
        </x-card>

        <x-card class="bg-gradient-to-br from-warning/10 to-warning/5 border-warning/20">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-sm font-medium text-base-content/60 mb-1">Total Value</div>
                    <div class="text-2xl font-bold text-warning">${{ number_format($stats['total_value'], 2) }}</div>
                </div>
                <div class="p-3 rounded-full bg-warning/20">
                    <x-icon name="o-currency-dollar" class="w-8 h-8 text-warning" />
                </div>
            </div>
        </x-card>
    </div>

    <!-- HEADER -->
    <x-header title="Yachts" separator progress-indicator class="mb-6">
        <x-slot:middle class="justify-end">
            <x-input placeholder="Search yachts..." wire:model.live.debounce="search" clearable
                icon="o-magnifying-glass" class="w-64" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Create Yacht" link="{{ route('admin.yatch.create') }}" icon="o-plus-circle"
                class="btn-primary shadow-lg hover:shadow-xl transition-all duration-300" responsive />
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel"
                class="btn-outline hover:btn-primary transition-all duration-300" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow class="overflow-hidden border-2 border-base-300/50">
        <div class="bg-gradient-to-r from-primary/5 to-primary/10 p-4 border-b border-base-300">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <x-icon name="o-table-cells" class="w-5 h-5 text-primary" />
                Yacht Listings
            </h3>
        </div>
        <x-table :headers="$headers" :rows="$yatches" :sort-by="$sortBy" with-pagination class="hover">
            @scope('cell_image', $yatch)
                <div class="flex items-center">
                    @if ($yatch['image'])
                        <div class="relative group">
                            <img src="{{ asset($yatch['image']) }}" alt="{{ $yatch['name'] }}"
                                class="w-20 h-20 object-cover rounded-xl shadow-md hover:shadow-xl transition-all duration-300 hover:scale-105 border-2 border-base-300">
                            <div
                                class="absolute inset-0 bg-primary/0 hover:bg-primary/10 rounded-xl transition-all duration-300">
                            </div>
                        </div>
                    @else
                        <div
                            class="w-20 h-20 bg-gradient-to-br from-base-300 to-base-200 rounded-xl flex items-center justify-center shadow-inner border-2 border-base-300">
                            <x-icon name="o-photo" class="w-10 h-10 text-base-content/30" />
                        </div>
                    @endif
                </div>
            @endscope

            @scope('cell_name', $yatch)
                <div class="font-semibold text-base-content">{{ $yatch['name'] }}</div>
            @endscope

            @scope('cell_price', $yatch)
                @if ($yatch['price'] !== 'N/A')
                    <div class="font-bold text-primary text-lg">{{ $yatch['price'] }}</div>
                @else
                    <span class="badge badge-ghost">N/A</span>
                @endif
            @endscope

            @scope('cell_discount_price', $yatch)
                @if ($yatch['discount_price'] !== 'N/A')
                    <div class="font-bold text-success text-lg">{{ $yatch['discount_price'] }}</div>
                    <div class="badge badge-success badge-sm mt-1">Discount</div>
                @else
                    <span class="badge badge-ghost">N/A</span>
                @endif
            @endscope

            @scope('cell_sku', $yatch)
                <span class="font-mono text-sm bg-base-200 px-2 py-1 rounded">{{ $yatch['sku'] }}</span>
            @endscope

            @scope('actions', $yatch)
                <div class="flex gap-2">
                    <x-button icon="o-eye" link="{{ route('admin.yatch.show', $yatch['id']) }}"
                        class="btn-ghost btn-sm hover:btn-primary transition-all duration-300 tooltip"
                        tooltip="View Details" />
                    <x-button icon="o-pencil" link="{{ route('admin.yatch.edit', $yatch['id']) }}"
                        class="btn-ghost btn-sm hover:btn-warning transition-all duration-300 tooltip"
                        tooltip="Edit Yacht" />
                    <x-button icon="o-trash" wire:click="delete({{ $yatch['id'] }})"
                        wire:confirm="Are you sure you want to delete this yacht?" spinner
                        class="btn-ghost btn-sm hover:btn-error text-error transition-all duration-300 tooltip"
                        tooltip="Delete Yacht" />
                </div>
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <div class="space-y-4">
            <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass"
                @keydown.enter="$wire.drawer = false" />

            <div class="divider">Quick Filters</div>

            <div class="space-y-2">
                <x-button label="All Yachts" wire:click="clear" class="btn-ghost w-full justify-start" />
                <x-button label="With Discount" class="btn-ghost w-full justify-start" />
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner
                class="btn-ghost hover:btn-error transition-all duration-300" />
            <x-button label="Done" icon="o-check"
                class="btn-primary shadow-lg hover:shadow-xl transition-all duration-300"
                @click="$wire.drawer = false" />
        </x-slot:actions>
    </x-drawer>
</div>
