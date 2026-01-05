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
