<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Models\{Boat, BoatServiceType};
use Illuminate\Support\Str;

new class extends Component {
    use Toast, WithFileUploads;

    public Boat $boat;

    // Basic Info
    public string $name = '';
    public string $slug = '';
    public string $service_type = '';
    public ?string $description = null;
    public ?string $location = null;
    public ?string $features = null;

    // Inline service type creation
    public bool $addServiceTypeModal = false;
    public string $service_type_name = '';

    // Capacity
    public int $min_passengers = 1;
    public int $max_passengers = 10;

    // Marina/Taxi Pricing
    public ?float $price_1hour = null;
    public ?float $price_2hours = null;
    public ?float $price_3hours = null;
    public ?float $additional_hour_price = null;

    // Ferry Pricing
    public ?float $price_per_person_adult = null;
    public ?float $price_per_person_child = null;
    public ?float $private_trip_price = null;
    public ?float $private_trip_return_price = null;

    // Limousine Pricing
    public ?float $price_15min = null;
    public ?float $price_30min = null;
    public ?float $price_full_boat = null;

    // Meta & Status
    public ?string $meta_description = null;
    public ?string $meta_keywords = null;
    public bool $is_active = true;
    public bool $is_featured = false;
    public int $sort_order = 0;

    public $image;

    public function mount(Boat $boat): void
    {
        $this->boat = $boat;
        $this->fill($boat->toArray());
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:boats,slug,' . $this->boat->id,
            'service_type' => 'required|string|exists:boat_service_types,slug',
            'min_passengers' => 'required|integer|min:1',
            'max_passengers' => 'required|integer|min:1',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($this->image) {
            $path = $this->image->store('boats', 'public');
            $validated['image'] = $path;
        }

        $this->boat->update([...$validated, 'description' => $this->description, 'location' => $this->location, 'features' => $this->features, 'price_1hour' => $this->price_1hour, 'price_2hours' => $this->price_2hours, 'price_3hours' => $this->price_3hours, 'additional_hour_price' => $this->additional_hour_price, 'price_per_person_adult' => $this->price_per_person_adult, 'price_per_person_child' => $this->price_per_person_child, 'private_trip_price' => $this->private_trip_price, 'private_trip_return_price' => $this->private_trip_return_price, 'price_15min' => $this->price_15min, 'price_30min' => $this->price_30min, 'price_full_boat' => $this->price_full_boat, 'meta_description' => $this->meta_description, 'meta_keywords' => $this->meta_keywords, 'is_active' => $this->is_active, 'is_featured' => $this->is_featured, 'sort_order' => $this->sort_order]);

        $this->success('Boat updated successfully.', redirectTo: route('admin.boats.index'));
    }

    public function updatedName(): void
    {
        $this->slug = Str::slug($this->name);
    }

    public function saveServiceType(): void
    {
        $this->validate([
            'service_type_name' => 'required|string|max:255|unique:boat_service_types,name',
        ]);

        $serviceType = BoatServiceType::create([
            'name' => $this->service_type_name,
            'slug' => Str::slug($this->service_type_name),
            'is_active' => true,
        ]);

        $this->success('Service type created successfully.');
        $this->addServiceTypeModal = false;
        $this->reset('service_type_name');
        $this->service_type = $serviceType->slug;
    }

    public function with(): array
    {
        $serviceTypes = BoatServiceType::active()->ordered()->get()->map(fn($type) => ['id' => $type->slug, 'name' => $type->name]);

        return [
            'serviceTypes' => $serviceTypes,
            'breadcrumbs' => [['label' => 'Dashboard', 'url' => route('admin.index')], ['label' => 'Boats', 'link' => route('admin.boats.index')], ['label' => 'Edit ' . $this->boat->name]],
        ];
    }
}; ?>

<div>
    <x-header title="Edit Boat" separator>
        <x-slot:middle>
            <x-breadcrumbs :items="$breadcrumbs" class="text-sm text-gray-500" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back to Boats" link="{{ route('admin.boats.index') }}" class="btn-outline"
                responsive />
        </x-slot:actions>
    </x-header>

    <form wire:submit="save">
        <div class="grid gap-5 lg:grid-cols-3">
            {{-- Main Form --}}
            <div class="lg:col-span-2 space-y-5">
                {{-- Basic Information --}}
                <x-card title="Basic Information">
                    <div class="grid gap-4">
                        <x-input label="Boat Name *" wire:model.blur="name" placeholder="Enter boat name" />
                        <x-input label="Slug *" wire:model="slug" placeholder="boat-slug" hint="URL-friendly name" />

                        <x-choices-offline label="Service Type *" icon="o-tag" wire:model.live="service_type"
                            :options="$serviceTypes" placeholder="Select service type..." single searchable>
                            <x-slot:append>
                                <x-button icon="o-plus" label="Add" class="btn-primary join-item btn-sm"
                                    @click="$wire.addServiceTypeModal = true" responsive />
                            </x-slot:append>
                        </x-choices-offline>

                        <x-textarea label="Description" wire:model="description" placeholder="Enter boat description"
                            rows="4" />

                        <div class="grid grid-cols-2 gap-4">
                            <x-input label="Location" wire:model="location" placeholder="Marina, Terminal, etc." />
                            <x-input label="Sort Order" type="number" wire:model="sort_order" />
                        </div>

                        <x-textarea label="Features" wire:model="features"
                            placeholder="List boat features and amenities" rows="3" />
                    </div>
                </x-card>

                {{-- Capacity --}}
                <x-card title="Passenger Capacity">
                    <div class="grid grid-cols-2 gap-4">
                        <x-input label="Minimum Passengers *" type="number" wire:model="min_passengers"
                            min="1" />
                        <x-input label="Maximum Passengers *" type="number" wire:model="max_passengers"
                            min="1" />
                    </div>
                </x-card>

                {{-- Pricing based on Service Type --}}
                <x-card title="Pricing Information">
                    @if ($service_type === 'marina_trip' || $service_type === 'taxi')
                        <div class="space-y-3">
                            <h4 class="font-semibold">Hourly Pricing</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <x-input label="1 Hour Price (KD)" type="number" step="0.01"
                                    wire:model="price_1hour" />
                                <x-input label="2 Hours Price (KD)" type="number" step="0.01"
                                    wire:model="price_2hours" />
                                <x-input label="3 Hours Price (KD)" type="number" step="0.01"
                                    wire:model="price_3hours" />
                                <x-input label="Additional Hour (KD)" type="number" step="0.01"
                                    wire:model="additional_hour_price" />
                            </div>
                        </div>
                    @elseif($service_type === 'ferry')
                        <div class="space-y-3">
                            <h4 class="font-semibold">Per Person Pricing</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <x-input label="Adult Price (KD)" type="number" step="0.01"
                                    wire:model="price_per_person_adult" />
                                <x-input label="Child Price (KD)" type="number" step="0.01"
                                    wire:model="price_per_person_child" />
                            </div>
                            <h4 class="font-semibold mt-4">Private Trip Pricing</h4>
                            <div class="grid grid-cols-2 gap-4">
                                <x-input label="One Way (KD)" type="number" step="0.01"
                                    wire:model="private_trip_price" />
                                <x-input label="Return (KD)" type="number" step="0.01"
                                    wire:model="private_trip_return_price" />
                            </div>
                        </div>
                    @elseif($service_type === 'limousine')
                        <div class="space-y-3">
                            <h4 class="font-semibold">Time-based Pricing</h4>
                            <div class="grid grid-cols-3 gap-4">
                                <x-input label="15 Minutes (KD)" type="number" step="0.01"
                                    wire:model="price_15min" />
                                <x-input label="30 Minutes (KD)" type="number" step="0.01"
                                    wire:model="price_30min" />
                                <x-input label="Full Boat/Hour (KD)" type="number" step="0.01"
                                    wire:model="price_full_boat" />
                            </div>
                        </div>
                    @else
                        <div class="text-center text-gray-500 py-4">
                            Select a service type to configure pricing
                        </div>
                    @endif
                </x-card>

                {{-- SEO --}}
                <x-card title="SEO Information">
                    <div class="grid gap-4">
                        <x-textarea label="Meta Description" wire:model="meta_description"
                            placeholder="Brief description for search engines" rows="2" />
                        <x-input label="Meta Keywords" wire:model="meta_keywords"
                            placeholder="keyword1, keyword2, keyword3" />
                    </div>
                </x-card>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-5">
                {{-- Image Upload --}}
                <x-card title="Boat Image">
                    @if ($boat->image && !$image)
                        <div class="mb-4">
                            <img src="{{ asset('storage/' . $boat->image) }}" alt="{{ $boat->name }}"
                                class="w-full rounded-lg">
                        </div>
                    @endif

                    @if ($image)
                        <div class="mb-4">
                            <img src="{{ $image->temporaryUrl() }}" class="w-full rounded-lg">
                        </div>
                    @endif

                    <x-file wire:model="image" accept="image/*" />
                    <div class="text-xs text-gray-500 mt-2">Max size: 2MB. Formats: JPG, PNG</div>
                </x-card>

                {{-- Status --}}
                <x-card title="Status & Visibility">
                    <div class="space-y-3">
                        <x-checkbox label="Active" wire:model="is_active" hint="Make boat available for bookings" />
                        <x-checkbox label="Featured" wire:model="is_featured" hint="Show on homepage" />
                    </div>
                </x-card>

                {{-- Actions --}}
                <x-card>
                    <div class="space-y-2">
                        <x-button label="Save Changes" type="submit" icon="o-check" class="btn-primary w-full"
                            spinner="save" />
                        <x-button label="Cancel" link="{{ route('admin.boats.index') }}" icon="o-x-mark"
                            class="btn-outline w-full" />
                    </div>
                </x-card>
            </div>
        </div>
    </form>

    {{-- Add Service Type Modal --}}
    <x-modal wire:model="addServiceTypeModal" title="Add Service Type" class="backdrop-blur">
        <x-form wire:submit="saveServiceType">
            <x-input label="Service Type Name *" wire:model="service_type_name" icon="o-tag"
                placeholder="e.g., Marina Trip, Water Taxi, Ferry..." />

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.addServiceTypeModal = false" />
                <x-button label="Create" type="submit" icon="o-check" class="btn-primary"
                    spinner="saveServiceType" />
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
