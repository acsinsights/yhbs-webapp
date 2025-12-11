<x-modal wire:model="createCustomerModal" title="Create New Customer" class="backdrop-blur" max-width="md">
    <x-form wire:submit="createCustomer">
        <div class="space-y-4">
            <x-input wire:model="customer_name" label="Customer Name" placeholder="Enter customer name" icon="o-user"
                hint="Full name of the customer" />
            <x-input wire:model="customer_email" label="Email" type="email" placeholder="Enter email address"
                icon="o-envelope" hint="Unique email address" />
        </div>

        <x-slot:actions>
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                <x-button icon="o-x-mark" label="Cancel" @click="$wire.createCustomerModal = false"
                    class="btn-ghost w-full sm:w-auto" responsive />
                <x-button icon="o-check" label="Create Customer" type="submit" class="btn-primary w-full sm:w-auto"
                    spinner="createCustomer" responsive />
            </div>
        </x-slot:actions>
    </x-form>
</x-modal>
