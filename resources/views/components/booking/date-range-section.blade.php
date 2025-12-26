@props([
    'stepNumber' => '1',
    'checkInLabel' => 'Check In',
    'checkOutLabel' => 'Check Out',
    'checkInHint' => 'Check-in must be today or later',
    'checkOutHint' => 'Check-out must be after check-in',
    'minCheckInDate' => null,
    'checkIn' => 'check_in',
    'checkOut' => 'check_out',
    'dateRangeModel' => 'date_range',
    'bookedDates' => [],
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
    <div class="mt-6">
        <x-datepicker label="Select Date Range ({{ $checkInLabel }} to {{ $checkOutLabel }})"
            wire:model.live="{{ $dateRangeModel }}" icon="o-calendar" :config="[
                'mode' => 'range',
                'dateFormat' => 'Y-m-d',
                'altInput' => true,
                'altFormat' => 'M d, Y',
                'minDate' => 'today',
                'disable' => $bookedDates,
                'conjunction' => ' to ',
                'allowInput' => false,
                'clickOpens' => true,
            ]"
            hint="📅 Select {{ strtolower($checkInLabel) }} and {{ strtolower($checkOutLabel) }} dates. Time will be set automatically." />
    </div>
</x-card>
