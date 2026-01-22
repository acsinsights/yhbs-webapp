<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use App\Models\WebsiteSetting;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Cache;

new class extends Component {
    use Toast;

    // Social Media Links
    #[Title('Website Settings')]
    public string $facebook = '';
    public string $twitter = '';
    public string $instagram = '';
    public string $linkedin = '';
    public string $youtube = '';

    // Contact Details
    public string $contact_email = '';
    public string $contact_phone = '';
    public string $contact_address = '';
    public string $whatsapp = '';

    // Maintenance Mode
    public bool $maintenance_mode = false;
    public string $maintenance_message = '';

    public function mount(): void
    {
        $this->loadSettings();
    }

    private function loadSettings(): void
    {
        $settings = WebsiteSetting::whereIn('key', ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'contact_email', 'contact_phone', 'contact_address', 'whatsapp', 'maintenance_mode', 'maintenance_message'])
            ->pluck('value', 'key')
            ->toArray();

        $this->facebook = $settings['facebook'] ?? '';
        $this->twitter = $settings['twitter'] ?? '';
        $this->instagram = $settings['instagram'] ?? '';
        $this->linkedin = $settings['linkedin'] ?? '';
        $this->youtube = $settings['youtube'] ?? '';
        $this->contact_email = $settings['contact_email'] ?? '';
        $this->contact_phone = $settings['contact_phone'] ?? '';
        $this->contact_address = $settings['contact_address'] ?? '';
        $this->whatsapp = $settings['whatsapp'] ?? '';
        $this->maintenance_mode = filter_var($settings['maintenance_mode'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $this->maintenance_message = $settings['maintenance_message'] ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|url',
            'instagram' => 'nullable|url',
            'linkedin' => 'nullable|url',
            'youtube' => 'nullable|url',
            'contact_email' => 'nullable|email',
            'contact_phone' => 'nullable|string|max:20',
            'contact_address' => 'nullable|string|max:500',
            'whatsapp' => 'nullable|string|max:20',
            'maintenance_mode' => 'boolean',
            'maintenance_message' => 'nullable|string|max:500',
        ]);

        $settings = [
            'facebook' => ['name' => 'Facebook URL', 'key' => 'facebook', 'value' => $this->facebook, 'type' => 'url'],
            'twitter' => ['name' => 'Twitter URL', 'key' => 'twitter', 'value' => $this->twitter, 'type' => 'url'],
            'instagram' => ['name' => 'Instagram URL', 'key' => 'instagram', 'value' => $this->instagram, 'type' => 'url'],
            'linkedin' => ['name' => 'LinkedIn URL', 'key' => 'linkedin', 'value' => $this->linkedin, 'type' => 'url'],
            'youtube' => ['name' => 'YouTube URL', 'key' => 'youtube', 'value' => $this->youtube, 'type' => 'url'],
            'contact_email' => ['name' => 'Contact Email', 'key' => 'contact_email', 'value' => $this->contact_email, 'type' => 'email'],
            'contact_phone' => ['name' => 'Contact Phone', 'key' => 'contact_phone', 'value' => $this->contact_phone, 'type' => 'text'],
            'contact_address' => ['name' => 'Contact Address', 'key' => 'contact_address', 'value' => $this->contact_address, 'type' => 'textarea'],
            'whatsapp' => ['name' => 'WhatsApp Number', 'key' => 'whatsapp', 'value' => $this->whatsapp, 'type' => 'text'],
            'maintenance_mode' => ['name' => 'Maintenance Mode', 'key' => 'maintenance_mode', 'value' => $this->maintenance_mode ? '1' : '0', 'type' => 'toggle'],
            'maintenance_message' => ['name' => 'Maintenance Message', 'key' => 'maintenance_message', 'value' => $this->maintenance_message, 'type' => 'textarea'],
        ];

        foreach ($settings as $setting) {
            WebsiteSetting::updateOrCreate(['key' => $setting['key']], $setting);
            Cache::forget("website_setting_{$setting['key']}");
        }

        $this->success('Website settings updated successfully.');
    }
};

?>

<div class="pb-4">
    <x-header title="Website Settings" separator />

    <x-form wire:submit="save">
        {{-- Social Media Links --}}
        <x-card title="Social Media Links" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <x-input wire:model="facebook" label="Facebook URL" placeholder="https://facebook.com/..." icon="o-link"
                    type="url" />

                <x-input wire:model="twitter" label="Twitter URL" placeholder="https://twitter.com/..." icon="o-link"
                    type="url" />

                <x-input wire:model="instagram" label="Instagram URL" placeholder="https://instagram.com/..."
                    icon="o-link" type="url" />

                <x-input wire:model="linkedin" label="LinkedIn URL" placeholder="https://linkedin.com/..."
                    icon="o-link" type="url" />

                <x-input wire:model="youtube" label="YouTube URL" placeholder="https://youtube.com/..." icon="o-link"
                    type="url" />

                <x-input wire:model="whatsapp" label="WhatsApp Number" placeholder="+965 XXXX XXXX" icon="o-phone"
                    hint="Include country code" />
            </div>
        </x-card>

        {{-- Contact Details --}}
        <x-card title="Contact Details" class="mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <x-input wire:model="contact_email" label="Contact Email" placeholder="info@example.com"
                    icon="o-envelope" type="email" />

                <x-input wire:model="contact_phone" label="Contact Phone" placeholder="+965 XXXX XXXX" icon="o-phone" />
            </div>

            <div class="mt-4">
                <x-textarea wire:model="contact_address" label="Contact Address" placeholder="Enter full address"
                    rows="3" />
            </div>
        </x-card>

        {{-- Maintenance Mode --}}
        <x-card title="Maintenance Mode" class="mb-6">
            <div class="space-y-4">
                <div class="form-control">
                    <x-toggle wire:model.live="maintenance_mode" label="Enable Maintenance Mode" />
                    <p class="text-sm text-gray-500 mt-2">
                        When enabled, visitors will see a maintenance page. Admin users can still access the site.
                    </p>
                </div>

                @if ($maintenance_mode)
                    <x-textarea wire:model="maintenance_message" label="Maintenance Message"
                        placeholder="We're currently performing scheduled maintenance. We'll be back soon!"
                        rows="3" hint="This message will be displayed to visitors" />
                @endif
            </div>
        </x-card>

        {{-- Form Actions --}}
        <div class="flex flex-col sm:flex-row justify-end gap-2 sm:gap-3">
            <x-button icon="o-check" label="Save Settings" type="submit" class="btn-primary" spinner="save"
                responsive />
        </div>
    </x-form>
</div>
