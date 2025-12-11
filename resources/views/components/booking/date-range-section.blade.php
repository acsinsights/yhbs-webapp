@props([
    'stepNumber' => '1',
    'checkInLabel' => 'Check In',
    'checkOutLabel' => 'Check Out',
    'checkInHint' => 'Check-in must be today or later',
    'checkOutHint' => 'Check-out must be after check-in',
    'minCheckInDate' => null,
    'checkIn' => 'check_in',
    'checkOut' => 'check_out',
])

<x-card class="bg-base-200">
    <div class="flex items-start justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step {{ $stepNumber }}</p>
            <h3 class="text-xl font-semibold text-base-content mt-1">Booking Dates</h3>
            <p class="text-sm text-base-content/60 mt-1">Select your {{ strtolower($checkInLabel) }} and
                {{ strtolower($checkOutLabel) }} dates</p>
        </div>
        <x-icon name="o-calendar" class="w-8 h-8 text-primary/70" />
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
        <x-input wire:model.live.debounce.300ms="{{ $checkIn }}" :label="$checkInLabel" type="datetime-local"
            icon="o-calendar" :min="$minCheckInDate" :hint="$checkInHint" />
        <x-input wire:model.live.debounce.300ms="{{ $checkOut }}" :label="$checkOutLabel" type="datetime-local"
            icon="o-calendar" min="{{ $checkIn }}" :hint="$checkOutHint" />
    </div>
</x-card>
