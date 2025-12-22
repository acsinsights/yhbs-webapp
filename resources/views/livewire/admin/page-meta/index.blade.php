<?php

use Livewire\Volt\Component;
use App\Models\PageMeta;
use Livewire\WithPagination;
use Illuminate\View\View;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;

new class extends Component {
    use WithPagination;
    #[Title('Page Meta Settings')]
    public $headers;
    #[Url]
    public string $search = '';

    public $sortBy = ['column' => 'page_name', 'direction' => 'asc'];
    
    // boot
    public function boot(): void
    {
        $this->headers = [
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'], 
            ['key' => 'page_name', 'label' => 'Page Name'], 
            ['key' => 'meta_title', 'label' => 'Meta Title'],
            ['key' => 'updated_at', 'label' => 'Last Updated']
        ];
    }

    public function rendering(View $view): void
    {
        $query = PageMeta::orderBy(...array_values($this->sortBy));
        
        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('page_name', 'like', "%{$this->search}%")
                  ->orWhere('meta_title', 'like', "%{$this->search}%");
            });
        }
        
        $view->pageMetas = $query->paginate(20);
        $view->title('Page Meta Settings');
    }
};
?>

<div>
    <div class="flex justify-between items-start lg:items-center flex-col lg:flex-row mt-3 mb-5 gap-2">
        <div>
            <h1 class="text-2xl font-bold mb-2">
                Page Meta Settings
            </h1>
            <div class="breadcrumbs text-sm">
                <ul class="flex">
                    <li>
                        <a href="{{ route('admin.index') }}" wire:navigate>
                            Dashboard
                        </a>
                    </li>
                    <li>
                        Page Meta Settings
                    </li>
                </ul>
            </div>
        </div>
        <div class="flex gap-3">
            <x-input placeholder="Search ..." icon="o-magnifying-glass" wire:model.live.debounce="search" />
        </div>
    </div>
    <hr class="mb-5">
    <x-table :headers="$headers" :rows="$pageMetas" with-pagination :sort-by="$sortBy">
        @scope('cell_meta_title', $meta)
            <p class="truncate w-64">{{ $meta->meta_title ?? 'Not set' }}</p>
        @endscope
        @scope('cell_updated_at', $meta)
            {{ $meta->updated_at->format('d M Y') }}
        @endscope
        @scope('actions', $meta)
            <div class="flex">
                <x-button icon="o-pencil" link="{{ route('admin.page-meta.edit', $meta->id) }}" class="btn-xs" />
            </div>
        @endscope
        <x-slot:empty>
            <x-empty icon="o-no-symbol" message="No page meta found" />
        </x-slot>
    </x-table>
</div>
