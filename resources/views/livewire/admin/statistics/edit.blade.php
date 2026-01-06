<div>
    <x-header title="Edit Statistic: {{ $statistic->title }}" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Back" link="{{ route('admin.statistics.index') }}" icon="o-arrow-left" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="col-span-2">
                    <x-input label="Title" wire:model="title" placeholder="e.g., Tour Completed" />
                </div>

                <div>
                    <x-input label="Count" wire:model="count" placeholder="e.g., 26K+" />
                </div>

                <div>
                    <x-input label="Order" wire:model="order" type="number" min="0" />
                </div>

                <div class="col-span-2">
                    <x-file label="Icon Image" wire:model="new_icon" accept="image/*"
                        hint="Upload a new icon image (max 2MB)">
                        <img src="{{ $icon ? asset('storage/' . $icon) : 'https://placehold.co/100x100' }}"
                            alt="Statistic Icon" class="h-24 w-24 object-contain rounded p-2 border" />
                    </x-file>

                    @if ($new_icon)
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 mb-2">New Icon Preview:</p>
                            <img src="{{ $new_icon->temporaryUrl() }}"
                                class="h-16 w-16 object-contain border rounded p-2">
                        </div>
                    @endif
                </div>

                <div>
                    <x-toggle label="Active" wire:model="is_active" />
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Cancel" link="{{ route('admin.statistics.index') }}" />
                <x-button label="Save" type="submit" icon="o-check" spinner="save" class="btn-primary" />
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
