<?php

use App\Models\Yatch;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads, Toast;

    #[Title('Edit Yacht')]
    public Yatch $yatch;
    public string $name = '';
    public string $slug = '';
    public $image = null;
    public ?string $description = '';
    public ?int $sku = null;
    public ?float $price = null;
    public ?float $discount_price = null;
    public array $library = [];
    public $config = ['aspectRatio' => 16 / 9];

    public function mount($yatch): void
    {
        $this->yatch = $yatch instanceof Yatch ? $yatch : Yatch::findOrFail($yatch);
        $this->name = $this->yatch->name;
        $this->slug = $this->yatch->slug;
        $this->description = $this->yatch->description;
        $this->sku = $this->yatch->sku;
        $this->price = $this->yatch->price;
        $this->discount_price = $this->yatch->discount_price;
        $this->library = $this->yatch->library ?? [];
    }

    public function updatedName($value)
    {
        $this->slug = Str::slug($value);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:yatches,slug,' . $this->yatch->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'description' => 'nullable|string',
            'sku' => 'nullable|integer',
            'price' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'library' => 'nullable|array',
        ]);

        $this->yatch->name = $validated['name'];
        $this->yatch->slug = $validated['slug'];
        $this->yatch->description = $validated['description'];
        $this->yatch->sku = $validated['sku'];
        $this->yatch->price = $validated['price'];
        $this->yatch->discount_price = $validated['discount_price'];
        $this->yatch->library = !empty($validated['library']) ? $validated['library'] : null;

        if ($this->image) {
            // Delete old image if exists
            if ($this->yatch->image) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $this->yatch->image));
            }

            $url = $this->image->store('yatches', 'public');
            $this->yatch->image = "/storage/$url";
        }

        $this->yatch->save();

        $this->success('Yacht updated successfully!', redirectTo: route('admin.yatch.index'));
    }
}; ?>
@section('cdn')
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
@endsection
<div>
    <!-- Header Section -->
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="p-3 rounded-xl bg-gradient-to-br from-warning/20 to-warning/10">
                <x-icon name="o-pencil" class="w-8 h-8 text-warning" />
            </div>
            <div>
                <h1 class="text-3xl font-bold bg-gradient-to-r from-warning to-orange-500 bg-clip-text text-transparent">
                    Edit Yacht
                </h1>
                <p class="text-sm text-base-content/60 mt-1">Update yacht information</p>
            </div>
        </div>
        <div class="breadcrumbs text-sm">
            <ul>
                <li>
                    <a href="{{ route('admin.yatch.index') }}" wire:navigate class="hover:text-primary transition-colors">
                        <x-icon name="o-sparkles" class="w-4 h-4 inline mr-1" />
                        Yachts
                    </a>
                </li>
                <li>
                    <a href="{{ route('admin.yatch.show', $yatch) }}" wire:navigate
                        class="hover:text-primary transition-colors">
                        {{ $yatch->name }}
                    </a>
                </li>
                <li class="text-warning font-semibold">Edit</li>
            </ul>
        </div>
    </div>

    <x-card shadow class="border-2 border-warning/20 overflow-hidden">
        <!-- Card Header -->
        <div class="bg-gradient-to-r from-warning/10 via-warning/5 to-transparent p-4 border-b border-warning/20">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <x-icon name="o-document-text" class="w-6 h-6 text-warning" />
                Edit Yacht Information
            </h2>
        </div>

        <x-form wire:submit="save" class="p-6">
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- Left Column -->
                <div class="space-y-6">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 mb-2">
                            <x-icon name="o-sparkles" class="w-5 h-5 text-primary" />
                            <span class="font-semibold">Basic Information</span>
                        </div>
                        <div class="divider my-2"></div>
                    </div>

                    <x-input label="Name" wire:model="name" placeholder="Enter yacht name"
                        hint="The slug will be auto-generated from the name" icon="o-tag" class="input-bordered" />

                    <x-input label="Slug" wire:model="slug" placeholder="yacht-slug"
                        hint="URL-friendly version of the name" icon="o-link" class="input-bordered" />

                    <x-input label="SKU" wire:model="sku" type="number" placeholder="Enter SKU" icon="o-hashtag"
                        class="input-bordered" />

                    <x-textarea label="Description" wire:model="description" placeholder="Enter yacht description"
                        rows="6" class="textarea-bordered" />

                    <div class="card bg-base-200/50 border-2 border-dashed border-base-300">
                        <div class="card-body p-4">
                            <x-file label="Main Image" wire:model="image" accept="image" crop-after-change
                                :crop-config="$config">
                                <div class="mt-3">
                                    @if ($image)
                                        <div class="relative group">
                                            <img src="{{ $image->temporaryUrl() }}"
                                                class="h-64 w-full object-cover rounded-xl shadow-lg border-2 border-warning/30"
                                                alt="Preview">
                                            <div
                                                class="absolute inset-0 bg-warning/0 group-hover:bg-warning/10 rounded-xl transition-all duration-300 flex items-center justify-center">
                                                <span
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity text-warning font-semibold">
                                                    Click to change
                                                </span>
                                            </div>
                                        </div>
                                    @elseif($yatch->image)
                                        <div class="relative group">
                                            <img src="{{ asset($yatch->image) }}"
                                                class="h-64 w-full object-cover rounded-xl shadow-lg border-2 border-base-300"
                                                alt="Current Image">
                                            <div
                                                class="absolute inset-0 bg-base-content/0 group-hover:bg-base-content/10 rounded-xl transition-all duration-300 flex items-center justify-center">
                                                <span
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity font-semibold">
                                                    Click to change image
                                                </span>
                                            </div>
                                        </div>
                                    @else
                                        <div
                                            class="h-64 w-full bg-gradient-to-br from-base-300 to-base-200 rounded-xl flex flex-col items-center justify-center border-2 border-dashed border-base-400 shadow-inner">
                                            <x-icon name="o-photo" class="w-16 h-16 text-base-content/30 mb-3" />
                                            <p class="text-sm text-base-content/60 font-medium">Upload yacht image</p>
                                            <p class="text-xs text-base-content/40 mt-1">Click to select or drag & drop
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            </x-file>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2 mb-2">
                            <x-icon name="o-currency-dollar" class="w-5 h-5 text-success" />
                            <span class="font-semibold">Pricing Information</span>
                        </div>
                        <div class="divider my-2"></div>
                    </div>

                    <x-input label="Price" wire:model="price" type="number" step="0.01" placeholder="0.00"
                        icon="o-currency-dollar" class="input-bordered input-lg" />

                    <x-input label="Discount Price" wire:model="discount_price" type="number" step="0.01"
                        placeholder="0.00" icon="o-tag" hint="Must be less than the regular price"
                        class="input-bordered" />

                    @if ($price && $discount_price && $discount_price < $price)
                        @php
                            $discountPercent = round((($price - $discount_price) / $price) * 100);
                        @endphp
                        <div class="alert alert-success shadow-lg">
                            <x-icon name="o-check-circle" class="w-6 h-6" />
                            <div>
                                <h3 class="font-bold">Great Deal!</h3>
                                <div class="text-sm">You're offering a <span
                                        class="font-bold text-success">{{ $discountPercent }}% discount</span></div>
                            </div>
                        </div>
                    @endif

                    <div class="divider">Metadata</div>

                    <div class="stats stats-vertical shadow-lg border-2 border-base-300">
                        <div class="stat bg-gradient-to-br from-base-200 to-base-100">
                            <div class="stat-title flex items-center gap-2">
                                <x-icon name="o-calendar" class="w-4 h-4" />
                                Created
                            </div>
                            <div class="stat-value text-lg">{{ $yatch->created_at->format('M d, Y') }}</div>
                            <div class="stat-desc">{{ $yatch->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="stat bg-gradient-to-br from-base-200 to-base-100">
                            <div class="stat-title flex items-center gap-2">
                                <x-icon name="o-clock" class="w-4 h-4" />
                                Last Updated
                            </div>
                            <div class="stat-value text-lg">{{ $yatch->updated_at->format('M d, Y') }}</div>
                            <div class="stat-desc">{{ $yatch->updated_at->diffForHumans() }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="divider my-6"></div>

            <x-slot:actions>
                <div class="flex justify-between w-full items-center">
                    <x-button label="Cancel" link="{{ route('admin.yatch.show', $yatch) }}"
                        class="btn-ghost hover:btn-error transition-all duration-300" icon="o-x-mark" />
                    <x-button label="Update Yacht"
                        class="btn-warning btn-lg shadow-lg hover:shadow-xl transition-all duration-300"
                        type="submit" spinner="save" icon="o-check" />
                </div>
            </x-slot:actions>
        </x-form>
    </x-card>
</div>
