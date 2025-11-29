<?php

use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Http\UploadedFile;
use Livewire\WithFileUploads;
use Livewire\Volt\Component;
use Mary\Traits\{Toast, WithMediaSync};
use App\Models\Hotel;

new class extends Component {
    use Toast, WithFileUploads, WithMediaSync;

    public Hotel $hotel;

    public string $name = '';
    public string $slug = '';
    public ?UploadedFile $image = null;
    public ?string $existing_image = null;
    public ?string $description = null;

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

    public function mount(Hotel $hotel): void
    {
        $this->hotel = $hotel;
        $this->name = $hotel->name;
        $this->slug = $hotel->slug;
        $this->existing_image = $hotel->image;
        $this->image = null; // Keep null for file upload, use existing_image for display
        $this->description = $hotel->description;
        $this->library = $hotel->library ?? new Collection();
    }

    public function update(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:hotels,slug,' . $this->hotel->id,
            'image' => 'nullable|image|max:5000',
            'files.*' => 'image|max:5000',
            'description' => 'nullable|string',
            'library' => 'nullable',
        ]);

        // Handle single image upload - keep existing if no new upload
        $imagePath = $this->existing_image;
        if ($this->image instanceof UploadedFile) {
            $url = $this->image->store('hotels', 'public');
            $imagePath = "/storage/$url";
        }

        $this->hotel->update([
            'name' => $this->name,
            'slug' => Str::slug($this->slug),
            'image' => $imagePath,
            'description' => $this->description,
        ]);

        // Sync media files and update library metadata
        $this->syncMedia(model: $this->hotel, library: 'library', files: 'files', storage_subpath: '/hotels/library', model_field: 'library', visibility: 'public', disk: 'public');

        $this->success('Hotel updated successfully.', redirectTo: route('admin.hotels.index'));
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
                'label' => 'Hotels',
                'link' => route('admin.hotels.index'),
                'icon' => 'o-building-office-2',
            ],
            [
                'label' => 'Edit Hotel',
                'icon' => 'o-pencil',
            ],
        ];
    @endphp

    <x-header title="Edit Hotel" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Update hotel information</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back to Hotels" link="{{ route('admin.hotels.index') }}"
                class="btn-primary btn-soft" responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow class="mt-3 md:mt-5">
        <x-form wire:submit="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <x-input wire:model="name" label="Hotel Name" placeholder="Enter hotel name" icon="o-building-office-2"
                    hint="The slug will be auto-generated from the name" />

                <x-input wire:model="slug" label="Slug" placeholder="hotel-slug" icon="o-link"
                    hint="URL-friendly version of the name" />

                <x-file wire:model="image" label="Hotel Image" placeholder="Upload hotel image" crop-after-change
                    :crop-config="$config2" hint="Max: 5MB">
                    <img src="{{ $existing_image ? (str_starts_with($existing_image, 'http') ? $existing_image : asset($existing_image)) : 'https://placehold.co/600x400' }}"
                        alt="Hotel Image" class="rounded-md object-cover w-full h-35 md:h-40" />
                </x-file>

                <x-image-library wire:model="files" wire:library="library" :preview="$library"
                    label="Hotel Images Gallery" hint="Max 5MB per image" change-text="Change" crop-text="Crop"
                    remove-text="Remove" crop-title-text="Crop image" crop-cancel-text="Cancel" crop-save-text="Crop"
                    add-files-text="Add images" />
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
                    hint="Detailed description of the hotel (HTML code editing enabled)" :config="$editorConfig" />
            </div>

            {{-- Form Actions --}}
            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 mt-6 md:mt-8 pt-4 md:pt-6 border-t">
                <x-button icon="o-x-mark" label="Cancel" link="{{ route('admin.hotels.index') }}"
                    class="btn-warning btn-soft" responsive />
                <x-button icon="o-check" label="Update Hotel" type="submit" class="btn-primary" spinner="update"
                    responsive />
            </div>
        </x-form>
    </x-card>
</div>
