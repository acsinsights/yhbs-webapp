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
        $this->validate([
            'metaTitle' => 'nullable|string|max:60',
            'metaDescription' => 'nullable|string|max:155',
            'metaKeywords' => 'nullable|string|max:500',
        ], [
            'metaTitle.max' => 'Meta title should be maximum 60 characters for optimal SEO.',
            'metaDescription.max' => 'Meta description should be maximum 155 characters for optimal SEO.',
        ]);

        $this->pageMeta->meta_title = $this->metaTitle;
        $this->pageMeta->meta_description = $this->metaDescription;
        $this->pageMeta->meta_keywords = $this->metaKeywords;
        $this->pageMeta->save();
        
        $this->success('Page meta updated successfully', redirectTo: route('admin.page-meta.index'));
    }
};
?>

<div>
    <div class="breadcrumbs mb-4 text-sm">
        <h1 class="text-2xl font-bold mb-2">
            Edit Page Meta
        </h1>
        <ul>
            <li><a href="{{ route('admin.index') }}" wire:navigate>Dashboard</a></li>
            <li><a href="{{ route('admin.page-meta.index') }}" wire:navigate>Page Meta Settings</a></li>
            <li>Edit</li>
        </ul>
    </div>
    <hr class="mb-5">
    
    <x-form wire:submit.prevent="save">
        <x-input label="Page Name" wire:model="pageName" readonly />
        
        @php
            $titleLength = mb_strlen($metaTitle ?? '');
            $descLength = mb_strlen($metaDescription ?? '');
        @endphp
        
        <div class="form-control">
            <label class="label">
                <span class="label-text font-semibold">Meta Title <span class="text-error">*</span></span>
                <span class="label-text-alt">
                    <span class="{{ $titleLength > 60 ? 'text-error' : ($titleLength >= 50 ? 'text-success' : 'text-warning') }}">
                        {{ $titleLength }}/60
                    </span>
                </span>
            </label>
            <x-input wire:model.live="metaTitle" placeholder="Enter meta title (50-60 characters recommended)" />
            <label class="label">
                <span class="label-text-alt">
                    <div class="text-xs text-base-content/70 mt-1">
                        <p class="font-semibold mb-1">📌 SEO Guidelines:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            <li>Ideal length: <strong>50-60 characters</strong></li>
                            <li>Primary keyword <strong>start mein rakho</strong></li>
                            <li>Brand name include karo (optional)</li>
                        </ul>
                    </div>
                    @if($titleLength > 60)
                        <span class="text-error text-xs mt-1">⚠️ Title too long! Google will truncate it.</span>
                    @elseif($titleLength < 50 && $titleLength > 0)
                        <span class="text-warning text-xs mt-1">💡 Consider adding more characters (50-60 recommended)</span>
                    @elseif($titleLength >= 50 && $titleLength <= 60)
                        <span class="text-success text-xs mt-1">✅ Perfect length for SEO!</span>
                    @endif
                </span>
            </label>
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-semibold">Meta Description</span>
                <span class="label-text-alt">
                    <span class="{{ $descLength > 155 ? 'text-error' : ($descLength >= 140 ? 'text-success' : 'text-warning') }}">
                        {{ $descLength }}/155
                    </span>
                </span>
            </label>
            <x-textarea wire:model.live="metaDescription" rows="4" placeholder="Enter meta description (140-155 characters recommended)" />
            <label class="label">
                <span class="label-text-alt">
                    <div class="text-xs text-base-content/70 mt-1">
                        <p class="font-semibold mb-1">📌 SEO Guidelines:</p>
                        <ul class="list-disc list-inside space-y-0.5">
                            <li>Ideal length: <strong>140-155 characters</strong></li>
                            <li>Include <strong>keyword + benefits + CTA</strong></li>
                            <li>Compelling description jo users ko click karne ke liye motivate kare</li>
                        </ul>
                    </div>
                    @if($descLength > 155)
                        <span class="text-error text-xs mt-1">⚠️ Description too long! Google will truncate it.</span>
                    @elseif($descLength < 140 && $descLength > 0)
                        <span class="text-warning text-xs mt-1">💡 Consider adding more details (140-155 recommended)</span>
                    @elseif($descLength >= 140 && $descLength <= 155)
                        <span class="text-success text-xs mt-1">✅ Perfect length for SEO!</span>
                    @endif
                </span>
            </label>
        </div>

        <div class="form-control">
            <label class="label">
                <span class="label-text font-semibold">Meta Keywords <span class="text-base-content/50 text-xs">(Optional)</span></span>
            </label>
            <x-textarea wire:model="metaKeywords" rows="3" placeholder="Comma-separated keywords (e.g., keyword1, keyword2, keyword3)" />
            <label class="label">
                <span class="label-text-alt">
                    <div class="text-xs text-base-content/50 mt-1">
                        <p class="font-semibold mb-1">ℹ️ Note:</p>
                        <p>Google ignores meta keywords. This field is optional and mainly for internal reference.</p>
                    </div>
                </span>
            </label>
        </div>
        
        <x-slot:actions>
            <x-button label="Cancel" link="{{ route('admin.page-meta.index') }}" />
            <x-button label="Save" class="btn-primary" type="submit" spinner="save" />
        </x-slot:actions>
    </x-form>
</div>
