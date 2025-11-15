<?php

use Mary\Traits\Toast;
use Mary\Traits\WithMediaSync;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\{Room, Hotel, Category, Amenity};

new class extends Component {
    use Toast, WithFileUploads, WithMediaSync;

    public Room $room;

    public int $hotel_id = 0;
    public string $room_number = '';
    public ?string $image = null;
    public ?string $existing_image = null;
    public ?string $description = null;
    public ?float $price = null;
    public ?float $discount_price = null;
    public array $category_ids = [];
    public array $amenity_ids = [];
    public ?string $meta_keywords = null;
    public ?string $meta_description = null;

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

    public function mount(Room $room): void
    {
        $this->room = $room;
        $this->hotel_id = $room->hotel_id;
        $this->room_number = $room->room_number;
        $this->existing_image = $room->image;
        $this->image = null; // Keep null for file upload, use existing_image for display
        $this->description = $room->description;
        $this->price = $room->price;
        $this->discount_price = $room->discount_price;
        $this->meta_keywords = $room->meta_keywords;
        $this->meta_description = $room->meta_description;
        $this->category_ids = $room->categories->pluck('id')->toArray();
        $this->amenity_ids = $room->amenities->pluck('id')->toArray();

        // Load existing library metadata from room
        $this->library = $room->library ?? new Collection();
    }

    public function update(): void
    {
        $this->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'room_number' => 'required|string|max:255',
            'image' => 'nullable|image|max:5000',
            'files.*' => 'image|max:5000',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'meta_keywords' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:150',
            'library' => 'nullable',
        ]);

        // Handle single image upload - keep existing if no new upload
        $imagePath = $this->existing_image;
        if ($this->image) {
            $url = $this->image->store('rooms', 'public');
            $imagePath = "/storage/$url";
        }

        $this->room->update([
            'hotel_id' => $this->hotel_id,
            'room_number' => $this->room_number,
            'image' => $imagePath,
            'description' => $this->description,
            'price' => $this->price,
            'discount_price' => $this->discount_price,
            'meta_keywords' => $this->meta_keywords,
            'meta_description' => $this->meta_description,
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
            'type' => 'room',
        ]);

        $this->success('Amenity created successfully.');
        $this->addAmenityModal = false;
        $this->reset('amenity_name', 'amenity_icon');
        $this->amenity_ids = array_merge($this->amenity_ids, [$amenity->id]);
    }

    public function rendering(View $view): void
    {
        $view->hotels = Hotel::latest()->get();
        $view->categories = Category::latest()->get();
        $view->amenities = Amenity::type('room')->latest()->get();
    }
}; ?>
@section('cdn')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.2.1/tinymce.min.js" referrerpolicy="origin"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
@endsection
<div class="pb-4">
    <x-header title="Edit Room" subtitle="Update room information" separator>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back to Rooms" link="{{ route('admin.rooms.index') }}" class="btn-ghost"
                responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="mt-3 md:mt-5">
        <x-form wire:submit="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <x-select wire:model="hotel_id" label="Hotel" placeholder="Select a hotel" :options="$hotels"
                    option-value="id" option-label="name" icon="o-building-office-2"
                    hint="Select the hotel this room belongs to" />

                <x-input wire:model="room_number" label="Room Number" placeholder="e.g., 101, 202, Suite A"
                    icon="o-hashtag" hint="Unique room identifier" />

                <x-file wire:model="image" label="Room Image" placeholder="Upload room image" crop-after-change
                    :crop-config="$config2" hint="Max: 5MB">
                    <img src="{{ $existing_image ? asset($existing_image) : 'https://placehold.co/600x400' }}"
                        alt="Room Image" class="rounded-md object-cover w-full h-35 md:h-40" />
                </x-file>

                <x-image-library wire:model="files" wire:library="library" :preview="$library" label="Room Images Gallery"
                    hint="Max 5MB per image" change-text="Change" crop-text="Crop" remove-text="Remove"
                    crop-title-text="Crop image" crop-cancel-text="Cancel" crop-save-text="Crop"
                    add-files-text="Add images" />

                <x-input wire:model="price" type="number" step="0.01" label="Price" placeholder="0.00"
                    icon="o-currency-dollar" hint="Regular room price" />

                <x-input wire:model="discount_price" type="number" step="0.01" label="Discount Price"
                    placeholder="0.00" icon="o-tag" hint="Discounted price (optional)" />

                <x-textarea wire:model="meta_description" label="Meta Description" hint="Max 150 characters"
                    rows="3" />

                <x-textarea wire:model="meta_keywords" label="Meta Keywords" hint="Separated by commas"
                    rows="3" />
            </div>

            <div class="mt-4 md:mt-6">
                <x-choices-offline wire:model="category_ids" label="Categories" placeholder="Select categories"
                    :options="$categories" icon="o-squares-2x2" hint="Select one or more categories for this room" searchable
                    clearable>
                    <x-slot:append>
                        <x-button icon="o-plus" label="Add Category" class="btn-primary join-item btn-sm md:btn-md"
                            @click="$wire.addCategoryModal = true" responsive />
                    </x-slot:append>
                </x-choices-offline>
            </div>

            {{-- Amenities --}}
            <div class="mt-4 md:mt-6">
                <x-choices-offline wire:model="amenity_ids" label="Amenities" placeholder="Select amenities"
                    :options="$amenities" icon="o-sparkles" hint="Select one or more amenities available in this room"
                    searchable clearable>
                    <x-slot:append>
                        <x-button icon="o-plus" label="Add Amenity" class="btn-primary join-item btn-sm md:btn-md"
                            @click="$wire.addAmenityModal = true" responsive />
                    </x-slot:append>
                </x-choices-offline>
            </div>

            {{-- Description Editor --}}
            <div class="mt-4 md:mt-6">
                <x-editor wire:model="description" label="Description" hint="Detailed description of the room" />
            </div>

            {{-- Form Actions --}}
            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 mt-6 md:mt-8 pt-4 md:pt-6 border-t">
                <x-button icon="o-arrow-left" label="Back to Rooms" link="{{ route('admin.rooms.index') }}"
                    class="btn-ghost w-full sm:w-auto order-2 sm:order-1" responsive />
                <x-button icon="o-x-mark" label="Cancel" link="{{ route('admin.rooms.index') }}"
                    class="btn-ghost w-full sm:w-auto order-1 sm:order-2" responsive />
                <x-button icon="o-check" label="Update Room" type="submit"
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
