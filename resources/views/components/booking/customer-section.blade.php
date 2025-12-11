@props([
    'stepNumber' => '2',
    'customers' => [],
])

<x-card class="bg-base-200">
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-wide text-primary font-semibold">Step {{ $stepNumber }}</p>
            <h3 class="text-xl font-semibold text-base-content mt-1">Customer Details</h3>
            <p class="text-sm text-base-content/60 mt-1">Select or create a customer for this booking</p>
        </div>
        <x-button type="button" icon="o-plus" label="New Customer" @click="$wire.createCustomerModal = true"
            class="btn-sm btn-primary" />
    </div>

    <div class="mt-6">
        <x-choices-offline wire:model.live="user_id" label="Select Customer" placeholder="Choose a customer"
            :options="$customers" icon="o-user" hint="Select existing customer or create a new one" single clearable
            searchable>
            @scope('item', $customer)
                <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-base-200/50 transition-colors">
                    <div class="shrink-0">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-base mb-1 truncate">{{ $customer->name }}</div>
                        <div class="text-xs text-base-content/60 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="truncate">{{ $customer->email }}</span>
                        </div>
                    </div>
                </div>
            @endscope
            @scope('selection', $customer)
                {{ $customer->name }}
            @endscope>
        </x-choices-offline>
    </div>
</x-card>
