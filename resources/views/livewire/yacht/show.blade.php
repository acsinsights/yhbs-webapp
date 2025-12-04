<?php

use Mary\Traits\Toast;
use App\Models\Yacht;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use Toast;

    public Yacht $yacht;

    public function mount($yacht): void
    {
        $this->yacht = $yacht instanceof Yacht ? $yacht : Yacht::findOrFail($yacht);
    }

    public function delete(): void
    {
        // Delete image if exists
        if ($this->yacht->image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $this->yacht->image));
        }

        $this->yacht->delete();
        $this->success('Yacht deleted successfully!', redirectTo: route('admin.yacht.index'));
    }
}; ?>

@php
    $slides = [];

    // Add main image first if it exists
    if ($yacht->image) {
        $slides[] = ['image' => asset($yacht->image)];
    }

    // Add library images
    if ($yacht->library && is_iterable($yacht->library)) {
        foreach ($yacht->library as $item) {
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
    <x-header title="{{ $yacht->name }}" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/60">Yacht overview and details</p>
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-pencil" label="Edit" link="{{ route('admin.yacht.edit', $yacht) }}" class="btn-primary"
                responsive />
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.yacht.index') }}"
                class="btn-outline btn-primary" responsive />
            <x-button icon="o-trash" label="Delete" wire:click="delete"
                wire:confirm="Delete this yacht? This action cannot be undone." class="btn-error" responsive />
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
        @if ($yacht->sku)
            <x-card shadow class="border-l-4 border-l-info">
                <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg bg-info/10">
                        <x-icon name="o-hashtag" class="w-6 h-6 text-info" />
                    </div>
                    <div>
                        <p class="text-xs text-base-content/60 uppercase tracking-wide">SKU</p>
                        <p class="font-semibold text-sm font-mono">{{ $yacht->sku }}</p>
                    </div>
                </div>
            </x-card>
        @endif

        <x-card shadow class="border-l-4 border-l-primary">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-primary/10">
                    <x-icon name="o-identification" class="w-6 h-6 text-primary" />
                </div>
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">ID</p>
                    <p class="font-semibold text-sm font-mono">#{{ $yacht->id }}</p>
                </div>
            </div>
        </x-card>

        <x-card shadow class="border-l-4 border-l-success">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-success/10">
                    <x-icon name="o-link" class="w-6 h-6 text-success" />
                </div>
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Slug</p>
                    <code class="text-sm break-all">{{ $yacht->slug }}</code>
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
                    <p class="font-semibold text-sm">{{ $yacht->created_at->format('M d, Y') }}</p>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Pricing Card --}}
    @if ($yacht->price)
        <x-card shadow>
            <x-slot:title class="flex items-center gap-2">
                <x-icon name="o-currency-dollar" class="w-5 h-5" />
                <span>Pricing</span>
            </x-slot:title>
            <div class="flex items-baseline gap-4">
                @if ($yacht->discount_price)
                    <div>
                        <p class="text-xs text-base-content/60 mb-1">Regular Price</p>
                        <p class="text-2xl font-bold text-base-content/40 line-through">
                            {{ currency_format($yacht->price) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-base-content/60 mb-1">Discount Price</p>
                        <div class="flex items-baseline gap-2">
                            <p class="text-3xl font-bold text-primary">{{ currency_format($yacht->discount_price) }}</p>
                            @php
                                $discountPercent = round(
                                    (($yacht->price - $yacht->discount_price) / $yacht->price) * 100,
                                );
                            @endphp
                            <x-badge :value="$discountPercent . '% OFF'" class="badge-success badge-sm" />
                        </div>
                    </div>
                @else
                    <div>
                        <p class="text-xs text-base-content/60 mb-1">Price</p>
                        <p class="text-3xl font-bold text-primary">{{ currency_format($yacht->price) }}</p>
                    </div>
                @endif
            </div>
        </x-card>
    @endif

    {{-- Description --}}
    @if ($yacht->description)
        <x-card shadow>
            <x-slot:title class="flex items-center gap-2">
                <x-icon name="o-document-text" class="w-5 h-5" />
                <span>Description</span>
            </x-slot:title>
            <p class="text-base-content/80 whitespace-pre-line">{{ $yacht->description }}</p>
        </x-card>
    @endif

    {{-- Specifications --}}
    <x-card shadow>
        <x-slot:title class="flex items-center gap-2">
            <x-icon name="o-cog-6-tooth" class="w-5 h-5" />
            <span>Specifications</span>
        </x-slot:title>
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @if ($yacht->length)
                <div class="p-3 rounded-lg bg-base-200/50">
                    <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Length</p>
                    <p class="text-sm font-semibold">{{ $yacht->length }}m</p>
                </div>
            @endif
            @if ($yacht->max_guests)
                <div class="p-3 rounded-lg bg-base-200/50">
                    <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Max Guests</p>
                    <p class="text-sm font-semibold">{{ $yacht->max_guests }}</p>
                </div>
            @endif
            @if ($yacht->max_crew)
                <div class="p-3 rounded-lg bg-base-200/50">
                    <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Max Crew</p>
                    <p class="text-sm font-semibold">{{ $yacht->max_crew }}</p>
                </div>
            @endif
            @if ($yacht->max_capacity)
                <div class="p-3 rounded-lg bg-base-200/50">
                    <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Max Capacity</p>
                    <p class="text-sm font-semibold">{{ $yacht->max_capacity }}</p>
                </div>
            @endif
            @if ($yacht->max_fuel_capacity)
                <div class="p-3 rounded-lg bg-base-200/50">
                    <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Fuel Capacity</p>
                    <p class="text-sm font-semibold">{{ $yacht->max_fuel_capacity }}L</p>
                </div>
            @endif
        </div>
    </x-card>

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
                            <code class="text-sm font-mono break-all">{{ $yacht->slug }}</code>
                        </div>
                        @if ($yacht->sku)
                            <div class="p-3 rounded-lg bg-base-200/50">
                                <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">SKU</p>
                                <code class="text-sm font-mono">{{ $yacht->sku }}</code>
                            </div>
                        @endif
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Record ID</p>
                            <code class="text-sm font-mono">#{{ $yacht->id }}</code>
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
                            <p class="text-sm font-semibold">{{ $yacht->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-base-content/50">{{ $yacht->created_at->format('h:i A') }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Updated</p>
                            <p class="text-sm font-semibold">{{ $yacht->updated_at->format('M d, Y') }}</p>
                            <p class="text-xs text-base-content/50">{{ $yacht->updated_at->format('h:i A') }}</p>
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>
        </div>
    </x-card>
</div>
