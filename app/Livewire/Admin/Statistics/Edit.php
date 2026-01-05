<?php

namespace App\Livewire\Admin\Statistics;

use App\Models\Statistic;
use Livewire\Component;
use Livewire\WithFileUploads;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Storage;

class Edit extends Component
{
    use Toast, WithFileUploads;

    public Statistic $statistic;
    public $icon = ''; // Will hold image path
    public $new_icon; // For new image upload
    public $title = '';
    public $count = '';
    public $is_active = true;
    public $order = 0;

    public function mount(Statistic $statistic)
    {
        $this->statistic = $statistic;
        $this->icon = $statistic->icon ?? '';
        $this->title = $statistic->title;
        $this->count = $statistic->count;
        $this->is_active = $statistic->is_active;
        $this->order = $statistic->order;
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'new_icon' => 'nullable|image|max:2048', // 2MB max
            'count' => 'required|string|max:50',
            'is_active' => 'boolean',
            'order' => 'integer|min:0',
        ]);

        $data = [
            'title' => $this->title,
            'count' => $this->count,
            'is_active' => $this->is_active,
            'order' => $this->order,
        ];

        // Handle image upload
        if ($this->new_icon) {
            // Delete old icon if exists
            if ($this->statistic->icon && Storage::disk('public')->exists($this->statistic->icon)) {
                Storage::disk('public')->delete($this->statistic->icon);
            }

            // Store new icon
            $data['icon'] = $this->new_icon->store('statistics', 'public');
        }

        $this->statistic->update($data);

        $this->success('Statistic updated successfully.', redirectTo: route('admin.statistics.index'));
    }

    public function render()
    {
        return view('livewire.admin.statistics.edit');
    }
}
