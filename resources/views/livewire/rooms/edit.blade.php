<?php

use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use Livewire\WithFileUploads;
use Livewire\Volt\Component;
use Mary\Traits\{Toast, WithMediaSync};
use App\Models\{Amenity, Category, Room};

new class extends Component {
    use Toast, WithFileUploads, WithMediaSync;

    public Room $room;

    public string $name = '';
    public string $slug = '';
    public string $room_number = '';
    public ?UploadedFile $image = null;
    public ?string $existing_image = null;
    public ?string $description = null;
    public ?float $price_per_night = null;
    public ?float $price_per_2night = null;
    public ?float $price_per_3night = null;
    public ?float $additional_night_price = null;
    public array $category_ids = [];
    public array $amenity_ids = [];
    public ?string $meta_keywords = null;
    public ?string $meta_description = null;
    public bool $is_active = false;
    public bool $is_under_maintenance = false;
    public ?string $maintenance_note = null;
    public ?int $adults = null;
    public ?int $children = null;

    // Image library properties
    public array $files = [];
    public Collection $library;

    public bool $addCategoryModal = false;
    public string $category_name = '';
    public $category_icon = null;

    public bool $addAmenityModal = false;
    public string $amenity_name = '';

    public $config = ['aspectRatio' => 1];
    public $config2 = ['aspectRatio' => 16 / 9];

    public function rules(): array
    {
        return [
            'files.*' => 'image|max:5000',
            'library' => 'nullable',
        ];
    }

    public function mount(Room $room): void
    {
        $this->room = $room;
        $this->name = $room->name ?? '';
        $this->slug = $room->slug ?? '';
        $this->room_number = $room->room_number;
        $this->existing_image = $room->image;
        $this->image = null; // Keep null for file upload, use existing_image for display
        $this->description = $room->description;
        $this->price_per_night = $room->price_per_night;
        $this->price_per_2night = $room->price_per_2night;
        $this->price_per_3night = $room->price_per_3night;
        $this->additional_night_price = $room->additional_night_price;
        $this->meta_keywords = $room->meta_keywords;
        $this->meta_description = $room->meta_description;
        $this->is_active = $room->is_active ?? false;
        $this->is_under_maintenance = $room->is_under_maintenance ?? false;
        $this->maintenance_note = $room->maintenance_note;
        $this->adults = $room->adults;
        $this->children = $room->children;
        $this->category_ids = $room->categories->pluck('id')->toArray();
        $this->amenity_ids = $room->amenities->pluck('id')->toArray();
        $this->library = $room->library ?? new Collection();
    }

    public function updatedName($value): void
    {
        // Auto-generate slug from name
        $this->slug = Str::slug($value);

        // Ensure slug is unique (excluding current room)
        $originalSlug = $this->slug;
        $counter = 1;
        while (Room::where('slug', $this->slug)->where('id', '!=', $this->room->id)->exists()) {
            $this->slug = $originalSlug . '-' . $counter;
            $counter++;
        }
    }

    public function update(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:rooms,slug,' . $this->room->id,
            'room_number' => 'required|string|max:255',
            'image' => 'nullable|image|max:5000',
            'files.*' => 'image|max:5000',
            'description' => 'nullable|string',
            'price_per_night' => 'nullable|numeric|min:0',
            'price_per_2night' => 'nullable|numeric|min:0',
            'price_per_3night' => 'nullable|numeric|min:0',
            'additional_night_price' => 'nullable|numeric|min:0',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:150',
            'is_active' => 'nullable|boolean',
            'is_under_maintenance' => 'nullable|boolean',
            'maintenance_note' => 'nullable|string|max:255',
            'adults' => 'nullable|integer|min:0',
            'children' => 'nullable|integer|min:0',
            'library' => 'nullable',
        ]);

        // Handle single image upload - keep existing if no new upload
        $imagePath = $this->existing_image;
        if ($this->image instanceof UploadedFile) {
            $url = $this->image->store('rooms', 'public');
            $imagePath = "/storage/$url";
        }

        $this->room->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'room_number' => $this->room_number,
            'image' => $imagePath,
            'description' => $this->description,
            'price_per_night' => $this->price_per_night,
            'price_per_2night' => $this->price_per_2night,
            'price_per_3night' => $this->price_per_3night,
            'additional_night_price' => $this->additional_night_price,
            'meta_keywords' => $this->meta_keywords,
            'meta_description' => $this->meta_description,
            'is_active' => $this->is_active,
            'is_under_maintenance' => $this->is_under_maintenance,
            'maintenance_note' => $this->maintenance_note,
            'adults' => $this->adults,
            'children' => $this->children,
        ]);

        // Sync media files and update library metadata
        $this->syncMedia(model: $this->room, library: 'library', files: 'files', storage_subpath: '/rooms/library', model_field: 'library', visibility: 'public', disk: 'public');

        $this->room->categories()->sync($this->category_ids);
        $this->room->amenities()->sync($this->amenity_ids);

        $this->success('Room updated successfully.', redirectTo: route('admin.rooms.index'));
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
        ]);

        $amenity = Amenity::create([
            'name' => $this->amenity_name,
            'slug' => Str::slug($this->amenity_name),
            'type' => 'room',
        ]);

        $this->success('Amenity created successfully.');
        $this->addAmenityModal = false;
        $this->reset('amenity_name');
        $this->amenity_ids = array_merge($this->amenity_ids, [$amenity->id]);
    }

    public function rendering(View $view): void
    {
        $view->categories = Category::type('room')->latest()->get();
        $view->amenities = Amenity::type('room')->latest()->get();
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
                'label' => 'Rooms',
                'link' => route('admin.rooms.index'),
                'icon' => 'o-home-modern',
            ],
            [
                'label' => 'Edit Room',
                'icon' => 'o-pencil',
            ],
        ];
    @endphp

    <x-header title="Edit Room" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Update room information</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back to Rooms" link="{{ route('admin.rooms.index') }}"
                class="btn-primary btn-soft" responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="mt-3 md:mt-5">
        <x-form wire:submit="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <x-input wire:model="name" label="Room Name" placeholder="e.g., Standard Room, Deluxe Suite"
                    icon="o-tag" hint="Display name for the room (slug will be auto-generated)" />

                <x-input wire:model="room_number" label="Room Number" placeholder="e.g., 101, 202, Suite A"
                    icon="o-hashtag" hint="Unique room identifier" />

                <div class="flex items-center">
                    <x-toggle wire:model="is_active" label="Active Status" hint="Enable or disable this room" />
                </div>

                <div class="flex items-center">
                    <x-toggle wire:model="is_under_maintenance" label="Under Maintenance"
                        hint="Toggle when room needs maintenance" class="text-warning" />
                </div>

                @if ($is_under_maintenance)
                    <div class="md:col-span-2">
                        <x-input wire:model="maintenance_note" label="Maintenance Note"
                            placeholder="e.g., AC repair - Available from Jan 15" icon="o-wrench-screwdriver"
                            hint="This message will be shown to customers" />
                    </div>
                @endif

                <x-input wire:model="adults" type="number" label="Adults" placeholder="e.g., 2" icon="o-user"
                    hint="Maximum number of adults (optional)" min="0" />

                <x-input wire:model="children" type="number" label="Children" placeholder="e.g., 1" icon="o-user"
                    hint="Maximum number of children (optional)" min="0" />


                <x-file wire:model="image" label="Room Image" placeholder="Upload room image" crop-after-change
                    :crop-config="$config2" hint="Max: 5MB">
                    <img src="{{ $existing_image ? asset($existing_image) : 'https://placehold.co/600x400' }}"
                        alt="Room Image" class="rounded-md object-cover w-full h-35 md:h-40" />
                </x-file>

                <x-image-library wire:model="files" wire:library="library" :preview="$library" label="Room Images Gallery"
                    hint="Max 5MB per image" change-text="Change" crop-text="Crop" remove-text="Remove"
                    crop-title-text="Crop image" crop-cancel-text="Cancel" crop-save-text="Crop"
                    add-files-text="Add images" />

                <x-input wire:model="price_per_night" type="number" step="0.01" label="Price Per Night"
                    placeholder="0.00" icon="o-currency-dollar" hint="Price for 1 night (optional)" />

                <x-input wire:model="price_per_2night" type="number" step="0.01" label="Price Per 2 Nights"
                    placeholder="0.00" icon="o-currency-dollar" hint="Price for 2 nights (optional)" />

                <x-input wire:model="price_per_3night" type="number" step="0.01" label="Price Per 3 Nights"
                    placeholder="0.00" icon="o-currency-dollar" hint="Price for 3 nights (optional)" />

                <x-input wire:model="additional_night_price" type="number" step="0.01"
                    label="Additional Night Price" placeholder="0.00" icon="o-currency-dollar"
                    hint="Price for each additional night (optional)" />

                <x-textarea wire:model="meta_description" label="Meta Description" hint="Max 150 characters"
                    rows="3" />

                <x-textarea wire:model="meta_keywords" label="Meta Keywords" hint="Separated by commas"
                    rows="3" />
            </div>

            <div class="mt-4 md:mt-6">
                <x-choices-offline wire:model="category_ids" label="Categories" placeholder="Select categories"
                    :options="$categories" icon="o-squares-2x2" hint="Select one or more categories for this room"
                    searchable clearable>
                    <x-slot:append>
                        <x-button icon="o-plus" label="Add Category"
                            class="btn-primary join-item btn-sm md:btn-md ml-2" @click="$wire.addCategoryModal = true"
                            responsive />
                    </x-slot:append>
                </x-choices-offline>
            </div>

            {{-- Amenities --}}
            <div class="mt-4 md:mt-6">
                <x-choices-offline wire:model="amenity_ids" label="Amenities" placeholder="Select amenities"
                    :options="$amenities" icon="o-sparkles" hint="Select one or more amenities available in this room"
                    searchable clearable>
                    <x-slot:append>
                        <x-button icon="o-plus" label="Add Amenity"
                            class="btn-primary join-item btn-sm md:btn-md ml-2" @click="$wire.addAmenityModal = true"
                            responsive />
                    </x-slot:append>
                </x-choices-offline>
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
                    hint="Detailed description of the room (HTML code editing enabled)" :config="$editorConfig" />
            </div>

            {{-- Form Actions --}}
            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 mt-6 md:mt-8 pt-4 md:pt-6 border-t">
                <x-button icon="o-x-mark" label="Cancel" link="{{ route('admin.rooms.index') }}"
                    class="btn-error btn-outline" responsive />
                <x-button icon="o-check" label="Update" type="submit" class="btn-primary btn-outline"
                    spinner="update" responsive />
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
            <x-input wire:model="amenity_name" label="Amenity Name" placeholder="Enter amenity name" />

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
