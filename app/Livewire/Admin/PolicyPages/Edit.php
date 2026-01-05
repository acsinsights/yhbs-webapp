<?php

namespace App\Livewire\Admin\PolicyPages;

use App\Models\PolicyPage;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Str;

class Edit extends Component
{
    use Toast;

    public ?PolicyPage $policyPage = null;
    public $title = '';
    public $slug = '';
    public $content = '';
    public $is_active = true;
    public $isCreating = false;

    public function mount($id = null)
    {
        if ($id) {
            $this->policyPage = PolicyPage::findOrFail($id);
            $this->title = $this->policyPage->title;
            $this->slug = $this->policyPage->slug;
            $this->content = $this->policyPage->content ?? '';
            $this->is_active = $this->policyPage->is_active;
        } else {
            $this->isCreating = true;
        }
    }

    public function updatedTitle()
    {
        if ($this->isCreating || empty($this->slug)) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function save()
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:policy_pages,slug,' . ($this->policyPage->id ?? 'NULL'),
            'content' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data = [
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'is_active' => $this->is_active,
        ];

        if ($this->isCreating) {
            PolicyPage::create($data);
            $this->success('Policy page created successfully.', redirectTo: route('admin.policy-pages.index'));
        } else {
            $this->policyPage->update($data);
            $this->success('Policy page updated successfully.', redirectTo: route('admin.policy-pages.index'));
        }
    }

    public function render()
    {
        return view('livewire.admin.policy-pages.edit');
    }
}
