<?php

namespace App\Livewire\Admin\PolicyPages;

use App\Models\PolicyPage;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use WithPagination, Toast;

    public $search = '';
    public $sortBy = 'id';
    public $sortDirection = 'desc';

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
        $page = PolicyPage::findOrFail($id);
        $page->update(['is_active' => !$page->is_active]);

        $this->success('Status updated successfully.');
    }

    public function render()
    {
        $pages = PolicyPage::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                    ->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.policy-pages.index', [
            'pages' => $pages,
        ]);
    }
}
