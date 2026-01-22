<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\{Locked, Title};
use App\Models\Testimonial;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads, Toast;

    #[Locked]
    public ?Testimonial $testimonial = null;

    public string $customer_name = '';
    public string $customer_designation = '';
    public string $testimonial_text = '';
    public $customer_image;
    public ?string $existingImage = null;
    public int $rating = 5;
    public bool $is_active = true;
    public int $order = 0;
    public bool $isCreating = false;

    public function mount(?int $id = null): void
    {
        if ($id) {
            $this->testimonial = Testimonial::findOrFail($id);
            $this->customer_name = $this->testimonial->customer_name;
            $this->customer_designation = $this->testimonial->customer_designation ?? '';
            $this->testimonial_text = $this->testimonial->testimonial;
            $this->existingImage = $this->testimonial->customer_image;
            $this->rating = $this->testimonial->rating;
            $this->is_active = $this->testimonial->is_active;
            $this->order = $this->testimonial->order;
        } else {
            $this->isCreating = true;
        }
    }

    public function save(): void
    {
        $rules = [
            'customer_name' => 'required|string|max:255',
            'customer_designation' => 'nullable|string|max:255',
            'testimonial_text' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
            'customer_image' => 'nullable|image|max:2048',
        ];

        $this->validate($rules);

        $data = [
            'customer_name' => $this->customer_name,
            'customer_designation' => $this->customer_designation,
            'testimonial' => $this->testimonial_text,
            'rating' => $this->rating,
            'is_active' => $this->is_active,
            'order' => $this->order,
        ];

        if ($this->customer_image) {
            if (!$this->isCreating && $this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            $data['customer_image'] = $this->customer_image->store('testimonials', 'public');
        } elseif (!$this->isCreating && $this->existingImage) {
            $data['customer_image'] = $this->existingImage;
        }

        if ($this->isCreating) {
            Testimonial::create($data);
            $this->success('Testimonial created successfully.', redirectTo: route('admin.testimonials.index'));
        } else {
            $this->testimonial->update($data);
            $this->success('Testimonial updated successfully.', redirectTo: route('admin.testimonials.index'));
        }
    }
};

?>

<div class="pb-4">
    <x-header :title="$isCreating ? 'Create Testimonial' : 'Edit Testimonial'" separator>
        <x-slot:actions>
            <x-button label="Back" icon="o-arrow-left" link="{{ route('admin.testimonials.index') }}"
                class="btn-outline" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <x-input wire:model="customer_name" label="Customer Name" placeholder="John Doe" icon="o-user" required />

                <x-input wire:model="customer_designation" label="Designation" placeholder="CEO, Company Name"
                    icon="o-briefcase" />
            </div>

            <div class="mt-4">
                <x-textarea wire:model="testimonial_text" label="Testimonial"
                    placeholder="Enter customer testimonial..." rows="4" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6 mt-4">
                <div>
                    <label class="label">
                        <span class="label-text">Rating</span>
                    </label>
                    <select wire:model="rating" class="select select-bordered w-full">
                        @for ($i = 1; $i <= 5; $i++)
                            <option value="{{ $i }}">{{ $i }} Star{{ $i > 1 ? 's' : '' }}</option>
                        @endfor
                    </select>
                </div>

                <x-input wire:model="order" label="Order" placeholder="0" icon="o-bars-3" type="number" min="0"
                    hint="Display order (lower numbers first)" />
            </div>

            <div class="mt-4">
                <x-file wire:model="customer_image" label="Customer Image" accept="image/*"
                    hint="Upload customer photo (Max: 2MB, Recommended: Square image)">
                    <img src="{{ $existingImage ? asset('storage/' . $existingImage) : 'https://placehold.co/400x400' }}"
                        alt="Customer Image" class="rounded-full object-cover w-32 h-32 mx-auto shadow-md" />
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

            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 mt-6 md:mt-8 pt-4 md:pt-6 border-t">
                <x-button icon="o-x-mark" label="Cancel" link="{{ route('admin.testimonials.index') }}"
                    class="btn-error btn-outline" responsive />
                <x-button icon="o-check" label="{{ $isCreating ? 'Create' : 'Update' }}" type="submit"
                    class="btn-primary btn-outline" spinner="save" responsive />
            </div>
        </x-form>
    </x-card>
</div>
