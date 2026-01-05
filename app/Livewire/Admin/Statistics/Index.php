<?php

namespace App\Livewire\Admin\Statistics;

use App\Models\Statistic;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use WithPagination, Toast;

    public $search = '';
    public $sortBy = 'order';
    public $sortDirection = 'asc';

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
        $statistic = Statistic::findOrFail($id);
        $statistic->update(['is_active' => !$statistic->is_active]);

        $this->success('Status updated successfully.');
    }

    public function render()
    {
        $statistics = Statistic::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                    ->orWhere('count', 'like', "%{$this->search}%");
            })
            ->orderBy($this->sortBy, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.statistics.index', [
            'statistics' => $statistics,
        ]);
    }
}
