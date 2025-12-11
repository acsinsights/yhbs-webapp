@props([
    'stepNumber' => '3',
])

<x-card class="bg-base-200">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step {{ $stepNumber }}</p>
            <h3 class="text-xl font-semibold text-base-content mt-1">Guest Details</h3>
            <p class="text-sm text-base-content/60 mt-1">Number of guests for this booking</p>
        </div>
        <x-icon name="o-user-group" class="w-8 h-8 text-primary/70" />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
        <x-input wire:model.live.debounce.300ms="adults" label="Adults" type="number" min="1"
            icon="o-user-group" />
        <x-input wire:model.live.debounce.300ms="children" label="Children" type="number" min="0"
            icon="o-face-smile" />
    </div>
</x-card>
