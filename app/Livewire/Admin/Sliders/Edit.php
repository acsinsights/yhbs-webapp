<?php

namespace App\Livewire\Admin\Sliders;

use App\Models\Slider;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Storage;

class Edit extends Component
{
    use WithFileUploads, Toast;

    public ?Slider $slider = null;
    public $title = '';
    public $description = '';
    public $image;
    public $existingImage = null;
    public $button_text = '';
    public $button_link = '';
    public $is_active = true;
    public $order = 0;
    public $isCreating = false;

    public function mount($id = null)
    {
        if ($id) {
            $this->slider = Slider::findOrFail($id);
            $this->title = $this->slider->title;
            $this->description = $this->slider->description ?? '';
            $this->existingImage = $this->slider->image;
            $this->button_text = $this->slider->button_text ?? '';
            $this->button_link = $this->slider->button_link ?? '';
            $this->is_active = $this->slider->is_active;
            $this->order = $this->slider->order;
        } else {
            $this->isCreating = true;
        }
    }

    public function save()
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
            'button_link' => 'nullable|url',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ];

        if ($this->isCreating) {
            $rules['image'] = 'required|image|max:2048';
        } else {
            $rules['image'] = 'nullable|image|max:2048';
        }

        $this->validate($rules);

        $data = [
            'title' => $this->title,
            'description' => $this->description,
            'button_text' => $this->button_text,
            'button_link' => $this->button_link,
            'is_active' => $this->is_active,
            'order' => $this->order,
        ];

        // Handle image upload
        if ($this->image) {
            // Delete old image if updating
            if (!$this->isCreating && $this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }

            $data['image'] = $this->image->store('sliders', 'public');
        } elseif (!$this->isCreating && $this->existingImage) {
            // Preserve existing image when updating without new upload
            $data['image'] = $this->existingImage;
        }

        if ($this->isCreating) {
            Slider::create($data);
            $this->success('Slider created successfully.', redirectTo: route('admin.sliders.index'));
        } else {
            $this->slider->update($data);
            $this->success('Slider updated successfully.', redirectTo: route('admin.sliders.index'));
        }
    }

    public function render()
    {
        return view('livewire.admin.sliders.edit');
    }
}
