<?php

namespace App\Livewire\Admin\WebsiteSettings;

use App\Models\WebsiteSetting;
use Livewire\Component;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Cache;

class Index extends Component
{
    use Toast;

    // Social Media Links
    public $facebook = '';
    public $twitter = '';
    public $instagram = '';
    public $linkedin = '';
    public $youtube = '';

    // Contact Details
    public $contact_email = '';
    public $contact_phone = '';
    public $contact_address = '';
    public $whatsapp = '';

    // Maintenance Mode
    public $maintenance_mode = false;
    public $maintenance_message = '';

    public function mount()
    {
        $this->loadSettings();
    }

    private function loadSettings()
    {
        $settings = WebsiteSetting::whereIn('key', [
            'facebook',
            'twitter',
            'instagram',
            'linkedin',
            'youtube',
            'contact_email',
            'contact_phone',
            'contact_address',
            'whatsapp',
            'maintenance_mode',
            'maintenance_message'
        ])->pluck('value', 'key')->toArray();

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

    public function save()
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
            WebsiteSetting::updateOrCreate(
                ['key' => $setting['key']],
                $setting
            );
            Cache::forget("website_setting_{$setting['key']}");
        }

        $this->success('Website settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.admin.website-settings.index');
    }
}
