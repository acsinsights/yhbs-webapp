<?php

use Mary\Traits\Toast;
use Mary\Traits\WithMediaSync;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Http\UploadedFile;
use App\Models\{Yatch, Category, Amenity};

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
    public ?int $length = null;
    public ?int $max_guests = null;
    public ?int $max_crew = null;
    public ?int $max_fuel_capacity = null;
    public ?int $max_capacity = null;
    public array $category_ids = [];
    public array $amenity_ids = [];

    // Image library properties
    public array $files = [];
    public Collection $library;

    public bool $addCategoryModal = false;
    public string $category_name = '';
    public $category_icon = null;

    public bool $addAmenityModal = false;
    public string $amenity_name = '';
    public $amenity_icon = null;

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
        $this->existing_image = $yatch->image;
        $this->image = null; // Keep null for file upload, use existing_image for display
        $this->description = $yatch->description;
        $this->sku = $yatch->sku;
        $this->price = $yatch->price;
        $this->discount_price = $yatch->discount_price;
        $this->length = $yatch->length;
        $this->max_guests = $yatch->max_guests;
        $this->max_crew = $yatch->max_crew;
        $this->max_fuel_capacity = $yatch->max_fuel_capacity;
        // Auto-calculate max_capacity from guests and crew
        $this->calculateMaxCapacity();
        $this->category_ids = $yatch->categories->pluck('id')->toArray();
        $this->amenity_ids = $yatch->amenities->pluck('id')->toArray();

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

    private function calculateMaxCapacity(): void
    {
        $guests = $this->max_guests ?? 0;
        $crew = $this->max_crew ?? 0;
        $this->max_capacity = $guests + $crew;
    }

    public function hydrate(): void
    {
        $this->ensureLibraryIsCollection();
    }

    public function updated($propertyName): void
    {
        $this->ensureLibraryIsCollection();
    }

    private function ensureLibraryIsCollection(): void
    {
        if (!($this->library instanceof Collection)) {
            $this->library = is_array($this->library) ? Collection::make($this->library) : Collection::make([]);
        }
    }

    public function update(): void
    {
        $rules = [
            'name' => 'required|string|max:255|unique:yatches,name,' . $this->yatch->id,
            'files.*' => 'image|max:5000',
            'description' => 'nullable|string',
            'sku' => 'nullable|integer',
            'price' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'length' => 'nullable|integer|min:0',
            'max_guests' => 'nullable|integer|min:0',
            'max_crew' => 'nullable|integer|min:0',
            'max_fuel_capacity' => 'nullable|integer|min:0',
            'max_capacity' => 'nullable|integer|min:0',
            'library' => 'nullable',
        ];

        if ($this->image instanceof UploadedFile) {
            $rules['image'] = 'nullable|image|max:5000';
        }

        $this->validate($rules);

        $this->calculateMaxCapacity();

        $slug = Str::slug($this->name);

        $originalSlug = $slug;
        $counter = 1;
        while (Yatch::where('slug', $slug)->where('id', '!=', $this->yatch->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $imagePath = $this->existing_image;
        if ($this->image instanceof UploadedFile) {
            $url = $this->image->store('yatches', 'public');
            $imagePath = "/storage/$url";
        }

        $this->yatch->update([
            'name' => $this->name,
            'slug' => $slug,
            'image' => $imagePath,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'length' => $this->length,
            'max_guests' => $this->max_guests,
            'max_crew' => $this->max_crew,
            'max_fuel_capacity' => $this->max_fuel_capacity,
            'max_capacity' => $this->max_capacity,
        ]);

        $this->ensureLibraryIsCollection();

        $this->syncMedia(model: $this->yatch, library: 'library', files: 'files', storage_subpath: '/yatches/library', model_field: 'library', visibility: 'public', disk: 'public');

        $this->yatch->categories()->sync($this->category_ids);
        $this->yatch->amenities()->sync($this->amenity_ids);

        $this->success('Yacht updated successfully.', redirectTo: route('admin.yatch.index'));
    }

    public function saveCategory(): void
    {
        $this->validate([
            'category_name' => 'required|string|max:255',
            'category_icon' => 'nullable|image|max:2500',
        ]);

        $icon = null;
        if ($this->category_icon) {
            $url = $this->category_icon->store('categories', 'public');
            $icon = "/storage/$url";
        }

        $category = Category::create([
            'name' => $this->category_name,
            'slug' => Str::slug($this->category_name),
            'icon' => $icon,
            'type' => 'yatch',
        ]);

        $this->success('Category created successfully.');
        $this->addCategoryModal = false;
        $this->reset('category_name', 'category_icon');
        $this->category_ids = array_merge($this->category_ids, [$category->id]);
    }

    public function saveAmenity(): void
    {
        $this->validate([
            'amenity_name' => 'required|string|max:255',
            'amenity_icon' => 'nullable|image|max:2500',
        ]);

        $icon = null;
        if ($this->amenity_icon) {
            $url = $this->amenity_icon->store('amenities', 'public');
            $icon = "/storage/$url";
        }

        $amenity = Amenity::create([
            'name' => $this->amenity_name,
            'slug' => Str::slug($this->amenity_name),
            'icon' => $icon,
            'type' => 'yatch',
        ]);

        $this->success('Amenity created successfully.');
        $this->addAmenityModal = false;
        $this->reset('amenity_name', 'amenity_icon');
        $this->amenity_ids = array_merge($this->amenity_ids, [$amenity->id]);
    }

    public function rendering(View $view): void
    {
        $view->categories = Category::type('yatch')->latest()->get();
        $view->amenities = Amenity::type('yatch')->latest()->get();
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
                'label' => 'Yachts',
                'link' => route('admin.yatch.index'),
                'icon' => 'o-home-modern',
            ],
            [
                'label' => 'Edit Yacht',
                'icon' => 'o-pencil',
            ],
        ];
    @endphp

    <x-header title="Edit Yacht" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Update yacht information</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back to Yachts" link="{{ route('admin.yatch.index') }}"
                class="btn-primary btn-soft" responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="mt-3 md:mt-5">
        <x-form wire:submit="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <x-input wire:model="name" label="Name" placeholder="Enter yacht name" icon="o-tag"
                    hint="The slug will be auto-generated from the name" />

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

                <x-input wire:model="length" type="number" label="Length (m)" placeholder="Enter length in meters"
                    icon="o-arrows-pointing-out" hint="Yacht length in meters" />

                <x-input wire:model="max_guests" type="number" label="Max Guests" placeholder="Enter maximum guests"
                    icon="o-users" hint="Maximum number of guests" />

                <x-input wire:model="max_crew" type="number" label="Max Crew" placeholder="Enter maximum crew"
                    icon="o-user-group" hint="Maximum number of crew members" />

                <x-input wire:model="max_fuel_capacity" type="number" label="Max Fuel Capacity (L)"
                    placeholder="Enter fuel capacity" icon="o-beaker" hint="Maximum fuel capacity in liters" />
            </div>

            <div class="mt-4 md:mt-6">
                <x-choices-offline wire:model="category_ids" label="Categories" placeholder="Select categories"
                    :options="$categories" icon="o-squares-2x2" hint="Select one or more categories for this yacht"
                    searchable clearable>
                    <x-slot:append>
                        <x-button icon="o-plus" label="Add Category" class="btn-primary join-item btn-sm md:btn-md"
                            @click="$wire.addCategoryModal = true" responsive />
                    </x-slot:append>
                </x-choices-offline>
            </div>

            <div class="mt-4 md:mt-6">
                <x-choices-offline wire:model="amenity_ids" label="Amenities" placeholder="Select amenities"
                    :options="$amenities" icon="o-sparkles" hint="Select one or more amenities available in this yacht"
                    searchable clearable>
                    <x-slot:append>
                        <x-button icon="o-plus" label="Add Amenity" class="btn-primary join-item btn-sm md:btn-md"
                            @click="$wire.addAmenityModal = true" responsive />
                    </x-slot:append>
                </x-choices-offline>
            </div>

            <div class="mt-4 md:mt-6">
                @php
                    $editorConfig = [
                        'valid_elements' => '*[*]',
                        'extended_valid_elements' => '*[*]',
                        'plugins' => 'code',
                        'toolbar' => 'undo redo | align bullist numlist | outdent indent | quickimage quicktable | code',
                    ];
                @endphp
                <x-editor wire:model="description" label="Description" hint="Detailed description of the yacht (HTML code editing enabled)"
                    :config="$editorConfig" />
            </div>

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

    <x-modal wire:model="addCategoryModal" title="Add Category" class="backdrop-blur" max-width="md">
        <x-form wire:submit="saveCategory">
            <div class="space-y-4">
                <x-input wire:model="category_name" label="Category Name" placeholder="Enter category name" />
                <x-file wire:model="category_icon" label="Category Icon" placeholder="Enter category icon"
                    crop-after-change :crop-config="$config" hint="Max: 2MB">
                    <img src="https://placehold.co/300" alt="Category Icon"
                        class="rounded-md w-full max-w-xs h-48 object-cover mx-auto" />
                </x-file>
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" @click="$wire.addCategoryModal = false"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Add Category" type="submit"
                        class="btn-primary w-full sm:w-auto" spinner="saveCategory" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>

    <x-modal wire:model="addAmenityModal" title="Add Amenity" class="backdrop-blur" max-width="md">
        <x-form wire:submit="saveAmenity">
            <div class="space-y-4">
                <x-input wire:model="amenity_name" label="Amenity Name" placeholder="Enter amenity name" />
                <x-file wire:model="amenity_icon" label="Amenity Icon" placeholder="Enter amenity icon"
                    crop-after-change :crop-config="$config" hint="Max: 2MB">
                    <img src="https://placehold.co/300" alt="Amenity Icon"
                        class="rounded-md w-full max-w-xs h-48 object-cover mx-auto" />
                </x-file>
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" @click="$wire.addAmenityModal = false"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Add Amenity" type="submit" class="btn-primary w-full sm:w-auto"
                        spinner="saveAmenity" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
