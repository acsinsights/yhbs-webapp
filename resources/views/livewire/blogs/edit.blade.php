<?php

use Mary\Traits\Toast;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Livewire\Volt\Component;
use App\Models\Blog;
use App\Models\Tag;

new class extends Component {
    use Toast, WithFileUploads;

    public Blog $blog;
    public string $title = '';
    public string $description = '';
    public string $content = '';
    public $image = null;
    public ?string $existing_image = null;
    public bool $is_published = false;
    public ?string $date = null;
    public array $tags = [];
    public array $availableTags = [];

    public $config = [
        'aspectRatio' => 708 / 465,
        'viewMode' => 2,
    ];

    public function mount(Blog $blog): void
    {
        $this->blog = $blog;
        $this->title = $blog->title;
        $this->description = $blog->description ?? '';
        $this->content = $blog->content ?? '';
        $this->existing_image = $blog->image;
        $this->is_published = $blog->is_published;
        $this->date = $blog->date?->format('Y-m-d');
        $this->tags = $blog->tags->pluck('name')->toArray();
        $this->loadAvailableTags();
    }

    public function loadAvailableTags(): void
    {
        $this->availableTags = Tag::pluck('name')->toArray();
    }

    public function updatedTags($value, $key): void
    {
        // When tags are updated, check for new tags and create them
        if (is_array($this->tags)) {
            foreach ($this->tags as $tagName) {
                if ($tagName && !in_array($tagName, $this->availableTags)) {
                    Tag::firstOrCreate(['slug' => Str::slug($tagName)], ['name' => $tagName]);
                    $this->loadAvailableTags();
                }
            }
        }
    }

    public function save(): void
    {
        $this->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'date' => 'nullable|date',
        ]);

        $imagePath = $this->existing_image;
        if ($this->image) {
            $url = $this->image->store('blogs', 'public');
            $imagePath = $url; // Store relative path without /storage/ prefix
        }

        $this->blog->update([
            'title' => $this->title,
            'slug' => Str::slug($this->title),
            'description' => $this->description,
            'content' => $this->content,
            'image' => $imagePath,
            'is_published' => $this->is_published,
            'date' => $this->date,
        ]);

        // Sync tags
        $tagIds = [];
        foreach ($this->tags as $tagName) {
            $tag = Tag::firstOrCreate(['slug' => Str::slug($tagName)], ['name' => $tagName]);
            $tagIds[] = $tag->id;
        }
        $this->blog->tags()->sync($tagIds);

        $this->existing_image = $imagePath;
        $this->success('Blog updated successfully.');
    }

    public function with(): array
    {
        return [
            'blog' => $this->blog,
        ];
    }
}; ?>

<div>
    <x-header title="Edit Blog: {{ $blog->title }}" separator progress-indicator>
        <x-slot:actions>
            <x-button label="Back to Blogs" icon="o-arrow-left" link="{{ route('admin.blogs.index') }}" />
            <x-button label="Save Changes" icon="o-check" wire:click="save" spinner="save" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Left Column - Main Content (2/3 width) --}}
        <div class="lg:col-span-2 space-y-5">
            <x-card title="Blog Information">
                <div class="space-y-5">
                    <x-input label="Title *" wire:model="title" placeholder="Enter blog title" />

                    <x-textarea label="Short Description" wire:model="description"
                        placeholder="Enter a brief summary of your blog post..." rows="4"
                        hint="This will be shown in blog listings and search results" />

                    <x-tags label="Tags" wire:model="tags" icon="o-tag"
                        hint="Type and press Enter to add tags (e.g., tourism, yacht, marine)" />
                </div>
            </x-card>

            <x-card title="Blog Content">
                @php
                    $editorConfig = [
                        'plugins' => 'autoresize code lists link image table',
                        'toolbar' =>
                            'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
                        'min_height' => 400,
                        'max_height' => 600,
                        'statusbar' => false,
                        'lists_indent_on_tab' => true,
                        'list_class_list' => json_encode([
                            ['title' => 'Default', 'value' => ''],
                            ['title' => 'Circle', 'value' => 'circle'],
                            ['title' => 'Square', 'value' => 'square'],
                            ['title' => 'Disc', 'value' => 'disc'],
                        ]),
                        'content_style' =>
                            'body { font-family: Arial, sans-serif; font-size: 14px; } ul { list-style-type: disc; } ul.circle { list-style-type: circle; } ul.square { list-style-type: square; }',
                    ];
                @endphp
                <x-editor label="Content *" wire:model="content" :config="$editorConfig"
                    hint="Write your blog post content here" />
            </x-card>
        </div>

        {{-- Right Column - Sidebar (1/3 width) --}}
        <div class="space-y-5">
            <x-card title="Publishing">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-2">Publication Date</label>
                        <input type="date" wire:model="date" class="input input-bordered w-full"
                            placeholder="Select date" />
                        <p class="text-xs text-gray-500 mt-1">When this blog should be published</p>
                    </div>

                    <div class="divider my-2"></div>

                    <div class="form-control">
                        <label class="label cursor-pointer justify-start gap-3">
                            <input type="checkbox" wire:model="is_published" class="checkbox checkbox-primary" />
                            <div>
                                <span class="label-text font-medium">Publish Now</span>
                                <p class="text-xs text-gray-500">Make this blog visible to public</p>
                            </div>
                        </label>
                    </div>

                    @if ($is_published)
                        <div class="alert alert-success">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current"
                                fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="text-sm">This blog is published</span>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current"
                                fill="none" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="text-sm">Draft - Not visible to public</span>
                        </div>
                    @endif
                </div>
            </x-card>

            <x-card title="Featured Image">
                <div class="space-y-3">
                    <x-file label="Upload Image" wire:model="image" accept="image" crop-after-change :crop-config="$config"
                        hint="Recommended: 708x465 pixels">
                        <div class="w-full" style="max-width: 100%; aspect-ratio: 708/465;">
                            <img id="imagePreview"
                                src="{{ $existing_image ?: 'https://placehold.co/708x465/e2e8f0/64748b?text=No+Image' }}"
                                class="w-full h-full rounded-lg object-cover border-2 border-gray-200"
                                alt="Blog Image Preview">
                        </div>
                    </x-file>
                    <p class="text-xs text-gray-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="inline h-4 w-4" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        This image will be displayed at the top of your blog post
                    </p>
                </div>
            </x-card>

            <div class="flex flex-col gap-3">
                <x-button label="Save Changes" icon="o-check" wire:click="save" spinner="save"
                    class="btn-primary btn-block" />
                <x-button label="Back to List" icon="o-arrow-left" link="{{ route('admin.blogs.index') }}"
                    class="btn-outline btn-block" />
            </div>
        </div>
    </div>
</div>

@section('cdn')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/7.2.1/tinymce.min.js" referrerpolicy="origin"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />
@endsection
