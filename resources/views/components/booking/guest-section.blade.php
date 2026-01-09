@props([
    'stepNumber' => '3',
    'maxAdults',
    'maxChildren',
    'adults' => 1,
    'children' => 0,
    'guests' => [],
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
    <div class="grid grid-cols-1 gap-4 mt-6">
        <x-input wire:model.live.debounce.350ms="adults" label="Number of Guests" type="number" min="1"
            icon="o-user-group" :max="$maxAdults" hint="Maximum: {{ $maxAdults }} guests" />
    </div>

    {{-- Guest Names --}}
    @if ($adults > 0)
        <div class="mt-6">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2">
                    <x-icon name="o-user" class="w-4 h-4" />
                    <h4 class="text-sm font-semibold text-base-content">Guest Details</h4>
                </div>
                @if (count($guests) < $adults)
                    <button type="button" wire:click="addGuest" class="btn btn-sm btn-primary">
                        <x-icon name="o-plus" class="w-4 h-4" />
                        Add Guest ({{ count($guests) }}/{{ $adults }})
                    </button>
                @endif
            </div>
            <p class="text-xs text-base-content/60 mb-3">You can add up to {{ $adults }} guest(s). First guest
                details are required.</p>
            <div class="space-y-4">
                @foreach ($guests as $i => $guest)
                    <div wire:key="guest-{{ $i }}" class="p-4 border border-base-300 rounded-lg bg-base-100">
                        <div class="flex items-center justify-between mb-3">
                            <h5 class="font-semibold text-sm">Guest {{ $i + 1 }}{{ $i === 0 ? ' *' : '' }}</h5>
                        </div>
                        <div class="grid grid-cols-1 gap-3">
                            <x-input wire:model="guests.{{ $i }}.name"
                                label="Full Name{{ $i === 0 ? ' *' : '' }}" placeholder="Enter guest full name"
                                icon="o-user" />
                            <x-input wire:model="guests.{{ $i }}.email" label="Email (Optional)"
                                type="email" placeholder="Enter guest email" icon="o-envelope" />
                            <x-input wire:model="guests.{{ $i }}.phone" label="Phone Number (Optional)"
                                type="tel" placeholder="Enter guest phone" icon="o-phone" />
                        </div>
                    </div>
                @endforeach
            </div>
            @if (count($guests) < $adults)
                <div class="mt-4">
                    <button type="button" wire:click="addGuest" class="btn btn-sm btn-primary w-full">
                        <x-icon name="o-plus" class="w-4 h-4" />
                        Add Guest ({{ count($guests) }}/{{ $adults }})
                    </button>
                </div>
            @endif
        </div>
    @endif
</x-card>
