<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use App\Models\WebsiteSetting;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Collection;

new class extends Component {
    use Toast;

    #[Title('Website Settings')]
    public array $settings = [];

    public function mount(): void
    {
        $this->loadSettings();
    }

    private function loadSettings(): void
    {
        $allSettings = WebsiteSetting::orderBy('id')->get();

        foreach ($allSettings as $setting) {
            if ($setting->type === 'toggle') {
                $this->settings[$setting->key] = filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
            } else {
                $this->settings[$setting->key] = $setting->value ?? '';
            }
        }
    }

    #[Computed]
    public function groupedSettings(): array
    {
        $allSettings = WebsiteSetting::orderBy('id')->get();
        $grouped = [];

        foreach ($allSettings as $setting) {
            $category = $this->getCategoryForSetting($setting);
            $grouped[$category][] = $setting;
        }

        return $grouped;
    }

    private function getCategoryForSetting(WebsiteSetting $setting): string
    {
        $key = $setting->key;

        if (str_contains($key, 'facebook') || str_contains($key, 'twitter') || str_contains($key, 'instagram') || str_contains($key, 'linkedin') || str_contains($key, 'youtube') || str_contains($key, 'whatsapp')) {
            return 'Social Media Links';
        }

        if (str_contains($key, 'contact') || str_contains($key, 'map')) {
            return 'Contact Details';
        }

        if (str_contains($key, 'maintenance')) {
            return 'Maintenance Mode';
        }

        if (str_contains($key, 'gtag') || str_contains($key, 'meta_pixel')) {
            return 'Analytics Settings';
        }

        return 'Other Settings';
    }

    public function save(): void
    {
        $rules = $this->buildValidationRules();
        $this->validate($rules);

        $allSettings = WebsiteSetting::all();

        foreach ($allSettings as $setting) {
            $value = $this->settings[$setting->key] ?? '';

            if ($setting->type === 'toggle') {
                $value = $value ? 'on' : 'off';
            }

            $setting->update(['value' => $value]);
            Cache::forget("website_setting_{$setting->key}");
        }

        // Clear all relevant caches to ensure settings take effect immediately
        Cache::flush();
        Artisan::call('view:clear');
        Artisan::call('config:clear');

        $this->success('Website settings updated successfully.');
    }

    private function buildValidationRules(): array
    {
        $rules = [];
        $allSettings = WebsiteSetting::all();

        foreach ($allSettings as $setting) {
            $rule = '';
            switch ($setting->type) {
                case 'email':
                    $rule = 'nullable|email';
                    break;
                case 'url':
                    $rule = 'nullable|url';
                    break;
                case 'number':
                    $rule = 'nullable|numeric';
                    break;
                case 'text':
                    $rule = 'nullable|string|max:255';
                    break;
                case 'textarea':
                    $rule = 'nullable|string|max:65535';
                    break;
                case 'toggle':
                    $rule = 'boolean';
                    break;
                default:
                    $rule = 'nullable|string';
            }
            $rules["settings.{$setting->key}"] = $rule;
        }

        return $rules;
    }
};

?>

<div class="pb-4">
    <x-header title="Website Settings" separator />

    <x-form wire:submit="save">
        @foreach ($this->groupedSettings as $groupName => $groupSettings)
            <x-card title="{{ $groupName }}" class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                    @foreach ($groupSettings as $setting)
                        @if ($setting->type === 'text')
                            <x-input wire:model="settings.{{ $setting->key }}" label="{{ $setting->name }}"
                                placeholder="{{ $setting->value }}" icon="o-pencil" />
                        @elseif($setting->type === 'email')
                            <x-input wire:model="settings.{{ $setting->key }}" label="{{ $setting->name }}"
                                placeholder="{{ $setting->value }}" icon="o-envelope" type="email" />
                        @elseif($setting->type === 'url')
                            <x-input wire:model="settings.{{ $setting->key }}" label="{{ $setting->name }}"
                                placeholder="{{ $setting->value }}" icon="o-link" type="url" />
                        @elseif($setting->type === 'number')
                            <x-input wire:model="settings.{{ $setting->key }}" label="{{ $setting->name }}"
                                placeholder="{{ $setting->value }}" icon="o-hashtag" type="number" />
                        @elseif($setting->type === 'textarea')
                            <div class="md:col-span-2">
                                <x-textarea wire:model="settings.{{ $setting->key }}" label="{{ $setting->name }}"
                                    placeholder="{{ $setting->value }}" rows="3" />
                            </div>
                        @elseif($setting->type === 'toggle')
                            <div class="md:col-span-2">
                                <x-toggle wire:model.live="settings.{{ $setting->key }}"
                                    label="{{ $setting->name }}" />
                                @if ($setting->key === 'maintenance')
                                    <p class="text-sm text-gray-500 mt-2">
                                        When enabled, visitors will see a maintenance page. Admin users can still access
                                        the site.
                                    </p>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </x-card>
        @endforeach

        {{-- Form Actions --}}
        <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
            <x-button icon="o-check" label="Save Settings" type="submit" class="btn-primary" spinner="save"
                responsive />
        </div>
    </x-form>
</div>
