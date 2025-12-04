<?php

use Mary\Traits\Toast;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Volt\Component;
use App\Models\Category;

new class extends Component {
    use Toast, WithPagination, WithFileUploads;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public int $perPage = 10;

    public bool $createModal = false;
    public string $name = '';
    public $icon = null;
    public string $type = 'room';

    public bool $editModal = false;
    public ?Category $editingCategory = null;
    public string $edit_name = '';
    public $edit_icon = null;
    public ?string $existing_icon = null;
    public string $edit_type = 'room';

    public $config = ['aspectRatio' => 1];

    // Delete action
    public function delete($id): void
    {
        $category = Category::findOrFail($id);
        $category->delete();

        $this->success('Category deleted successfully.');
    }

    public function createCategory(): void
    {
        $this->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'icon' => 'nullable|image|mimes:jpeg,jpg,svg|max:2500',
            'type' => 'required|in:room,yacht',
        ]);

        $iconPath = null;
        if ($this->icon) {
            $url = $this->icon->store('categories', 'public');
            $iconPath = "/storage/$url";
        }

        Category::create([
            'name' => $this->name,
            'slug' => Str::slug($this->name),
            'icon' => $iconPath,
            'type' => $this->type,
        ]);

        $this->createModal = false;
        $this->reset('name', 'icon', 'type');
        $this->success('Category created successfully.');
    }

    public function openEditModal($id): void
    {
        $this->editingCategory = Category::findOrFail($id);
        $this->edit_name = $this->editingCategory->name;
        $this->existing_icon = $this->editingCategory->icon;
        $this->edit_icon = null;
        $this->edit_type = $this->editingCategory->type ?? 'room';
        $this->editModal = true;
    }

    public function updateCategory(): void
    {
        $this->validate([
            'edit_name' => 'required|string|max:255|unique:categories,name,' . $this->editingCategory->id,
            'edit_icon' => 'nullable|image|mimes:jpeg,jpg,svg|max:2500',
            'edit_type' => 'required|in:room,yacht',
        ]);

        $iconPath = $this->existing_icon;
        if ($this->edit_icon) {
            $url = $this->edit_icon->store('categories', 'public');
            $iconPath = "/storage/$url";
        }

        $this->editingCategory->update([
            'name' => $this->edit_name,
            'slug' => Str::slug($this->edit_name),
            'icon' => $iconPath,
            'type' => $this->edit_type,
        ]);

        $this->editModal = false;
        $this->reset('edit_name', 'edit_icon', 'existing_icon', 'edit_type', 'editingCategory');
        $this->success('Category updated successfully.');
    }

    public function rendering(View $view)
    {
        $view->categories = Category::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', "%{$this->search}%")->orWhere('slug', 'like', "%{$this->search}%");
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate($this->perPage);

        $view->headers = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'name', 'label' => 'Name', 'sortable' => true], ['key' => 'slug', 'label' => 'Slug', 'sortable' => true], ['key' => 'icon', 'label' => 'Icon', 'sortable' => false], ['key' => 'type', 'label' => 'Type', 'sortable' => true]];
    }
}; ?>
@section('cdn')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
@endsection
<div>
    @php
        $breadcrumbs = [
            [
                'link' => route('admin.index'),
                'icon' => 's-home',
            ],
            [
                'label' => 'Categories',
                'icon' => 'o-squares-2x2',
            ],
        ];
    @endphp

    <x-header title="Categories" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Manage all categories</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>

        <x-slot:actions>
            <x-input icon="o-magnifying-glass" placeholder="Search..." wire:model.live.debounce="search" clearable />
            <x-button icon="o-plus" class="btn-primary" tooltip="Add Category" @click="$wire.createModal = true" />
            <x-button icon="o-funnel" tooltip-left="Filters" class="btn-info" responsive />
        </x-slot:actions>
    </x-header>

    <x-card shadow>
        <x-table :headers="$headers" :rows="$categories" :sort-by="$sortBy" with-pagination per-page="perPage"
            :per-page-values="[10, 25, 50, 100]">
            @scope('cell_name', $category)
                <x-badge :value="$category->name" class="badge-soft badge-primary" />
            @endscope

            @scope('cell_slug', $category)
                <code class="text-xs bg-base-200 px-2 py-1 rounded">{{ $category->slug }}</code>
            @endscope

            @scope('cell_icon', $category)
                @if ($category->icon)
                    @if (str_starts_with($category->icon, 'http'))
                        <img src="{{ $category->icon }}" alt="{{ $category->name }}"
                            class="w-10 h-10 rounded object-cover" />
                    @else
                        <img src="{{ asset($category->icon) }}" alt="{{ $category->name }}"
                            class="w-10 h-10 rounded object-cover" />
                    @endif
                @else
                    <span class="text-base-content/50">—</span>
                @endif
            @endscope

            @scope('cell_type', $category)
                <x-badge :value="$category->type ?? 'room'" class="badge-soft badge-sm" />
            @endscope

            @scope('actions', $category)
                <div class="flex items-center gap-2">
                    <x-button icon="o-pencil" @click="$wire.openEditModal({{ $category->id }})" class="btn-ghost btn-sm"
                        tooltip="Edit" />
                    <x-button icon="o-trash" wire:click="delete({{ $category->id }})"
                        wire:confirm="Are you sure you want to delete this category?" spinner
                        class="btn-ghost btn-sm text-error" tooltip="Delete" />
                </div>
            @endscope

            <x-slot:empty>
                <x-empty icon="o-squares-2x2" message="No categories found" />
            </x-slot:empty>
        </x-table>
    </x-card>

    {{-- Create Category Modal --}}
    <x-modal wire:model="createModal" title="Create Category" class="backdrop-blur" max-width="md">
        <x-form wire:submit="createCategory">
            <div class="space-y-4">
                <x-input wire:model="name" label="Category Name" placeholder="Enter category name" icon="o-tag"
                    hint="Unique category name" />

                <x-select wire:model="type" label="Type" placeholder="Select type" :options="[['id' => 'room', 'name' => 'Room'], ['id' => 'yacht', 'name' => 'Yacht']]" option-value="id"
                    option-label="name" icon="o-squares-2x2" hint="Category type" />

                <x-file wire:model="icon" label="Category Icon" placeholder="Upload category icon" crop-after-change
                    :crop-config="$config" hint="Max: 2MB (JPG, JPEG, SVG)">
                    <img src="https://placehold.co/300" alt="Category Icon"
                        class="rounded-md w-full max-w-xs h-48 object-cover mx-auto" />
                </x-file>
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" @click="$wire.createModal = false"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Create Category" type="submit" class="btn-primary w-full sm:w-auto"
                        spinner="createCategory" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>

    {{-- Edit Category Modal --}}
    <x-modal wire:model="editModal" title="Edit Category" class="backdrop-blur" max-width="md">
        <x-form wire:submit="updateCategory">
            <div class="space-y-4">
                <x-input wire:model="edit_name" label="Category Name" placeholder="Enter category name" icon="o-tag"
                    hint="Unique category name" />

                <x-select wire:model="edit_type" label="Type" placeholder="Select type" :options="[['id' => 'room', 'name' => 'Room'], ['id' => 'yacht', 'name' => 'Yacht']]"
                    option-value="id" option-label="name" icon="o-squares-2x2" hint="Category type" />

                <x-file wire:model="edit_icon" label="Category Icon" placeholder="Upload category icon"
                    crop-after-change :crop-config="$config" hint="Max: 2MB (JPG, JPEG, SVG)">
                    <img src="{{ $existing_icon ? (str_starts_with($existing_icon, 'http') ? $existing_icon : asset($existing_icon)) : 'https://placehold.co/300' }}"
                        alt="Category Icon" class="rounded-md w-full max-w-xs h-48 object-cover mx-auto" />
                </x-file>
            </div>

            <x-slot:actions>
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                    <x-button icon="o-x-mark" label="Cancel" @click="$wire.editModal = false"
                        class="btn-ghost w-full sm:w-auto" responsive />
                    <x-button icon="o-check" label="Update Category" type="submit"
                        class="btn-primary w-full sm:w-auto" spinner="updateCategory" responsive />
                </div>
            </x-slot:actions>
        </x-form>
    </x-modal>
</div>
