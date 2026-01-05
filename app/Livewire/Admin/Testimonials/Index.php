<?php

namespace App\Livewire\Admin\Testimonials;

use App\Models\Testimonial;
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
    public $customer_name = '';

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
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->update(['is_active' => !$testimonial->is_active]);

        $this->success('Status updated successfully.');
    }

    public function delete($id)
    {
        $testimonial = Testimonial::findOrFail($id);

        // Delete image if exists
        if ($testimonial->customer_image) {
            Storage::disk('public')->delete($testimonial->customer_image);
        }

        $testimonial->delete();
        $this->success('Testimonial deleted successfully.');
    }

    public function create()
    {
        $this->validate([
            'customer_name' => 'required|string|max:255',
        ]);

        $testimonial = Testimonial::create([
            'customer_name' => $this->customer_name,
            'testimonial' => '',
            'rating' => 5,
            'is_active' => false,
            'order' => Testimonial::max('order') + 1,
        ]);

        return $this->redirect(route('admin.testimonials.edit', $testimonial->id), navigate: true);
    }

    public function render()
    {
        $testimonials = Testimonial::query()
            ->when($this->search, function ($query) {
                $query->where('customer_name', 'like', "%{$this->search}%")
                    ->orWhere('testimonial', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.testimonials.index', [
            'testimonials' => $testimonials,
        ]);
    }
}
