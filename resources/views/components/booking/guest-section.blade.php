@props([
    'stepNumber' => '3',
    'maxAdults',
    'maxChildren',
    'adults' => 1,
    'children' => 0,
])

<x-card class="bg-base-200">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step {{ $stepNumber }}</p>
            <h3 class="text-xl font-semibold text-base-content mt-1">Guest Details</h3>
            <p class="text-sm text-base-content/60 mt-1">Number of guests and their names</p>
        </div>
        <x-icon name="o-user-group" class="w-8 h-8 text-primary/70" />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
        <x-input wire:model.live.debounce.350ms="adults" label="Adults" type="number" min="1" icon="o-user-group"
            :max="$maxAdults" />
        <x-input wire:model.live.debounce.350ms="children" label="Children" type="number" min="0"
            icon="o-face-smile" :max="$maxChildren" />
    </div>

    {{-- Adult Names --}}
    @if ($adults > 0)
        <div class="mt-6">
            <h4 class="text-sm font-semibold text-base-content mb-3 flex items-center gap-2">
                <x-icon name="o-user" class="w-4 h-4" />
                Adult Names
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @for ($i = 0; $i < $adults; $i++)
                    <x-input wire:model="adultNames.{{ $i }}" label="Adult {{ $i + 1 }} Name"
                        placeholder="Enter full name" icon="o-user" />
                @endfor
            </div>
        </div>
    @endif

    {{-- Children Names --}}
    @if ($children > 0)
        <div class="mt-6">
            <h4 class="text-sm font-semibold text-base-content mb-3 flex items-center gap-2">
                <x-icon name="o-face-smile" class="w-4 h-4" />
                Children Names
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @for ($i = 0; $i < $children; $i++)
                    <x-input wire:model="childrenNames.{{ $i }}" label="Child {{ $i + 1 }} Name"
                        placeholder="Enter full name (optional)" icon="o-user" />
                @endfor
            </div>
        </div>
    @endif
</x-card>
