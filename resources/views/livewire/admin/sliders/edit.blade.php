<div class="pb-4">
    <x-header :title="$isCreating ? 'Create Slider' : 'Edit Slider'" separator>
        <x-slot:actions>
            <x-button label="Back" icon="o-arrow-left" link="{{ route('admin.sliders.index') }}" class="btn-outline" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <x-input wire:model="title" label="Title" placeholder="Enter slider title" icon="o-document-text"
                    required />

                <x-input wire:model="order" label="Order" placeholder="0" icon="o-bars-3" type="number" min="0"
                    hint="Display order (lower numbers first)" />
            </div>

            <div class="mt-4">
                <x-textarea wire:model="description" label="Description" placeholder="Enter slider description"
                    rows="3" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4">
                <x-input wire:model="button_text" label="Button Text" placeholder="Learn More"
                    icon="o-cursor-arrow-rays" />

                <x-input wire:model="button_link" label="Button Link" placeholder="https://..." icon="o-link"
                    type="url" />
            </div>

            {{-- Image Upload --}}
            <div class="mt-4">
                <x-file wire:model="image" label="Slider Image" accept="image/*"
                    hint="Upload slider image (Max: 2MB, Recommended: 1920x800px)">
                    <img src="{{ $existingImage ? asset('storage/' . $existingImage) : 'https://placehold.co/1920x800' }}"
                        alt="Slider Image" class="rounded-md object-cover w-full max-w-2xl h-48 md:h-64" />
                </x-file>
            </div>

            <div class="mt-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Status</span>
                    </label>
                    <x-toggle wire:model="is_active" label="Active" />
                </div>
            </div>

            {{-- Form Actions --}}
            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 mt-6 md:mt-8 pt-4 md:pt-6 border-t">
                <x-button icon="o-x-mark" label="Cancel" link="{{ route('admin.sliders.index') }}"
                    class="btn-error btn-outline" responsive />
                <x-button icon="o-check" label="{{ $isCreating ? 'Create' : 'Update' }}" type="submit"
                    class="btn-primary btn-outline" spinner="save" responsive />
            </div>
        </x-form>
    </x-card>
</div>
