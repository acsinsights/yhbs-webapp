<?php

use Livewire\Volt\Component;
use Livewire\Attributes\{Locked, Title};
use App\Models\PolicyPage;
use Mary\Traits\Toast;
use Illuminate\Support\Str;

new class extends Component {
    use Toast;

    #[Locked]
    public ?PolicyPage $policyPage = null;

    public string $title = '';
    public string $slug = '';
    public string $content = '';
    public bool $is_active = true;
    public bool $isCreating = false;

    public function mount(?int $id = null): void
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

    public function updatedTitle(): void
    {
        if ($this->isCreating) {
            $this->slug = Str::slug($this->title);
        }
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $this->isCreating ? 'unique:policy_pages,slug' : 'unique:policy_pages,slug,' . $this->policyPage->id],
            'content' => 'required|string',
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
};

?>

@section('cdn')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.2.1/tinymce.min.js" referrerpolicy="origin"></script>
@endsection

<div class="pb-4">
    <x-header :title="$isCreating ? 'Create Policy Page' : 'Edit Policy Page'" separator>
        <x-slot:actions>
            <x-button label="Back" icon="o-arrow-left" link="{{ route('admin.policy-pages.index') }}"
                class="btn-outline" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <x-input wire:model.live="title" label="Title" placeholder="Enter page title" icon="o-document-text"
                    required />

                <x-input wire:model="slug" label="Slug" placeholder="page-slug" icon="o-link"
                    hint="URL-friendly version of the title" required />
            </div>

            <div class="mt-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">Status</span>
                    </label>
                    <x-toggle wire:model="is_active" label="Active" />
                </div>
            </div>

            <div class="mt-4 md:mt-6">
                @php
                    $editorConfig = [
                        'valid_elements' => '*[*]',
                        'extended_valid_elements' => '*[*]',
                        'plugins' => 'code link lists table',
                        'toolbar' =>
                            'undo redo | styles | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table | code',
                        'height' => 500,
                    ];
                @endphp
                <x-editor wire:model="content" label="Content" hint="Page content with HTML support"
                    :config="$editorConfig" />
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3 mt-6 md:mt-8 pt-4 md:pt-6 border-t">
                <x-button icon="o-x-mark" label="Cancel" link="{{ route('admin.policy-pages.index') }}"
                    class="btn-error btn-outline" responsive />
                <x-button icon="o-check" label="{{ $isCreating ? 'Create' : 'Update' }}" type="submit"
                    class="btn-primary btn-outline" spinner="save" responsive />
            </div>
        </x-form>
    </x-card>
</div>
