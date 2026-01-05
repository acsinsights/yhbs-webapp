<?php

namespace App\Livewire\Admin\Sliders;

use App\Models\Slider;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Storage;

class Index extends Component
{
    use WithPagination, Toast;

    public $search = '';
    public $sortBy = 'order';
    public $sortDirection = 'asc';
    public $showCreateModal = false;
    public $title = '';

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function sortByField($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleStatus($id)
    {
        $slider = Slider::findOrFail($id);
        $slider->update(['is_active' => !$slider->is_active]);

        $this->success('Status updated successfully.');
    }

    public function delete($id)
    {
        $slider = Slider::findOrFail($id);

        // Delete image if exists
        if ($slider->image) {
            Storage::disk('public')->delete($slider->image);
        }

        $slider->delete();
        $this->success('Slider deleted successfully.');
    }

    public function create()
    {
        $this->validate([
            'title' => 'required|string|max:255',
        ]);

        $slider = Slider::create([
            'title' => $this->title,
            'description' => '',
            'image' => null,
            'is_active' => false,
            'order' => Slider::max('order') + 1,
        ]);

        return $this->redirect(route('admin.sliders.edit', $slider->id), navigate: true);
    }

    public function render()
    {
        $sliders = Slider::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                    ->orWhere('description', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.sliders.index', [
            'sliders' => $sliders,
        ]);
    }
}
