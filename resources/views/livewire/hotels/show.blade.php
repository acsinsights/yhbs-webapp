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

    // Add main image first if it exists
    if ($hotel->image) {
        $slides[] = ['image' => asset($hotel->image)];
    }

    // Add library images
    if ($hotel->library && is_iterable($hotel->library)) {
        foreach ($hotel->library as $item) {
            $imageUrl = null;

            // Handle object/array structure with url, path, uuid
            if (is_array($item) || is_object($item)) {
                $item = (array) $item;
                // Use url if available (full URL), otherwise use path
                if (!empty($item['url'])) {
                    $imageUrl = $item['url'];
                } elseif (!empty($item['path'])) {
                    $imageUrl = asset('storage' . $item['path']);
                }
            } elseif (is_string($item) && $item !== '') {
                // Backward compatibility: handle string paths
                $imageUrl = asset($item);
            }

            if ($imageUrl) {
                $slides[] = ['image' => $imageUrl];
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
            <x-button icon="o-pencil" label="Edit" link="{{ route('admin.hotels.edit', $hotel) }}" class="btn-primary"
                responsive />
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.hotels.index') }}"
                class="btn-outline btn-primary" responsive />
            <x-button icon="o-trash" label="Delete" wire:click="delete"
                wire:confirm="Delete this hotel? This action cannot be undone." class="btn-error" responsive />
        </x-slot:actions>
    </x-header>

    {{-- Main Image Carousel --}}
    <x-card shadow>
        @if (!empty($slides))
            <x-carousel :slides="$slides" />
        @else
            <div class="aspect-video rounded-lg overflow-hidden bg-base-200 flex items-center justify-center">
                <div class="text-center text-base-content/50">
                    <x-icon name="o-photo" class="w-16 h-16 mx-auto mb-2" />
                    <p class="text-sm">No images available</p>
                </div>
            </div>
        @endif
    </x-card>

    {{-- Quick Stats --}}
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <x-card shadow class="border-l-4 border-l-primary">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-primary/10">
                    <x-icon name="o-link" class="w-6 h-6 text-primary" />
                </div>
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Slug</p>
                    <code class="text-sm break-all">{{ $hotel->slug }}</code>
                </div>
            </div>
        </x-card>

        <x-card shadow class="border-l-4 border-l-info">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-info/10">
                    <x-icon name="o-identification" class="w-6 h-6 text-info" />
                </div>
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">ID</p>
                    <p class="font-semibold text-sm font-mono">#{{ $hotel->id }}</p>
                </div>
            </div>
        </x-card>

        <x-card shadow class="border-l-4 border-l-success">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-success/10">
                    <x-icon name="o-photo" class="w-6 h-6 text-success" />
                </div>
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Total Images</p>
                    <p class="font-semibold text-sm">{{ count($slides) }}</p>
                </div>
            </div>
        </x-card>

        <x-card shadow class="border-l-4 border-l-warning">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-warning/10">
                    <x-icon name="o-calendar" class="w-6 h-6 text-warning" />
                </div>
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Created</p>
                    <p class="font-semibold text-sm">{{ $hotel->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Description --}}
    @if ($hotel->description)
        <x-card shadow>
            <x-slot:title class="flex items-center gap-2">
                <x-icon name="o-document-text" class="w-5 h-5" />
                <span>Description</span>
            </x-slot:title>
            <p class="text-base-content/80 whitespace-pre-line">{{ $hotel->description }}</p>
        </x-card>
    @endif

    {{-- Additional Information --}}
    <x-card shadow>
        <x-slot:title class="flex items-center gap-2">
            <x-icon name="o-information-circle" class="w-5 h-5" />
            <span>Additional Information</span>
        </x-slot:title>
        <div class="space-y-4">
            <x-collapse separator>
                <x-slot:heading class="flex items-center gap-2">
                    <x-icon name="o-finger-print" class="w-4 h-4" />
                    <span>Identifiers</span>
                </x-slot:heading>
                <x-slot:content>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Slug</p>
                            <code class="text-sm font-mono break-all">{{ $hotel->slug }}</code>
                        </div>
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Record ID</p>
                            <code class="text-sm font-mono">#{{ $hotel->id }}</code>
                        </div>
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Library Images</p>
                            <p class="text-sm font-semibold">{{ count($slides) }}</p>
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>

            <x-collapse separator>
                <x-slot:heading class="flex items-center gap-2">
                    <x-icon name="o-clock" class="w-4 h-4" />
                    <span>Timestamps</span>
                </x-slot:heading>
                <x-slot:content>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Created</p>
                            <p class="text-sm font-semibold">{{ $hotel->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-base-content/50">{{ $hotel->created_at->format('h:i A') }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Updated</p>
                            <p class="text-sm font-semibold">{{ $hotel->updated_at->format('M d, Y') }}</p>
                            <p class="text-xs text-base-content/50">{{ $hotel->updated_at->format('h:i A') }}</p>
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>
        </div>
    </x-card>
</div>
