<?php

use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use Livewire\WithFileUploads;
use Livewire\Volt\Component;
use Mary\Traits\{Toast, WithMediaSync};
use App\Models\House;

new class extends Component {
    use Toast, WithFileUploads, WithMediaSync;

    public House $house;

    public string $name = '';
    public string $slug = '';
    public string $house_number = '';
    public bool $is_active = false;
    public bool $is_under_maintenance = false;
    public ?string $maintenance_note = null;
    public ?UploadedFile $image = null;
    public ?string $existing_image = null;
    public ?string $description = null;
    public ?string $meta_description = null;
    public ?string $meta_keywords = null;
    public ?float $price_per_night = null;
    public ?float $price_per_2night = null;
    public ?float $price_per_3night = null;
    public ?float $additional_night_price = null;
    public ?int $adults = null;
    public ?int $children = null;
    public ?int $number_of_rooms = null;

    // Image library properties
    public array $files = [];
    public Collection $library;

    public $config2 = ['aspectRatio' => 16 / 9];

    public function rules(): array
    {
        return [
            'files.*' => 'image|max:5000',
            'library' => 'nullable',
        ];
    }

    public function mount(House $house): void
    {
        $this->house = $house;
        $this->name = $house->name;
        $this->slug = $house->slug;
        $this->house_number = $house->house_number ?? '';
        $this->is_active = $house->is_active ?? false;
        $this->is_under_maintenance = $house->is_under_maintenance ?? false;
        $this->maintenance_note = $house->maintenance_note;
        $this->existing_image = $house->image;
        $this->image = null; // Keep null for file upload, use existing_image for display
        $this->description = $house->description;
        $this->meta_description = $house->meta_description;
        $this->meta_keywords = $house->meta_keywords;
        $this->price_per_night = $house->price_per_night;
        $this->price_per_2night = $house->price_per_2night;
        $this->price_per_3night = $house->price_per_3night;
        $this->additional_night_price = $house->additional_night_price;
        $this->adults = $house->adults;
        $this->children = $house->children;
        $this->number_of_rooms = $house->number_of_rooms;
        $this->library = $house->library ?? new Collection();
    }

    public function update(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:houses,slug,' . $this->house->id,
            'house_number' => 'nullable|string|max:255|unique:houses,house_number,' . $this->house->id,
            'is_active' => 'boolean',
            'is_under_maintenance' => 'boolean',
            'maintenance_note' => 'nullable|string|max:255',
            'image' => 'nullable|image|max:5000',
            'files.*' => 'image|max:5000',
            'description' => 'nullable|string',
            'meta_description' => 'nullable|string|max:150',
            'meta_keywords' => 'nullable|string|max:255',
            'price_per_night' => 'nullable|numeric|min:0|max:999999999.99',
            'price_per_2night' => 'nullable|numeric|min:0|max:999999999.99',
            'price_per_3night' => 'nullable|numeric|min:0|max:999999999.99',
            'additional_night_price' => 'nullable|numeric|min:0|max:999999999.99',
            'adults' => 'nullable|integer|min:0|max:999',
            'children' => 'nullable|integer|min:0|max:999',
            'number_of_rooms' => 'nullable|integer|min:0|max:999',
            'library' => 'nullable',
        ]);

        // Handle single image upload - keep existing if no new upload
        $imagePath = $this->existing_image;
        if ($this->image instanceof UploadedFile) {
            $url = $this->image->store('houses', 'public');
            $imagePath = "/storage/$url";
        }

        $this->house->update([
            'name' => $this->name,
            'slug' => Str::slug($this->slug),
            'house_number' => $this->house_number ?: null,
            'is_active' => $this->is_active,
            'is_under_maintenance' => $this->is_under_maintenance,
            'maintenance_note' => $this->maintenance_note,
            'image' => $imagePath,
            'description' => $this->description,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'price_per_night' => $this->price_per_night,
            'price_per_2night' => $this->price_per_2night,
            'price_per_3night' => $this->price_per_3night,
            'additional_night_price' => $this->additional_night_price,
            'adults' => $this->adults,
            'children' => $this->children,
            'number_of_rooms' => $this->number_of_rooms,
        ]);

        // Sync media files and update library metadata
        $this->syncMedia(model: $this->house, library: 'library', files: 'files', storage_subpath: '/houses/library', model_field: 'library', visibility: 'public', disk: 'public');

        $this->success('House updated successfully.', redirectTo: route('admin.houses.index'));
    }
}; ?>
@section('cdn')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.2.1/tinymce.min.js" referrerpolicy="origin"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
@endsection
<div class="pb-4">
    @php
        $breadcrumbs = [
            [
                'link' => route('admin.index'),
                'icon' => 's-home',
            ],
            [
                'label' => 'Houses',
                'link' => route('admin.houses.index'),
                'icon' => 'o-building-office-2',
            ],
            [
                'label' => 'Edit House',
                'icon' => 'o-pencil',
            ],
        ];
    @endphp

    <x-header title="Edit House" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Update house information</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back to Houses" link="{{ route('admin.houses.index') }}"
                class="btn-primary btn-soft" responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="mt-3 md:mt-5">
        <x-form wire:submit="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <x-input wire:model="name" label="House Name" placeholder="Enter house name" icon="o-building-office-2"
                    hint="The slug will be auto-generated from the name" />

                <x-input wire:model="house_number" label="House Number" placeholder="Enter house number"
                    icon="o-hashtag" hint="Optional unique house number" />

                <div>
                    <label class="block text-sm font-medium mb-2">Active Status</label>
                    <x-toggle wire:model="is_active" label="Active" hint="Toggle to activate/deactivate this house" />
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Maintenance Status</label>
                    <x-toggle wire:model="is_under_maintenance" label="Under Maintenance"
                        hint="Toggle when house needs maintenance" class="text-warning" />
                </div>

                @if ($is_under_maintenance)
                    <x-input wire:model="maintenance_note" label="Maintenance Note"
                        placeholder="e.g., Plumbing repairs - Available from Jan 15" icon="o-wrench-screwdriver"
                        hint="This message will be shown to customers" />
                @endif

                <x-file wire:model="image" label="House Image" placeholder="Upload house image" crop-after-change
                    :crop-config="$config2" hint="Max: 5MB">
                    <img src="{{ $existing_image ? (str_starts_with($existing_image, 'http') ? $existing_image : asset($existing_image)) : 'https://placehold.co/600x400' }}"
                        alt="House Image" class="rounded-md object-cover w-full h-35 md:h-40" />
                </x-file>

                <x-image-library wire:model="files" wire:library="library" :preview="$library"
                    label="House Images Gallery" hint="Max 5MB per image" change-text="Change" crop-text="Crop"
                    remove-text="Remove" crop-title-text="Crop image" crop-cancel-text="Cancel" crop-save-text="Crop"
                    add-files-text="Add images" />
            </div>

            {{-- Pricing Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4 md:mt-6">
                <x-input wire:model="price_per_night" label="Price Per Night" placeholder="0.00"
                    icon="o-currency-dollar" type="number" step="0.01" min="0"
                    hint="Base price for 1 night" />

                <x-input wire:model="price_per_2night" label="Price Per 2 Nights" placeholder="0.00"
                    icon="o-currency-dollar" type="number" step="0.01" min="0"
                    hint="Special price for 2 nights" />

                <x-input wire:model="price_per_3night" label="Price Per 3 Nights" placeholder="0.00"
                    icon="o-currency-dollar" type="number" step="0.01" min="0"
                    hint="Special price for 3 nights" />

                <x-input wire:model="additional_night_price" label="Additional Night Price" placeholder="0.00"
                    icon="o-currency-dollar" type="number" step="0.01" min="0"
                    hint="Price per night after 3 nights" />
            </div>

            {{-- Capacity Section --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mt-4 md:mt-6">
                <x-input wire:model="adults" label="Maximum Adults" placeholder="0" icon="o-user" type="number"
                    min="0" hint="Maximum number of adults" />

                <x-input wire:model="children" label="Maximum Children" placeholder="0" icon="o-user-group"
                    type="number" min="0" hint="Maximum number of children" />

                <x-input wire:model="number_of_rooms" label="Number of Rooms" placeholder="0" icon="o-home"
                    type="number" min="0" hint="Total number of rooms in house" />
            </div>
            {{-- Meta Information Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4 md:mt-6">
                <x-textarea wire:model="meta_description" label="Meta Description" hint="Max 150 characters"
                    rows="3" />

                <x-textarea wire:model="meta_keywords" label="Meta Keywords" hint="Separated by commas"
                    rows="3" />
            </div>

            {{-- Description Editor --}}
            <div class="mt-4 md:mt-6">
                @php
                    $editorConfig = [
                        'valid_elements' => '*[*]',
                        'extended_valid_elements' => '*[*]',
                        'plugins' => 'code',
                        'toolbar' =>
                            'undo redo | align bullist numlist | outdent indent | quickimage quicktable | code',
                    ];
                @endphp
                <x-editor wire:model="description" label="Description"
                    hint="Detailed description of the house (HTML code editing enabled)" :config="$editorConfig" />
            </div>
            {{-- Form Actions --}}
            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 mt-6 md:mt-8 pt-4 md:pt-6 border-t">
                <x-button icon="o-x-mark" label="Cancel" link="{{ route('admin.houses.index') }}"
                    class="btn-error btn-outline" responsive />
                <x-button icon="o-check" label="Update" type="submit" class="btn-primary btn-outline"
                    spinner="update" responsive />
            </div>
        </x-form>
    </x-card>
</div>
