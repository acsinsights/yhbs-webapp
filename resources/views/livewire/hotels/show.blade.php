<?php

use Mary\Traits\Toast;
use App\Models\Hotel;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use Toast;

    public Hotel $hotel;

    public function mount($hotel): void
    {
        $this->hotel = $hotel instanceof Hotel ? $hotel : Hotel::findOrFail($hotel);
    }

    public function delete(): void
    {
        // Delete image if exists
        if ($this->hotel->image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $this->hotel->image));
        }

        $this->hotel->delete();
        $this->success('Hotel deleted successfully!', redirectTo: route('admin.hotels.index'));
    }
}; ?>

@php
    $slides = [];
    if ($hotel->library && is_iterable($hotel->library)) {
        foreach ($hotel->library as $item) {
            if (is_string($item) && $item !== '') {
                $slides[] = ['image' => asset($item)];
            }
        }
    }
@endphp

<div class="space-y-6">
    <x-header title="{{ $hotel->name }}" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/60">Hotel overview and media</p>
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-pencil" label="Edit" link="{{ route('admin.hotels.edit', $hotel) }}" class="btn-primary" />
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.hotels.index') }}" class="btn-ghost" />
            <x-button icon="o-trash" label="Delete" wire:click="delete"
                wire:confirm="Delete this hotel? This action cannot be undone." class="btn-error" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-base-content/60 mb-1">Slug</p>
                    <code class="px-3 py-1 rounded bg-base-200 text-sm">{{ $hotel->slug }}</code>
                </div>
                <div class="space-y-1">
                    <p class="text-sm text-base-content/60">Created</p>
                    <p class="font-semibold">{{ $hotel->created_at->format('M d, Y') }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-sm text-base-content/60">Updated</p>
                    <p class="font-semibold">{{ $hotel->updated_at->format('M d, Y') }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-sm text-base-content/60">ID</p>
                    <p class="font-mono">{{ $hotel->id }}</p>
                </div>
            </div>
            <div class="aspect-video rounded-xl overflow-hidden bg-base-200 flex items-center justify-center">
                @if ($hotel->image)
                    <img src="{{ asset($hotel->image) }}" alt="{{ $hotel->name }}"
                        class="object-cover w-full h-full" />
                @else
                    <div class="text-center text-base-content/50">
                        <x-icon name="o-photo" class="w-16 h-16 mx-auto mb-2" />
                        <p>No cover image</p>
                    </div>
                @endif
            </div>
        </div>
    </x-card>

    @if (!empty($slides))
        <x-card>
            <x-slot:title>Gallery</x-slot:title>
            <x-carousel :slides="$slides" />
        </x-card>
    @endif

    @if ($hotel->description)
        <x-card>
            <x-slot:title>Description</x-slot:title>
            <p class="text-base-content/80 whitespace-pre-line">{{ $hotel->description }}</p>
        </x-card>
    @endif

    <x-card>
        <x-slot:title>More Details</x-slot:title>
        <div class="space-y-4">
            <x-collapse separator>
                <x-slot:heading>Identifiers</x-slot:heading>
                <x-slot:content>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Slug</p>
                            <span class="font-mono text-sm break-all">{{ $hotel->slug }}</span>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Record ID</p>
                            <span class="font-mono text-sm">{{ $hotel->id }}</span>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Library Images</p>
                            <span>{{ count($slides) }}</span>
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>

            <x-collapse separator>
                <x-slot:heading>Timestamps</x-slot:heading>
                <x-slot:content>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Created</p>
                            <span>{{ $hotel->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Updated</p>
                            <span>{{ $hotel->updated_at->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>
        </div>
    </x-card>
</div>
