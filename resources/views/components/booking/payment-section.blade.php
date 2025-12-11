@props([
    'stepNumber' => '5',
    'maxAmount' => 999999999.99,
])

<x-card class="bg-base-200">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step {{ $stepNumber }}</p>
            <h3 class="text-xl font-semibold text-base-content mt-1">Payment Details</h3>
            <p class="text-sm text-base-content/60 mt-1">Payment information for this booking</p>
        </div>
        <x-icon name="o-credit-card" class="w-8 h-8 text-primary/70" />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
        <x-input wire:model.live.debounce.350ms="amount" wire:change="$wire.updatedAmount()" label="Amount" type="number"
            step="0.01" min="0" :max="$maxAmount" icon="o-currency-dollar" :hint="'Total booking amount (auto-filled from price)'" />
        <x-select wire:model.live="payment_method" label="Payment Method" :options="[['id' => 'cash', 'name' => 'Cash'], ['id' => 'card', 'name' => 'Card']]" option-value="id"
            option-label="name" icon="o-credit-card" />
        <x-select wire:model.live="payment_status" label="Payment Status" :options="[['id' => 'paid', 'name' => 'Paid'], ['id' => 'pending', 'name' => 'Pending']]" option-value="id"
            option-label="name" icon="o-check-circle" />
    </div>
</x-card>
