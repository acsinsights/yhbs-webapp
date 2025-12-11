@props([
    'adults' => 1,
    'children' => 0,
    'checkInDate' => null,
    'checkOutDate' => null,
    'checkInLabel' => 'Check In',
    'checkOutLabel' => 'Check Out',
    'windowLabel' => 'Booking Window',
    'amount' => null,
    'paymentMethod' => 'cash',
    'paymentStatus' => 'pending',
    // Checklist props
    'showChecklist' => false,
    'customerSelected' => false,
    'selectionSelected' => false,
    'selectionLabel' => 'House',
    'amountFilled' => false,
    'paymentMethodSelected' => false,
    'paymentStatusSelected' => false,
    // Info message props
    'showInfoMessage' => false,
    'infoTitle' => 'Booking Information',
    'infoMessage' => '',
])

<div class="space-y-4">
    <div class="bg-base-200 p-4 rounded-2xl">
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-base-300/60">
            <div>
                <p class="text-xs uppercase tracking-wider text-primary font-bold">Live Summary</p>
                <h4 class="text-lg font-bold text-base-content">Booking Overview</h4>
            </div>
            <div class="w-10 h-10 rounded-lg bg-primary/10 flex items-center justify-center">
                <x-icon name="o-clipboard-document-check" class="w-5 h-5 text-primary" />
            </div>
        </div>

        <div class="space-y-2.5">
            {{-- Date Window --}}
            <div class="bg-base-100/80 rounded-lg p-2.5 border border-base-300/50">
                <div class="flex items-start gap-2">
                    <div class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center shrink-0">
                        <x-icon name="o-calendar" class="w-4 h-4 text-primary" />
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-semibold text-base-content/60 mb-1">{{ $windowLabel }}</p>
                        @if ($checkInDate && $checkOutDate)
                            <div class="space-y-1 flex justify-around">
                                <div>
                                    <p class="text-xs font-semibold text-primary mb-0.5">{{ $checkInLabel }}</p>
                                    <p class="text-xs font-semibold text-base-content">
                                        {{ $checkInDate->format('M d, Y') }},
                                    </p>
                                    <p class="text-xs font-semibold text-base-content">
                                        {{ $checkInDate->format('g:i A') }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-primary mb-0.5">{{ $checkOutLabel }}</p>
                                    <p class="text-xs font-semibold text-base-content">
                                        {{ $checkOutDate->format('M d, Y') }},
                                    </p>
                                    <p class="text-xs font-semibold text-base-content">
                                        {{ $checkOutDate->format('g:i A') }}
                                    </p>
                                </div>
                            </div>
                        @else
                            <p class="text-xs text-base-content/50 italic">Select dates</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Guests --}}
            <div class="bg-base-100/80 rounded-lg p-2.5 border border-base-300/50">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center shrink-0">
                        <x-icon name="o-user-group" class="w-4 h-4 text-primary" />
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-base-content/60 mb-0.5">Guests</p>
                        <p class="text-sm font-bold text-base-content">
                            {{ $adults + $children }}
                            <span class="text-xs font-normal text-base-content/70">
                                ({{ $adults }}A{{ $children > 0 ? ', ' . $children . 'C' : '' }})
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            {{-- Selection Slot (for room/house/yacht) --}}
            {{ $selection ?? '' }}

            {{-- Extra Sections Slot (for price breakdown, discounts, etc.) --}}
            {{ $extraSections ?? '' }}

            {{-- Amount --}}
            <div class="bg-gradient-to-br from-primary/10 to-primary/5 rounded-lg p-2.5 border-2 border-primary/20"
                wire:key="summary-amount-{{ $amount }}">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-md bg-primary/20 flex items-center justify-center shrink-0">
                        <x-icon name="o-currency-dollar" class="w-4 h-4 text-primary" />
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-primary/80 mb-0.5">Total Amount</p>
                        <p class="text-lg font-bold text-primary">
                            {{ $amount ? currency_format($amount) : '—' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Payment Details --}}
            <div class="bg-base-100/80 rounded-lg p-2.5 border border-base-300/50">
                <div class="flex items-start gap-2">
                    <div class="w-7 h-7 rounded-md bg-primary/10 flex items-center justify-center shrink-0">
                        <x-icon name="o-credit-card" class="w-4 h-4 text-primary" />
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-base-content/60 mb-1.5">Payment</p>
                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-base-content/70">Method</span>
                                <span
                                    class="text-xs font-semibold text-base-content capitalize">{{ $paymentMethod }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-base-content/70">Status</span>
                                <span
                                    class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $paymentStatus === 'paid' ? 'bg-success/20 text-success border border-success/30' : 'bg-warning/20 text-warning border border-warning/30' }}">
                                    {{ ucfirst($paymentStatus) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Checklist --}}
    @if ($showChecklist)
        <div class="bg-base-200 p-4 rounded-2xl">
            <div class="p-4 bg-base-100 rounded-lg">
                <p class="text-xs uppercase tracking-wide text-base-content/60">Checklist</p>
                <ul class="mt-2 space-y-2 text-sm">
                    <li class="flex items-center gap-2">
                        <span
                            class="w-2.5 h-2.5 rounded-full transition-colors duration-200 {{ $customerSelected ? 'bg-success' : 'bg-base-400' }}"></span>
                        <span
                            class="{{ $customerSelected ? 'text-success font-medium' : 'text-base-content/70' }}">Customer
                            selected</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span
                            class="w-2.5 h-2.5 rounded-full transition-colors duration-200 {{ $selectionSelected ? 'bg-success' : 'bg-base-400' }}"></span>
                        <span
                            class="{{ $selectionSelected ? 'text-success font-medium' : 'text-base-content/70' }}">{{ $selectionLabel }}
                            selected</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span
                            class="w-2.5 h-2.5 rounded-full transition-colors duration-200 {{ $amountFilled ? 'bg-success' : 'bg-base-400' }}"></span>
                        <span class="{{ $amountFilled ? 'text-success font-medium' : 'text-base-content/70' }}">Amount
                            filled</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span
                            class="w-2.5 h-2.5 rounded-full transition-colors duration-200 {{ $paymentMethodSelected ? 'bg-success' : 'bg-base-400' }}"></span>
                        <span
                            class="{{ $paymentMethodSelected ? 'text-success font-medium' : 'text-base-content/70' }}">Payment
                            method selected</span>
                    </li>
                    <li class="flex items-center gap-2">
                        <span
                            class="w-2.5 h-2.5 rounded-full transition-colors duration-200 {{ $paymentStatusSelected ? 'bg-success' : 'bg-base-400' }}"></span>
                        <span
                            class="{{ $paymentStatusSelected ? 'text-success font-medium' : 'text-base-content/70' }}">Payment
                            status selected</span>
                    </li>
                </ul>
            </div>

            {{-- Info Message --}}
            @if ($showInfoMessage && $infoMessage)
                <div class="rounded-2xl mt-6 border border-dashed border-base-300 bg-base-100 p-5">
                    <p class="text-sm font-semibold text-base-content">{{ $infoTitle }}</p>
                    <p class="text-sm text-base-content/60 mt-1">{{ $infoMessage }}</p>
                </div>
            @endif
        </div>
    @endif
</div>
