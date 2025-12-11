@props([
    'stepNumber' => '6',
])

<x-card class="bg-base-200">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step {{ $stepNumber }}</p>
            <h3 class="text-xl font-semibold text-base-content mt-1">Additional Notes</h3>
            <p class="text-sm text-base-content/60 mt-1">Any special requests or additional information</p>
        </div>
        <x-icon name="o-document-text" class="w-8 h-8 text-primary/70" />
    </div>
    <div class="mt-6">
        <x-textarea wire:model="notes" label="Notes" placeholder="Additional notes (optional)" icon="o-document-text"
            rows="3" />
    </div>
</x-card>
