<?php

use App\Models\WebsiteSetting;
use Mary\Traits\Toast;
use Livewire\Volt\Component;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    use Toast;

    public bool $editModal = false;
    public ?WebsiteSetting $editingSetting = null;
    public string $edit_value = '';
    public bool $edit_toggle_value = false;
    public string $edit_type = 'text';

    public function openEditModal($id): void
    {
        $this->editingSetting = WebsiteSetting::findOrFail($id);
        $this->edit_type = $this->editingSetting->type ?? 'text';

        if ($this->edit_type === 'toggle') {
            $this->edit_toggle_value = $this->editingSetting->value === 'on';
        } else {
            $this->edit_value = $this->editingSetting->value ?? '';
        }

        $this->editModal = true;
    }

    public function updateSetting(): void
    {
        if ($this->edit_type === 'toggle') {
            $value = $this->edit_toggle_value ? 'on' : 'off';
        } else {
            $this->validate([
                'edit_value' => $this->edit_type === 'number' ? 'nullable|numeric' : ($this->edit_type === 'email' ? 'nullable|email' : ($this->edit_type === 'url' ? 'nullable|url' : 'nullable|string|max:65535')),
            ]);
            $value = $this->edit_value;
        }

        $this->editingSetting->update([
            'value' => $value,
        ]);

        // Clear cache for this specific setting
        Cache::forget("website_setting_{$this->editingSetting->key}");

        $this->editModal = false;
        $this->reset('edit_value', 'edit_toggle_value', 'edit_type', 'editingSetting');
        $this->success('Setting updated successfully.');
    }

    public function rendering(View $view)
    {
        $view->settings = WebsiteSetting::orderBy('name')->get();
    }
}; ?>

<div>
    @php
        $breadcrumbs = [
            [
                'link' => route('admin.index'),
                'icon' => 's-home',
            ],
            [
                'label' => 'Website Settings',
                'icon' => 'o-cog-6-tooth',
            ],
        ];
    @endphp

    <x-header title="Website Settings" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/50 mb-2">Manage website configuration settings</p>
            <x-breadcrumbs :items="$breadcrumbs" separator="o-slash" class="mb-3" />
        </x-slot:subtitle>
    </x-header>

    <x-card shadow>
        <div class="space-y-2">
            @forelse($settings as $setting)
                <x-list-item :item="$setting">
                    <x-slot:avatar>
                        <x-badge :value="$setting->type === 'toggle' ? ($setting->value === 'on' ? 'Active' : 'Inactive') : ucfirst($setting->type)" :class="$setting->type === 'toggle' ? ($setting->value === 'on' ? 'badge-success badge-soft' : 'badge-error badge-soft') : 'badge-primary badge-soft'" />
                    </x-slot:avatar>

                    <x-slot:value>
                        <div class="font-semibold">{{ $setting->name }}</div>
                    </x-slot:value>

                    <x-slot:sub-value>
                        @if ($setting->type === 'toggle')
                            <span class="text-sm {{ $setting->value === 'on' ? 'text-success' : 'text-error' }}">
                                {{ $setting->value === 'on' ? 'Enabled' : 'Disabled' }}
                            </span>
                        @elseif($setting->key === 'currency_symbol')
                            <span class="text-sm text-base-content/70">{{ $setting->value ?: 'Not set' }}</span>
                        @elseif($setting->key === 'currency_name')
                            <span class="text-sm text-base-content/70">{{ $setting->value ?: 'Not set' }}</span>
                        @else
                            <span class="text-sm text-base-content/70">
                                {{ $setting->value ? Str::limit($setting->value, 50) : 'Not set' }}
                            </span>
                        @endif
                    </x-slot:sub-value>

                    <x-slot:actions>
                        <x-button icon="o-pencil" class="btn-sm btn-ghost"
                            @click="$wire.openEditModal({{ $setting->id }})" tooltip="Edit Setting" />
                    </x-slot:actions>
                </x-list-item>
            @empty
                <x-empty icon="o-cog-6-tooth" message="No settings found" />
            @endforelse
        </div>
    </x-card>

    {{-- Edit Setting Modal --}}
    <x-modal wire:model="editModal" title="Edit Setting" class="backdrop-blur" max-width="md">
        @if ($editingSetting)
            <x-form wire:submit="updateSetting">
                <div class="space-y-4">
                    <x-input :value="$editingSetting->name" label="Setting Name" disabled icon="o-tag" />

                    <x-input :value="$editingSetting->key" label="Setting Key" disabled icon="o-key" />

                    @if ($edit_type === 'toggle')
                        <x-toggle wire:model="edit_toggle_value" label="Status" hint="Enable or disable this setting" />
                    @elseif($edit_type === 'textarea')
                        <x-textarea wire:model="edit_value" label="Value" placeholder="Enter value"
                            hint="Enter the setting value" rows="5" />
                    @elseif($edit_type === 'number')
                        <x-input wire:model="edit_value" type="number" label="Value" placeholder="Enter numeric value"
                            hint="Enter a numeric value" icon="o-hashtag" />
                    @elseif($edit_type === 'email')
                        <x-input wire:model="edit_value" type="email" label="Value" placeholder="Enter email address"
                            hint="Enter a valid email address" icon="o-envelope" />
                    @elseif($edit_type === 'url')
                        <x-input wire:model="edit_value" type="url" label="Value" placeholder="Enter URL"
                            hint="Enter a valid URL" icon="o-link" />
                    @else
                        <x-input wire:model="edit_value" label="Value" placeholder="Enter value"
                            hint="Enter the setting value" icon="o-pencil" />
                    @endif
                </div>

                <x-slot:actions>
                    <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 w-full sm:w-auto">
                        <x-button icon="o-x-mark" label="Cancel" @click="$wire.editModal = false"
                            class="btn-ghost w-full sm:w-auto" responsive />
                        <x-button icon="o-check" label="Update Setting" type="submit"
                            class="btn-primary w-full sm:w-auto" spinner="updateSetting" responsive />
                    </div>
                </x-slot:actions>
            </x-form>
        @endif
    </x-modal>
</div>
