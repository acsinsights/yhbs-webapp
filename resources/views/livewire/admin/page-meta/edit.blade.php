<?php

use Livewire\Volt\Component;
use App\Models\PageMeta;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;
    #[Title('Edit Page Meta')]
    public $pageName;
    public $metaTitle;
    public $metaDescription;
    public $metaKeywords;

    public $pageMeta;

    public function mount($id)
    {
        $this->pageMeta = PageMeta::findOrFail($id);
        $this->pageName = $this->pageMeta->page_name;
        $this->metaTitle = $this->pageMeta->meta_title;
        $this->metaDescription = $this->pageMeta->meta_description;
        $this->metaKeywords = $this->pageMeta->meta_keywords;
    }

    public function save()
    {
        $this->validate(
            [
                'metaTitle' => 'nullable|string|max:60',
                'metaDescription' => 'nullable|string|max:155',
                'metaKeywords' => 'nullable|string|max:500',
            ],
            [
                'metaTitle.max' => 'Meta title should be maximum 60 characters for optimal SEO.',
                'metaDescription.max' => 'Meta description should be maximum 155 characters for optimal SEO.',
            ],
        );

        $this->pageMeta->meta_title = $this->metaTitle;
        $this->pageMeta->meta_description = $this->metaDescription;
        $this->pageMeta->meta_keywords = $this->metaKeywords;
        $this->pageMeta->save();

        $this->success('Page meta updated successfully', redirectTo: route('admin.page-meta.index'));
    }
};
?>

<div class="max-w-4xl mx-auto px-2 sm:px-4">
    <div class="breadcrumbs mb-4 text-sm overflow-x-auto">
        <h1 class="text-xl sm:text-2xl font-bold mb-2">
            Edit Page Meta
        </h1>
        <ul>
            <li><a href="{{ route('admin.index') }}" wire:navigate>Dashboard</a></li>
            <li><a href="{{ route('admin.page-meta.index') }}" wire:navigate>Page Meta Settings</a></li>
            <li>Edit</li>
        </ul>
    </div>
    <hr class="mb-5">

    <x-form wire:submit.prevent="save" class="space-y-4">
        <div class="w-full overflow-hidden">
            <x-input label="Page Name" wire:model="pageName" readonly class="w-full" />
        </div>

        @php
            $titleLength = mb_strlen($metaTitle ?? '');
            $descLength = mb_strlen($metaDescription ?? '');
        @endphp

        <div class="form-control w-full overflow-hidden">
            <label class="label flex-wrap">
                <span class="label-text font-semibold text-xs sm:text-sm">Meta Title <span
                        class="text-error">*</span></span>
                <span class="label-text-alt text-xs">
                    <span
                        class="{{ $titleLength > 60 ? 'text-error' : ($titleLength >= 50 ? 'text-success' : 'text-warning') }}">
                        {{ $titleLength }}/60
                    </span>
                </span>
            </label>
            <x-input wire:model.live="metaTitle" placeholder="Enter meta title" class="w-full" />
            <label class="label">
                <span class="label-text-alt w-full block">
                    <div class="text-[10px] sm:text-xs text-base-content/70 mt-1 break-words max-w-full">
                        <p class="font-semibold mb-1">📌 SEO Guidelines:</p>
                        <ul class="list-disc list-inside space-y-0.5 pl-0">
                            <li class="break-words">Length: <strong>50-60 chars</strong></li>
                            <li class="break-words">Keyword at start</li>
                            <li class="break-words">Include brand (optional)</li>
                        </ul>
                    </div>
                    @if ($titleLength > 60)
                        <span class="text-error text-[10px] sm:text-xs mt-1 block break-words">⚠️ Too long!</span>
                    @elseif($titleLength < 50 && $titleLength > 0)
                        <span class="text-warning text-[10px] sm:text-xs mt-1 block break-words">💡 Add more
                            (50-60)</span>
                    @elseif($titleLength >= 50 && $titleLength <= 60)
                        <span class="text-success text-[10px] sm:text-xs mt-1 block">✅ Perfect!</span>
                    @endif
                </span>
            </label>
        </div>

        <div class="form-control w-full overflow-hidden">
            <label class="label flex-wrap">
                <span class="label-text font-semibold text-xs sm:text-sm">Meta Description</span>
                <span class="label-text-alt text-xs">
                    <span
                        class="{{ $descLength > 155 ? 'text-error' : ($descLength >= 140 ? 'text-success' : 'text-warning') }}">
                        {{ $descLength }}/155
                    </span>
                </span>
            </label>
            <x-textarea wire:model.live="metaDescription" rows="4" placeholder="Enter description"
                class="w-full" />
            <label class="label">
                <span class="label-text-alt w-full block">
                    <div class="text-[10px] sm:text-xs text-base-content/70 mt-1 break-words max-w-full">
                        <p class="font-semibold mb-1">📌 SEO Guidelines:</p>
                        <ul class="list-disc list-inside space-y-0.5 pl-0">
                            <li class="break-words">Length: <strong>140-155 chars</strong></li>
                            <li class="break-words">Include keyword + CTA</li>
                            <li class="break-words">Compelling text</li>
                        </ul>
                    </div>
                    @if ($descLength > 155)
                        <span class="text-error text-[10px] sm:text-xs mt-1 block">⚠️ Too long!</span>
                    @elseif($descLength < 140 && $descLength > 0)
                        <span class="text-warning text-[10px] sm:text-xs mt-1 block break-words">💡 Add more
                            (140-155)</span>
                    @elseif($descLength >= 140 && $descLength <= 155)
                        <span class="text-success text-[10px] sm:text-xs mt-1 block">✅ Perfect!</span>
                    @endif
                </span>
            </label>
        </div>

        <div class="form-control w-full overflow-hidden">
            <label class="label flex-wrap">
                <span class="label-text font-semibold text-xs sm:text-sm">Meta Keywords <span
                        class="text-base-content/50 text-xs">(Optional)</span></span>
            </label>
            <x-textarea wire:model="metaKeywords" rows="3" placeholder="Keywords (comma-separated)"
                class="w-full" />
            <label class="label">
                <span class="label-text-alt w-full block">
                    <div class="text-[10px] sm:text-xs text-base-content/50 mt-1 break-words max-w-full">
                        <p class="font-semibold mb-1">ℹ️ Note:</p>
                        <p class="break-words">Optional field for internal reference.</p>
                    </div>
                </span>
            </label>
        </div>

        <x-slot:actions>
            <x-button label="Cancel" link="{{ route('admin.page-meta.index') }}" class="btn-sm sm:btn-md" />
            <x-button label="Save" class="btn-primary btn-sm sm:btn-md" type="submit" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>
