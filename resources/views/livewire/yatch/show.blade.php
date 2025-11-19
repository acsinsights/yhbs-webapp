<?php

use App\Models\Yatch;
use Livewire\Volt\Component;
use Livewire\Attributes\Title;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public Yatch $yatch;

    public function mount($yatch): void
    {
        $this->yatch = $yatch instanceof Yatch ? $yatch : Yatch::findOrFail($yatch);
    }

    public function delete(): void
    {
        // Delete image if exists
        if ($this->yatch->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('/storage/', '', $this->yatch->image));
        }

        $this->yatch->delete();
        $this->success('Yacht deleted successfully!', redirectTo: route('admin.yatch.index'));
    }
}; ?>

@php
    $slides = [];
    if ($yatch->library && is_iterable($yatch->library)) {
        foreach ($yatch->library as $item) {
            if (is_string($item) && $item !== '') {
                $slides[] = ['image' => asset($item)];
            }
        }
    }
@endphp

<div class="space-y-6">
    <x-header title="{{ $yatch->name }}" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/60">Yacht overview and details</p>
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-pencil" label="Edit" link="{{ route('admin.yatch.edit', $yatch) }}" class="btn-primary" />
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.yatch.index') }}" class="btn-ghost" />
            <x-button icon="o-trash" label="Delete" wire:click="delete"
                wire:confirm="Delete this yacht? This action cannot be undone." class="btn-error" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-4">
                @if ($yatch->sku)
                    <div>
                        <p class="text-sm text-base-content/60 mb-1">SKU</p>
                        <code class="px-3 py-1 rounded bg-base-200 text-sm font-mono">{{ $yatch->sku }}</code>
                    </div>
                @endif
                <div>
                    <p class="text-sm text-base-content/60 mb-1">Slug</p>
                    <code class="px-3 py-1 rounded bg-base-200 text-sm">{{ $yatch->slug }}</code>
                </div>
                @if ($yatch->price)
                    <div>
                        <p class="text-sm text-base-content/60 mb-1">Price</p>
                        <p class="text-2xl font-bold text-primary">${{ number_format($yatch->price, 2) }}</p>
                        @if ($yatch->discount_price)
                            <p class="text-lg font-semibold text-success">
                                ${{ number_format($yatch->discount_price, 2) }}</p>
                            @php
                                $discountPercent = round(
                                    (($yatch->price - $yatch->discount_price) / $yatch->price) * 100,
                                );
                            @endphp
                            <x-badge :value="$discountPercent . '% OFF'" class="badge-success badge-sm mt-1" />
                        @endif
                    </div>
                @endif
                <div class="space-y-1">
                    <p class="text-sm text-base-content/60">Created</p>
                    <p class="font-semibold">{{ $yatch->created_at->format('M d, Y') }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-sm text-base-content/60">Updated</p>
                    <p class="font-semibold">{{ $yatch->updated_at->format('M d, Y') }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-sm text-base-content/60">ID</p>
                    <p class="font-mono">{{ $yatch->id }}</p>
                </div>
            </div>
            <div class="aspect-video rounded-xl overflow-hidden bg-base-200 flex items-center justify-center">
                @if ($yatch->image)
                    <img src="{{ asset($yatch->image) }}" alt="{{ $yatch->name }}"
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

    @if ($yatch->description)
        <x-card>
            <x-slot:title>Description</x-slot:title>
            <p class="text-base-content/80 whitespace-pre-line">{{ $yatch->description }}</p>
        </x-card>
    @endif

    <x-card>
        <x-slot:title>More Details</x-slot:title>
        <div class="space-y-4">
            <x-collapse separator>
                <x-slot:heading>Specifications</x-slot:heading>
                <x-slot:content>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                        @if ($yatch->length)
                            <div>
                                <p class="text-xs text-base-content/60 uppercase mb-1">Length</p>
                                <span class="font-semibold">{{ $yatch->length }}m</span>
                            </div>
                        @endif
                        @if ($yatch->max_guests)
                            <div>
                                <p class="text-xs text-base-content/60 uppercase mb-1">Max Guests</p>
                                <span class="font-semibold">{{ $yatch->max_guests }}</span>
                            </div>
                        @endif
                        @if ($yatch->max_crew)
                            <div>
                                <p class="text-xs text-base-content/60 uppercase mb-1">Max Crew</p>
                                <span class="font-semibold">{{ $yatch->max_crew }}</span>
                            </div>
                        @endif
                        @if ($yatch->max_capacity)
                            <div>
                                <p class="text-xs text-base-content/60 uppercase mb-1">Max Capacity</p>
                                <span class="font-semibold">{{ $yatch->max_capacity }}</span>
                            </div>
                        @endif
                        @if ($yatch->max_fuel_capacity)
                            <div>
                                <p class="text-xs text-base-content/60 uppercase mb-1">Fuel Capacity</p>
                                <span class="font-semibold">{{ $yatch->max_fuel_capacity }}L</span>
                            </div>
                        @endif
                    </div>
                </x-slot:content>
            </x-collapse>

            <x-collapse separator>
                <x-slot:heading>Identifiers</x-slot:heading>
                <x-slot:content>
                    <div class="grid gap-4 md:grid-cols-3">
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Slug</p>
                            <span class="font-mono text-sm break-all">{{ $yatch->slug }}</span>
                        </div>
                        @if ($yatch->sku)
                            <div>
                                <p class="text-xs text-base-content/60 uppercase mb-1">SKU</p>
                                <span class="font-mono text-sm">{{ $yatch->sku }}</span>
                            </div>
                        @endif
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Record ID</p>
                            <span class="font-mono text-sm">{{ $yatch->id }}</span>
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>

            <x-collapse separator>
                <x-slot:heading>Pricing</x-slot:heading>
                <x-slot:content>
                    <div class="grid gap-4 md:grid-cols-2">
                        @if ($yatch->price)
                            <div>
                                <p class="text-xs text-base-content/60 uppercase mb-1">Regular Price</p>
                                <span
                                    class="text-xl font-bold text-primary">${{ number_format($yatch->price, 2) }}</span>
                            </div>
                        @endif
                        @if ($yatch->discount_price)
                            <div>
                                <p class="text-xs text-base-content/60 uppercase mb-1">Discount Price</p>
                                <span
                                    class="text-xl font-bold text-success">${{ number_format($yatch->discount_price, 2) }}</span>
                                @if ($yatch->price)
                                    @php
                                        $discountPercent = round(
                                            (($yatch->price - $yatch->discount_price) / $yatch->price) * 100,
                                        );
                                    @endphp
                                    <x-badge :value="$discountPercent . '% OFF'" class="badge-success badge-sm mt-1" />
                                @endif
                            </div>
                        @endif
                    </div>
                </x-slot:content>
            </x-collapse>

            <x-collapse separator>
                <x-slot:heading>Timestamps</x-slot:heading>
                <x-slot:content>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Created</p>
                            <span>{{ $yatch->created_at->format('M d, Y h:i A') }}</span>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Updated</p>
                            <span>{{ $yatch->updated_at->format('M d, Y h:i A') }}</span>
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>
        </div>
    </x-card>
</div>
