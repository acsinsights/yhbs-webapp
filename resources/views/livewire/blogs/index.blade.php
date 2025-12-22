<?php

use Mary\Traits\Toast;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use Livewire\Volt\Component;
use App\Models\Blog;

new class extends Component {
    use Toast, WithPagination;

    #[Url]
    public string $search = '';

    public array $sortBy = ['column' => 'id', 'direction' => 'desc'];
    public int $perPage = 10;

    public bool $createModal = false;
    public string $title = '';

    // Delete action
    public function delete($id): void
    {
        $blog = Blog::findOrFail($id);
        $blog->tags()->detach();
        $blog->delete();

        $this->success('Blog deleted successfully.');
    }

    public function openCreateModal(): void
    {
        $this->reset('title');
        $this->createModal = true;
    }

    public function createBlog(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
        ]);

        $blog = Blog::create([
            'title' => $this->title,
            'slug' => Str::slug($this->title),
            'is_published' => false,
        ]);

        $this->createModal = false;
        $this->success('Blog created. Redirecting to edit page...');

        $this->redirect(route('admin.blogs.edit', $blog->id));
    }

    public function headers(): array
    {
        return [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'image', 'label' => 'Image', 'class' => 'w-20'], ['key' => 'title', 'label' => 'Title', 'sortable' => true], ['key' => 'is_published', 'label' => 'Status', 'class' => 'w-24'], ['key' => 'date', 'label' => 'Date', 'sortable' => true, 'class' => 'w-32'], ['key' => 'actions', 'label' => 'Actions', 'class' => 'w-32']];
    }

    public function blogs()
    {
        return Blog::query()->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))->orderBy(...array_values($this->sortBy))->paginate($this->perPage);
    }

    public function with(): array
    {
        return [
            'blogs' => $this->blogs(),
            'headers' => $this->headers(),
        ];
    }
}; ?>

<div>
    <x-header title="Blogs" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Create Blog" icon="o-plus" wire:click="openCreateModal" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <x-table :headers="$headers" :rows="$blogs" :sort-by="$sortBy" with-pagination>
            @scope('cell_image', $blog)
                @if ($blog->image)
                    <img src="{{ $blog->image }}" alt="{{ $blog->title }}" class="w-16 h-16 object-cover rounded">
                @else
                    <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center">
                        <x-icon name="o-photo" class="w-8 h-8 text-gray-400" />
                    </div>
                @endif
            @endscope

            @scope('cell_is_published', $blog)
                @if ($blog->is_published)
                    <x-badge value="Published" class="badge-success" />
                @else
                    <x-badge value="Draft" class="badge-warning" />
                @endif
            @endscope

            @scope('cell_date', $blog)
                {{ $blog->date ? $blog->date->format('M d, Y') : '-' }}
            @endscope

            @scope('cell_actions', $blog)
                <div class="flex gap-2">
                    <x-button icon="o-pencil" link="{{ route('admin.blogs.edit', $blog->id) }}" spinner class="btn-sm" />
                    <x-button icon="o-trash" wire:click="delete({{ $blog->id }})" wire:confirm="Are you sure?" spinner
                        class="btn-sm btn-error" />
                </div>
            @endscope>

            <x-slot:empty>
                <x-empty icon="o-document-text" message="No blogs published yet" description="Check back soon for updates!" />
            </x-slot>
        </x-table>
    </x-card>

    {{-- Create Modal --}}
    <x-modal wire:model="createModal" title="Create Blog" class="backdrop-blur">
        <div class="space-y-4">
            <x-input label="Blog Title *" wire:model="title" placeholder="Enter blog title" />
        </div>

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.createModal = false" />
            <x-button label="Create & Continue" class="btn-primary" wire:click="createBlog" spinner="createBlog" />
        </x-slot:actions>
    </x-modal>
</div>
