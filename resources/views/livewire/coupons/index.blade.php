<?php

use Mary\Traits\Toast;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use App\Models\Coupon;
use App\Enums\DiscountTypeEnum;

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public int $perPage = 10;

    // Delete action
    public function delete($id): void
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        $this->success('Coupon deleted successfully.');
    }

    // Toggle active status
    public function toggleActive($id): void
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);

        $this->success($coupon->is_active ? 'Coupon activated' : 'Coupon deactivated');
    }

    // Headers
    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#', 'sortable' => true], ['key' => 'code', 'label' => 'Code', 'sortable' => true], ['key' => 'name', 'label' => 'Name', 'sortable' => true], ['key' => 'discount_type', 'label' => 'Type'], ['key' => 'discount_value', 'label' => 'Value'], ['key' => 'valid_from', 'label' => 'Valid From', 'sortable' => true], ['key' => 'valid_until', 'label' => 'Valid Until', 'sortable' => true], ['key' => 'usage_count', 'label' => 'Used/Limit'], ['key' => 'is_active', 'label' => 'Status'], ['key' => 'actions', 'label' => 'Actions', 'sortable' => false]];
    }

    public function coupons()
    {
        return Coupon::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('code', 'like', '%' . $this->search . '%')
                        ->orWhere('name', 'like', '%' . $this->search . '%')
                        ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'coupons' => $this->coupons(),
            'headers' => $this->headers(),
        ];
    }
}; ?>

<div>
    <x-header title="Coupons" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="New Coupon" :link="route('admin.coupons.create')" icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- Coupons Table -->
    <x-card>
        <x-table :headers="$headers" :rows="$coupons" :sort-by="$sortBy" with-pagination>
            @scope('cell_code', $coupon)
                <span class="font-mono font-bold text-primary">{{ $coupon->code }}</span>
            @endscope

            @scope('cell_discount_type', $coupon)
                <x-badge :value="$coupon->discount_type->label()"
                    class="{{ $coupon->discount_type->value === 'percentage' ? 'badge-info' : 'badge-success' }}" />
            @endscope

            @scope('cell_discount_value', $coupon)
                @if ($coupon->discount_type->value === 'percentage')
                    {{ $coupon->discount_value }}%
                @else
                    {{ number_format($coupon->discount_value, 3) }} KWD
                @endif
            @endscope

            @scope('cell_valid_from', $coupon)
                {{ $coupon->valid_from->format('d M Y') }}
            @endscope

            @scope('cell_valid_until', $coupon)
                {{ $coupon->valid_until->format('d M Y') }}
            @endscope

            @scope('cell_usage_count', $coupon)
                <span
                    class="{{ $coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit ? 'text-error' : 'text-success' }}">
                    {{ $coupon->usage_count }} / {{ $coupon->usage_limit ?? '∞' }}
                </span>
            @endscope

            @scope('cell_is_active', $coupon)
                <x-toggle wire:model="is_active" wire:click="toggleActive({{ $coupon->id }})" :checked="$coupon->is_active"
                    class="toggle-sm" />
            @endscope

            @scope('cell_actions', $coupon)
                <div class="flex gap-2">
                    <x-button icon="o-pencil" :link="route('admin.coupons.edit', $coupon->id)" spinner class="btn-sm btn-ghost" />
                    <x-button icon="o-trash" wire:click="delete({{ $coupon->id }})"
                        wire:confirm="Are you sure you want to delete this coupon?" spinner
                        class="btn-sm btn-ghost text-error" />
                </div>
            @endscope
        </x-table>
    </x-card>
</div>
