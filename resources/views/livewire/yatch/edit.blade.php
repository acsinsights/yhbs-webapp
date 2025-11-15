<?php

use Mary\Traits\Toast;
use Mary\Traits\WithMediaSync;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Http\UploadedFile;
use App\Models\Yatch;

new class extends Component {
    use Toast, WithFileUploads, WithMediaSync;

    public Yatch $yatch;

    public string $name = '';
    public string $slug = '';
    public $image = null;
    public ?string $existing_image = null;
    public ?string $description = null;
    public ?int $sku = null;
    public ?float $price = null;
    public ?float $discount_price = null;

    // Image library properties
    public array $files = [];
    public Collection $library;

    public $config = ['aspectRatio' => 1];
    public $config2 = ['aspectRatio' => 16 / 9];

    public function rules(): array
    {
        return [
            'files.*' => 'image|max:5000',
            'library' => 'nullable',
        ];
    }

    public function mount(Yatch $yatch): void
    {
        $this->yatch = $yatch;
        $this->name = $yatch->name;
        $this->slug = $yatch->slug;
        $this->existing_image = $yatch->image;
        $this->image = null; // Keep null for file upload, use existing_image for display
        $this->description = $yatch->description;
        $this->sku = $yatch->sku;
        $this->price = $yatch->price;
        $this->discount_price = $yatch->discount_price;

        // Load existing library metadata from yatch
        $libraryData = $yatch->library;
        if (empty($libraryData)) {
            $this->library = Collection::make([]);
        } elseif (is_array($libraryData)) {
            $this->library = Collection::make($libraryData);
        } elseif ($libraryData instanceof Collection) {
            $this->library = $libraryData;
        } else {
            $this->library = Collection::make([]);
        }
    }

    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
    }

    // Ensure library is always a Collection after Livewire hydration
    public function hydrate(): void
    {
        $this->ensureLibraryIsCollection();
    }

    // Ensure library is always a Collection after any property update
    public function updated($propertyName): void
    {
        $this->ensureLibraryIsCollection();
    }

    // Helper method to ensure library is always a Collection
    private function ensureLibraryIsCollection(): void
    {
        if (!($this->library instanceof Collection)) {
            $this->library = is_array($this->library) ? Collection::make($this->library) : Collection::make([]);
        }
    }

    public function update(): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:yatches,slug,' . $this->yatch->id,
            'files.*' => 'image|max:5000',
            'description' => 'nullable|string',
            'sku' => 'nullable|integer',
            'price' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'library' => 'nullable',
        ];

        // Only validate image if it's actually a file upload object
        if ($this->image instanceof UploadedFile) {
            $rules['image'] = 'nullable|image|max:5000';
        }

        $this->validate($rules);

        // Handle single image upload - keep existing if no new upload
        $imagePath = $this->existing_image;
        if ($this->image instanceof UploadedFile) {
            $url = $this->image->store('yatches', 'public');
            $imagePath = "/storage/$url";
        }

        $this->yatch->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'image' => $imagePath,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
        ]);

        // Ensure library is a Collection before calling syncMedia
        $this->ensureLibraryIsCollection();

        // Sync media files and update library metadata
        $this->syncMedia(model: $this->yatch, library: 'library', files: 'files', storage_subpath: '/yatches/library', model_field: 'library', visibility: 'public', disk: 'public');

        $this->success('Yacht updated successfully.', redirectTo: route('admin.yatch.index'));
    }
}; ?>
@section('cdn')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.2.1/tinymce.min.js" referrerpolicy="origin"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
@endsection
<div class="pb-4">
    <x-header title="Edit Yacht" subtitle="Update yacht information" separator>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back to Yachts" link="{{ route('admin.yatch.index') }}" class="btn-ghost"
                responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="mt-3 md:mt-5">
        <x-form wire:submit="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <x-input wire:model="name" label="Name" placeholder="Enter yacht name" icon="o-tag"
                    hint="The slug will be auto-generated from the name" />

                <x-input wire:model="slug" label="Slug" placeholder="yacht-slug" icon="o-link"
                    hint="URL-friendly version of the name" />

                <x-input wire:model="sku" type="number" label="SKU" placeholder="Enter SKU" icon="o-hashtag"
                    hint="Stock keeping unit" />

                <x-file wire:model="image" label="Yacht Image" placeholder="Upload yacht image" crop-after-change
                    :crop-config="$config2" hint="Max: 5MB">
                    <img src="{{ $existing_image ? asset($existing_image) : 'https://placehold.co/600x400' }}"
                        alt="Yacht Image" class="rounded-md object-cover w-full h-35 md:h-40" />
                </x-file>

                <x-image-library wire:model="files" wire:library="library" :preview="$library"
                    label="Yacht Images Gallery" hint="Max 5MB per image" change-text="Change" crop-text="Crop"
                    remove-text="Remove" crop-title-text="Crop image" crop-cancel-text="Cancel" crop-save-text="Crop"
                    add-files-text="Add images" />

                <x-input wire:model="price" type="number" step="0.01" label="Price" placeholder="0.00"
                    icon="o-currency-dollar" hint="Regular yacht price" />

                <x-input wire:model="discount_price" type="number" step="0.01" label="Discount Price"
                    placeholder="0.00" icon="o-tag" hint="Discounted price (optional)" />
            </div>

            {{-- Description Editor --}}
            <div class="mt-4 md:mt-6">
                <x-editor wire:model="description" label="Description" hint="Detailed description of the yacht" />
            </div>

            {{-- Form Actions --}}
            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 mt-6 md:mt-8 pt-4 md:pt-6 border-t">
                <x-button icon="o-arrow-left" label="Back to Yachts" link="{{ route('admin.yatch.index') }}"
                    class="btn-ghost w-full sm:w-auto order-2 sm:order-1" responsive />
                <x-button icon="o-x-mark" label="Cancel" link="{{ route('admin.yatch.index') }}"
                    class="btn-ghost w-full sm:w-auto order-1 sm:order-2" responsive />
                <x-button icon="o-check" label="Update Yacht" type="submit"
                    class="btn-primary w-full sm:w-auto order-3" spinner="update" responsive />
            </div>
        </x-form>
    </x-card>
</div>
