<?php

use Mary\Traits\{Toast, WithMediaSync};
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Locked;
use App\Models\{Amenity, Boat, BoatServiceType};
use Illuminate\Support\{Str, Collection};
use Illuminate\View\View;
use Illuminate\Http\UploadedFile;

new class extends Component {
    use Toast, WithFileUploads, WithMediaSync;

    public Boat $boat;

    // Basic Info
    public string $name = '';
    public string $slug = '';
    public string $service_type = '';
    public ?string $description = null;
    public ?string $location = null;
    public ?string $features = null;
    public array $amenity_ids = [];

    // Inline amenity creation
    public bool $addAmenityModal = false;
    public string $amenity_name = '';

    // Capacity
    public int $min_passengers = 1;
    public int $max_passengers = 10;

    // Yacht/Taxi Pricing
    public ?float $price_1hour = null;
    public ?float $price_2hours = null;
    public ?float $price_3hours = null;
    public ?float $additional_hour_price = null;

    // Ferry Pricing (Private: per hour, Public: per person/hour with weekday/weekend rates)
    public ?float $ferry_private_weekday = null;
    public ?float $ferry_private_weekend = null;
    public ?float $ferry_public_weekday = null;
    public ?float $ferry_public_weekend = null;

    // Limousine Pricing
    public ?float $price_15min = null;
    public ?float $price_30min = null;
    public ?float $price_full_boat = null;

    // Buffer time
    public int $buffer_time = 0;

    // Meta & Status
    public ?string $meta_description = null;
    public ?string $meta_keywords = null;
    public bool $is_active = true;
    public bool $is_under_maintenance = false;
    public ?string $maintenance_note = null;
    public bool $is_featured = false;
    public int $sort_order = 0;

    // Image handling
    public ?UploadedFile $image = null;
    public ?string $existing_image = null;

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

    public function mount(Boat $boat): void
    {
        $this->boat = $boat;
        $this->name = $boat->name;
        $this->slug = $boat->slug;
        $this->service_type = $boat->service_type;
        $this->description = $boat->description;
        $this->location = $boat->location;
        $this->features = $boat->features;
        $this->min_passengers = $boat->min_passengers;
        $this->max_passengers = $boat->max_passengers;
        $this->price_1hour = $boat->price_1hour;
        $this->price_2hours = $boat->price_2hours;
        $this->price_3hours = $boat->price_3hours;
        $this->additional_hour_price = $boat->additional_hour_price;
        $this->ferry_private_weekday = $boat->ferry_private_weekday;
        $this->ferry_private_weekend = $boat->ferry_private_weekend;
        $this->ferry_public_weekday = $boat->ferry_public_weekday;
        $this->ferry_public_weekend = $boat->ferry_public_weekend;
        $this->price_15min = $boat->price_15min;
        $this->price_30min = $boat->price_30min;
        $this->price_full_boat = $boat->price_full_boat;
        $this->buffer_time = $boat->buffer_time ?? 0;
        $this->meta_description = $boat->meta_description;
        $this->meta_keywords = $boat->meta_keywords;
        $this->is_active = $boat->is_active;
        $this->is_under_maintenance = $boat->is_under_maintenance ?? false;
        $this->maintenance_note = $boat->maintenance_note;
        $this->is_featured = $boat->is_featured;
        $this->sort_order = $boat->sort_order;
        $this->existing_image = $boat->image;
        $this->image = null;
        $this->amenity_ids = $boat->amenities->pluck('id')->toArray();
        $this->library = $boat->library ?? new Collection();
    }

    public function updatedName(): void
    {
        $this->slug = Str::slug($this->name);

        // Ensure slug is unique (excluding current boat)
        $originalSlug = $this->slug;
        $counter = 1;
        while (Boat::where('slug', $this->slug)->where('id', '!=', $this->boat->id)->exists()) {
            $this->slug = $originalSlug . '-' . $counter;
            $counter++;
        }
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:boats,slug,' . $this->boat->id,
            'service_type' => 'required|string|exists:boat_service_types,slug',
            'min_passengers' => 'required|integer|min:1',
            'max_passengers' => 'required|integer|min:1',
            'image' => 'nullable|image|max:5000',
            'files.*' => 'image|max:5000',
            'library' => 'nullable',
        ]);

        // Handle single image upload - keep existing if no new upload
        $imagePath = $this->existing_image;
        if ($this->image instanceof UploadedFile) {
            $url = $this->image->store('boats', 'public');
            $imagePath = "/storage/$url";
        }

        $this->boat->update([
            'name' => $this->name,
            'slug' => $this->slug,
            'service_type' => $this->service_type,
            'description' => $this->description,
            'location' => $this->location,
            'features' => $this->features,
            'min_passengers' => $this->min_passengers,
            'max_passengers' => $this->max_passengers,
            'image' => $imagePath,
            'price_1hour' => $this->price_1hour,
            'price_2hours' => $this->price_2hours,
            'price_3hours' => $this->price_3hours,
            'additional_hour_price' => $this->additional_hour_price,
            'ferry_private_weekday' => $this->ferry_private_weekday,
            'ferry_private_weekend' => $this->ferry_private_weekend,
            'ferry_public_weekday' => $this->ferry_public_weekday,
            'ferry_public_weekend' => $this->ferry_public_weekend,
            'price_15min' => $this->price_15min,
            'price_30min' => $this->price_30min,
            'price_full_boat' => $this->price_full_boat,
            'buffer_time' => $this->buffer_time,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'is_active' => $this->is_active,
            'is_under_maintenance' => $this->is_under_maintenance,
            'maintenance_note' => $this->maintenance_note,
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,
        ]);

        // Sync media files and update library metadata
        $this->syncMedia(model: $this->boat, library: 'library', files: 'files', storage_subpath: '/boats/library', model_field: 'library', visibility: 'public', disk: 'public');

        $this->boat->amenities()->sync($this->amenity_ids);

        $this->success('Boat updated successfully.', redirectTo: route('admin.boats.index'));
    }

    public function saveAmenity(): void
    {
        $this->validate([
            'amenity_name' => 'required|string|max:255',
        ]);

        $amenity = Amenity::create([
            'name' => $this->amenity_name,
            'slug' => Str::slug($this->amenity_name),
            'type' => 'boat',
        ]);

        $this->success('Amenity created successfully.');
        $this->addAmenityModal = false;
        $this->reset('amenity_name');
        $this->amenity_ids = array_merge($this->amenity_ids, [$amenity->id]);
    }

    public function rendering(View $view): void
    {
        $view->serviceTypes = BoatServiceType::active()->ordered()->get()->map(fn($type) => ['id' => $type->slug, 'name' => $type->name]);
        $view->amenities = Amenity::orderBy('name')->get()->map(fn($amenity) => ['id' => $amenity->id, 'name' => $amenity->name]);
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
                'label' => 'Boats',
                'link' => route('admin.boats.index'),
                'icon' => 'o-circle-stack',
            ],
            [
                'label' => 'Edit Boat',
                'icon' => 'o-pencil',
            ],
        ];
    @endphp

    <x-header title="Edit Boat" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Update boat information</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back to Boats" link="{{ route('admin.boats.index') }}"
                class="btn-primary btn-soft" responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="mt-3 md:mt-5">
        <x-form wire:submit="save">
            {{-- Basic Information --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <x-input wire:model.blur="name" label="Boat Name" placeholder="e.g., Marina 1, VIP Limousine"
                    icon="o-tag" hint="Display name for the boat (slug will be auto-generated)" />

                <x-choices-offline wire:model.live="service_type" label="Service Type" icon="o-squares-2x2"
                    :options="$serviceTypes" placeholder="Select service type..." single searchable />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input wire:model="location" label="Location" placeholder="e.g., Marina Bay, Terminal 1"
                        icon="o-map-pin" hint="Where the boat is stationed" />
                    <x-input wire:model="sort_order" type="number" label="Sort Order" placeholder="0"
                        icon="o-arrows-up-down" hint="Display order (lower numbers appear first)" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-input wire:model="min_passengers" type="number" label="Minimum Passengers" placeholder="e.g., 1"
                        icon="o-users" hint="Minimum capacity" min="1" />

                    <x-input wire:model="max_passengers" type="number" label="Maximum Passengers"
                        placeholder="e.g., 10" icon="o-users" hint="Maximum capacity" min="1" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <x-toggle wire:model="is_active" label="Active Status" hint="Enable or disable this boat" />
                    </div>

                    <div class="flex items-center">
                        <x-toggle wire:model="is_featured" label="Featured" hint="Show on homepage" />
                    </div>

                    <div class="flex items-center">
                        <x-toggle wire:model="is_under_maintenance" label="Under Maintenance"
                            hint="Toggle when boat needs maintenance" class="text-warning" />
                    </div>

                    @if ($is_under_maintenance)
                        <div class="md:col-span-2">
                            <x-input wire:model="maintenance_note" label="Maintenance Note"
                                placeholder="e.g., Engine maintenance - Available from Jan 15"
                                icon="o-wrench-screwdriver" hint="This message will be shown to customers" />
                        </div>
                    @endif
                </div>
                <x-choices-offline wire:model="amenity_ids" label="Amenities & Features" placeholder="Select amenities"
                    :options="$amenities" icon="o-sparkles" hint="Select one or more amenities available on this boat"
                    searchable clearable>
                    <x-slot:append>
                        <x-button icon="o-plus" label="Add Amenity" class="btn-primary join-item btn-sm md:btn-md"
                            @click="$wire.addAmenityModal = true" responsive />
                    </x-slot:append>
                </x-choices-offline>

                <x-input wire:model="buffer_time" type="number" label="Buffer Time (Minutes)" placeholder="0"
                    icon="o-clock" hint="Time between bookings for cleaning/preparation" min="0" />
            </div>
            {{-- Pricing Section --}}
            <div class="mt-6 md:mt-8">
                <x-card class="px-0" shadow>
                    @if ($service_type === 'yacht' || $service_type === 'taxi')
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-input label="1 Hour Price (KD)" type="number" step="0.01"
                                    wire:model="price_1hour" placeholder="0.00" />
                                <x-input label="2 Hours Price (KD)" type="number" step="0.01"
                                    wire:model="price_2hours" placeholder="0.00" />
                                <x-input label="3 Hours Price (KD)" type="number" step="0.01"
                                    wire:model="price_3hours" placeholder="0.00" />
                                <x-input label="Additional Hour (KD)" type="number" step="0.01"
                                    wire:model="additional_hour_price" placeholder="0.00" />
                            </div>
                        </div>
                    @elseif($service_type === 'ferry')
                        <div class="space-y-4">
                            <h4 class="font-semibold text-base">Private Trip Pricing (Per Hour)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-input label="Weekday (KD/hour)" type="number" step="0.01"
                                    wire:model="ferry_private_weekday" placeholder="0.00"
                                    hint="Sunday to Thursday" />
                                <x-input label="Weekend (KD/hour)" type="number" step="0.01"
                                    wire:model="ferry_private_weekend" placeholder="0.00"
                                    hint="Friday to Saturday" />
                            </div>
                            <h4 class="font-semibold text-base mt-4">Public Trip Pricing (Per Person/Hour)</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-input label="Weekday (KD/person/hour)" type="number" step="0.01"
                                    wire:model="ferry_public_weekday" placeholder="0.00" hint="Sunday to Thursday" />
                                <x-input label="Weekend (KD/person/hour)" type="number" step="0.01"
                                    wire:model="ferry_public_weekend" placeholder="0.00" hint="Friday to Saturday" />
                            </div>
                        </div>
                    @elseif($service_type === 'limousine')
                        <div class="space-y-4">
                            <h4 class="font-semibold text-base">Time-based Pricing</h4>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <x-input label="15 Minutes (KD)" type="number" step="0.01"
                                    wire:model="price_15min" placeholder="0.00" />
                                <x-input label="30 Minutes (KD)" type="number" step="0.01"
                                    wire:model="price_30min" placeholder="0.00" />
                                <x-input label="Full Boat/Hour (KD)" type="number" step="0.01"
                                    wire:model="price_full_boat" placeholder="0.00" />
                            </div>
                        </div>
                    @else
                        <div class="text-center text-base-content/50 py-8">
                            <x-icon name="o-information-circle" class="w-12 h-12 mx-auto mb-2" />
                            <p>Select a service type to configure pricing</p>
                        </div>
                    @endif
                </x-card>
            </div>
            {{-- Meta & Display --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4 md:mt-6">
                <x-textarea wire:model="meta_keywords" label="Meta Keywords" hint="Separated by commas"
                    rows="3" />

                <x-textarea wire:model="meta_description" label="Meta Description" hint="Max 150 characters"
                    rows="3" class="md:col-span-2" />
            </div>

            {{-- Description Editor --}}
            <div class="mt-6 md:mt-8">
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
                    hint="Detailed description of the boat (HTML code editing enabled)" :config="$editorConfig" />
            </div>
            {{-- Images Section --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4 md:mt-6">
                <x-file wire:model="image" label="Main Boat Image" placeholder="Upload boat image" crop-after-change
                    :crop-config="$config2" hint="Max: 5MB">
                    <img src="{{ $existing_image ? (str_starts_with($existing_image, 'http') ? $existing_image : asset($existing_image)) : 'https://placehold.co/600x400' }}"
                        alt="Boat Image" class="rounded-md object-cover w-full h-35 md:h-40" />
                </x-file>

                <x-image-library wire:model="files" wire:library="library" :preview="$library" label="Gallery Images"
                    hint="Max 1MB per image" change-text="Change" crop-text="Crop" remove-text="Remove"
                    crop-title-text="Crop image" crop-cancel-text="Cancel" crop-save-text="Crop"
                    add-files-text="Add images" />
            </div>
            {{-- Form Actions --}}
            <div class="flex flex-col sm:flex-row justify-between gap-2 sm:gap-3 mt-6 md:mt-8 pt-4 md:pt-6 border-t">
                <x-button icon="o-x-mark" label="Cancel" link="{{ route('admin.boats.index') }}"
                    class="btn-error btn-outline" responsive />
                <x-button icon="o-check" label="Update Boat" type="submit" class="btn-primary" spinner="save"
                    responsive />
            </div>
        </x-form>
    </x-card>

    {{-- Add Amenity Modal --}}
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
