<?php

namespace App\Livewire\Admin\Testimonials;

use App\Models\Testimonial;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Storage;

class Edit extends Component
{
    use WithFileUploads, Toast;

    public ?Testimonial $testimonial = null;
    public $customer_name = '';
    public $customer_image;
    public $existingImage = null;
    public $customer_designation = '';
    public $testimonial_text = '';
    public $rating = 5;
    public $is_active = true;
    public $order = 0;
    public $isCreating = false;

    public function mount($id = null)
    {
        if ($id) {
            $this->testimonial = Testimonial::findOrFail($id);
            $this->customer_name = $this->testimonial->customer_name;
            $this->existingImage = $this->testimonial->customer_image;
            $this->customer_designation = $this->testimonial->customer_designation ?? '';
            $this->testimonial_text = $this->testimonial->testimonial;
            $this->rating = $this->testimonial->rating;
            $this->is_active = $this->testimonial->is_active;
            $this->order = $this->testimonial->order;
        } else {
            $this->isCreating = true;
        }
    }

    public function save()
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

        // Handle image upload
        if ($this->customer_image) {
            // Delete old image if updating
            if (!$this->isCreating && $this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }

            $data['customer_image'] = $this->customer_image->store('testimonials', 'public');
        }

        if ($this->isCreating) {
            Testimonial::create($data);
            $this->success('Testimonial created successfully.', redirectTo: route('admin.testimonials.index'));
        } else {
            $this->testimonial->update($data);
            $this->success('Testimonial updated successfully.', redirectTo: route('admin.testimonials.index'));
        }
    }

    public function render()
    {
        return view('livewire.admin.testimonials.edit');
    }
}
