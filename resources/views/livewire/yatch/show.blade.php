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

    <x-card shadow>
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-6">
                {{-- Pricing Section --}}
                @if ($yatch->price)
                    <div class="p-4 rounded-lg bg-base-200">
                        <div class="flex items-center gap-2 mb-3">
                            <x-icon name="o-currency-dollar" class="w-5 h-5 text-primary" />
                            <p class="text-sm font-semibold text-base-content/70 uppercase tracking-wide">Pricing</p>
                        </div>
                        <div class="space-y-2">
                            @if ($yatch->discount_price)
                                <div class="flex items-baseline gap-3">
                                    <p class="text-3xl font-bold text-primary">
                                        {{ currency_format($yatch->discount_price) }}</p>
                                    <p class="text-xl line-through text-base-content/40">
                                        {{ currency_format($yatch->price) }}</p>
                                    @php
                                        $discountPercent = round(
                                            (($yatch->price - $yatch->discount_price) / $yatch->price) * 100,
                                        );
                                    @endphp
                                    <x-badge :value="$discountPercent . '% OFF'" class="badge-success badge-sm" />
                                </div>
                            @else
                                <p class="text-3xl font-bold text-primary">{{ currency_format($yatch->price) }}</p>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Information Grid --}}
                <div class="grid grid-cols-2 gap-4">
                    @if ($yatch->sku)
                        <div class="p-3 rounded-lg bg-base-200/50 border border-base-300">
                            <div class="flex items-center gap-2 mb-1">
                                <x-icon name="o-hashtag" class="w-4 h-4 text-base-content/50" />
                                <p class="text-xs font-medium text-base-content/60 uppercase tracking-wide">SKU</p>
                            </div>
                            <p class="text-sm font-semibold font-mono">{{ $yatch->sku }}</p>
                        </div>
                    @endif

                    <div class="p-3 rounded-lg bg-base-200/50 border border-base-300">
                        <div class="flex items-center gap-2 mb-1">
                            <x-icon name="o-identification" class="w-4 h-4 text-base-content/50" />
                            <p class="text-xs font-medium text-base-content/60 uppercase tracking-wide">ID</p>
                        </div>
                        <p class="text-sm font-semibold font-mono">#{{ $yatch->id }}</p>
                    </div>
                </div>

                {{-- Slug --}}
                <div class="p-3 rounded-lg bg-base-200/50 border border-base-300">
                    <div class="flex items-center gap-2 mb-1">
                        <x-icon name="o-link" class="w-4 h-4 text-base-content/50" />
                        <p class="text-xs font-medium text-base-content/60 uppercase tracking-wide">Slug</p>
                    </div>
                    <code class="text-sm break-all">{{ $yatch->slug }}</code>
                </div>

                {{-- Timestamps --}}
                <div class="grid grid-cols-2 gap-4">
                    <div class="p-3 rounded-lg bg-base-200/50 border border-base-300">
                        <div class="flex items-center gap-2 mb-1">
                            <x-icon name="o-calendar" class="w-4 h-4 text-base-content/50" />
                            <p class="text-xs font-medium text-base-content/60 uppercase tracking-wide">Created</p>
                        </div>
                        <p class="text-sm font-semibold">{{ $yatch->created_at->format('M d, Y') }}</p>
                        <p class="text-xs text-base-content/50">{{ $yatch->created_at->format('h:i A') }}</p>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50 border border-base-300">
                        <div class="flex items-center gap-2 mb-1">
                            <x-icon name="o-clock" class="w-4 h-4 text-base-content/50" />
                            <p class="text-xs font-medium text-base-content/60 uppercase tracking-wide">Updated</p>
                        </div>
                        <p class="text-sm font-semibold">{{ $yatch->updated_at->format('M d, Y') }}</p>
                        <p class="text-xs text-base-content/50">{{ $yatch->updated_at->format('h:i A') }}</p>
                    </div>
                </div>
            </div>

            {{-- Image Section --}}
            <div
                class="aspect-video rounded-xl overflow-hidden bg-base-200 flex items-center justify-center shadow-lg border border-base-300">
                @if ($yatch->image)
                    <img src="{{ asset($yatch->image) }}" alt="{{ $yatch->name }}"
                        class="object-cover w-full h-full" />
                @else
                    <div class="text-center text-base-content/50">
                        <x-icon name="o-photo" class="w-16 h-16 mx-auto mb-2" />
                        <p class="text-sm">No cover image</p>
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
                                    class="text-xl font-bold text-primary">{{ currency_format($yatch->price) }}</span>
                            </div>
                        @endif
                        @if ($yatch->discount_price)
                            <div>
                                <p class="text-xs text-base-content/60 uppercase mb-1">Discount Price</p>
                                <div class="flex items-center gap-2">
                                    <span
                                        class="text-xl font-bold text-success">{{ currency_format($yatch->discount_price) }}</span>
                                    @if ($yatch->price)
                                        @php
                                            $discountPercent = round(
                                                (($yatch->price - $yatch->discount_price) / $yatch->price) * 100,
                                            );
                                        @endphp
                                        <x-badge :value="$discountPercent . '% OFF'" class="badge-success badge-sm" />
                                    @endif
                                </div>
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
