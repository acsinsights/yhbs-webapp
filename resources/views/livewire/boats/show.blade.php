<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use App\Models\Boat;
use Illuminate\View\View;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use Toast;

    public Boat $boat;

    public function mount($boat): void
    {
        $this->boat = $boat instanceof Boat ? $boat->load('bookings') : Boat::with('bookings')->findOrFail($boat);
    }

    public function delete(): void
    {
        if ($this->boat->bookings()->count() > 0) {
            $this->error('Cannot delete boat with existing bookings.');
            return;
        }

        if ($this->boat->image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $this->boat->image));
        }

        $this->boat->delete();
        $this->success('Boat deleted successfully!', redirectTo: route('admin.boats.index'));
    }

    public function rendering(View $view): void
    {
        $view->bookings = $this->boat->bookings()->with('user')->orderByDesc('created_at')->get();

        $view->bookingHeaders = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'customer', 'label' => 'Customer', 'class' => 'whitespace-nowrap'], ['key' => 'schedule', 'label' => 'Schedule', 'class' => 'whitespace-nowrap'], ['key' => 'passengers', 'label' => 'Passengers', 'class' => 'whitespace-nowrap'], ['key' => 'amount', 'label' => 'Amount', 'class' => 'whitespace-nowrap'], ['key' => 'status', 'label' => 'Status', 'class' => 'whitespace-nowrap'], ['key' => 'payment', 'label' => 'Payment', 'class' => 'whitespace-nowrap']];
    }
}; ?>

@php
    $slides = [];

    // Add main image first if it exists
    if ($boat->image) {
        $slides[] = ['image' => asset($boat->image)];
    }

    // Add library images
    if ($boat->library && is_iterable($boat->library)) {
        foreach ($boat->library as $item) {
            $imageUrl = null;

            // Handle object/array structure with url, path, uuid
            if (is_array($item) || is_object($item)) {
                $item = (array) $item;
                // Use url if available (full URL), otherwise construct from path
                if (!empty($item['url'])) {
                    $imageUrl = $item['url'];
                } elseif (!empty($item['path'])) {
                    // Ensure path starts with /
                    $path = $item['path'];
                    if (!str_starts_with($path, '/')) {
                        $path = '/' . $path;
                    }
                    $imageUrl = asset('storage' . $path);
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
    <x-header title="{{ $boat->name }}" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/60">Boat overview, media, and booking history</p>
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-pencil" label="Edit" link="{{ route('admin.boats.edit', $boat) }}" class="btn-primary"
                responsive />
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.boats.index') }}"
                class="btn-outline btn-primary" responsive />
            <x-button icon="o-trash" label="Delete" wire:click="delete"
                wire:confirm="Delete this boat? This action cannot be undone." class="btn-error" responsive />
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
                    <x-icon name="o-tag" class="w-6 h-6 text-primary" />
                </div>
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Service Type</p>
                    <p class="font-semibold text-sm">{{ $boat->service_type_label }}</p>
                </div>
            </div>
        </x-card>

        <x-card shadow class="border-l-4 border-l-info">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-info/10">
                    <x-icon name="o-users" class="w-6 h-6 text-info" />
                </div>
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Capacity</p>
                    <p class="font-semibold text-sm">{{ $boat->min_passengers }}-{{ $boat->max_passengers }} passengers
                    </p>
                </div>
            </div>
        </x-card>

        <x-card shadow class="border-l-4 border-l-warning">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-warning/10">
                    <x-icon name="o-map-pin" class="w-6 h-6 text-warning" />
                </div>
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Location</p>
                    <p class="font-semibold text-sm">{{ $boat->location ?? 'N/A' }}</p>
                </div>
            </div>
        </x-card>

        <x-card shadow class="border-l-4 {{ $boat->is_active ? 'border-l-success' : 'border-l-error' }}">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg {{ $boat->is_active ? 'bg-success/10' : 'bg-error/10' }}">
                    <x-icon name="o-check-circle"
                        class="w-6 h-6 {{ $boat->is_active ? 'text-success' : 'text-error' }}" />
                </div>
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Status</p>
                    <x-badge :value="$boat->is_active ? 'Active' : 'Inactive'"
                        class="badge-soft {{ $boat->is_active ? 'badge-success' : 'badge-error' }} badge-sm" />
                </div>
            </div>
        </x-card>
    </div>

    {{-- Pricing Card --}}
    <x-card shadow>
        <x-slot:title class="flex items-center gap-2">
            <x-icon name="o-currency-dollar" class="w-5 h-5" />
            <span>Pricing</span>
        </x-slot:title>
        <div class="grid gap-6">
            @if ($boat->service_type === 'yacht' || $boat->service_type === 'taxi')
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @if ($boat->price_1hour)
                        <div>
                            <p class="text-xs text-base-content/60 mb-1">1 Hour</p>
                            <p class="text-lg font-semibold">{{ currency_format($boat->price_1hour) }}</p>
                        </div>
                    @endif
                    @if ($boat->price_2hours)
                        <div>
                            <p class="text-xs text-base-content/60 mb-1">2 Hours</p>
                            <p class="text-lg font-semibold">{{ currency_format($boat->price_2hours) }}</p>
                        </div>
                    @endif
                    @if ($boat->price_3hours)
                        <div>
                            <p class="text-xs text-base-content/60 mb-1">3 Hours</p>
                            <p class="text-lg font-semibold">{{ currency_format($boat->price_3hours) }}</p>
                        </div>
                    @endif
                    @if ($boat->additional_hour_price)
                        <div>
                            <p class="text-xs text-base-content/60 mb-1">Additional Hour</p>
                            <p class="text-lg font-semibold">{{ currency_format($boat->additional_hour_price) }}</p>
                        </div>
                    @endif
                </div>
            @elseif($boat->service_type === 'ferry')
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @if ($boat->price_per_person_adult)
                        <div>
                            <p class="text-xs text-base-content/60 mb-1">Adult (Per Person)</p>
                            <p class="text-lg font-semibold">{{ currency_format($boat->price_per_person_adult) }}</p>
                        </div>
                    @endif
                    @if ($boat->price_per_person_child)
                        <div>
                            <p class="text-xs text-base-content/60 mb-1">Child (Per Person)</p>
                            <p class="text-lg font-semibold">{{ currency_format($boat->price_per_person_child) }}</p>
                        </div>
                    @endif
                    @if ($boat->private_trip_price)
                        <div>
                            <p class="text-xs text-base-content/60 mb-1">Private Trip (One Way)</p>
                            <p class="text-lg font-semibold">{{ currency_format($boat->private_trip_price) }}</p>
                        </div>
                    @endif
                    @if ($boat->private_trip_return_price)
                        <div>
                            <p class="text-xs text-base-content/60 mb-1">Private Trip (Return)</p>
                            <p class="text-lg font-semibold">{{ currency_format($boat->private_trip_return_price) }}
                            </p>
                        </div>
                    @endif
                </div>
            @elseif($boat->service_type === 'limousine')
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                    @if ($boat->price_15min)
                        <div>
                            <p class="text-xs text-base-content/60 mb-1">15 Minutes</p>
                            <p class="text-lg font-semibold">{{ currency_format($boat->price_15min) }}</p>
                        </div>
                    @endif
                    @if ($boat->price_30min)
                        <div>
                            <p class="text-xs text-base-content/60 mb-1">30 Minutes</p>
                            <p class="text-lg font-semibold">{{ currency_format($boat->price_30min) }}</p>
                        </div>
                    @endif
                    @if ($boat->price_full_boat)
                        <div>
                            <p class="text-xs text-base-content/60 mb-1">Full Boat (1 Hour)</p>
                            <p class="text-lg font-semibold">{{ currency_format($boat->price_full_boat) }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </x-card>

    @if ($boat->description)
        <x-card>
            <x-slot:title>Description</x-slot:title>
            <p class="text-base-content/80 whitespace-pre-line">{{ $boat->description }}</p>
        </x-card>
    @endif

    @if ($boat->features)
        <x-card>
            <x-slot:title>Features</x-slot:title>
            <p class="text-base-content/80 whitespace-pre-line">{{ $boat->features }}</p>
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
                            <code class="text-sm font-mono break-all">{{ $boat->slug }}</code>
                        </div>
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Featured</p>
                            <code class="text-sm font-mono">{{ $boat->is_featured ? 'Yes' : 'No' }}</code>
                        </div>
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Record ID</p>
                            <code class="text-sm font-mono">#{{ $boat->id }}</code>
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>

            <x-collapse separator>
                <x-slot:heading class="flex items-center gap-2">
                    <x-icon name="o-magnifying-glass" class="w-4 h-4" />
                    <span>SEO Information</span>
                </x-slot:heading>
                <x-slot:content>
                    <div class="space-y-3">
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Meta Description</p>
                            <p class="text-sm">{{ $boat->meta_description ?? '—' }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Meta Keywords</p>
                            <p class="text-sm">{{ $boat->meta_keywords ?? '—' }}</p>
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
                            <p class="text-sm font-semibold">{{ $boat->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-base-content/50">{{ $boat->created_at->format('h:i A') }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Updated</p>
                            <p class="text-sm font-semibold">{{ $boat->updated_at->format('M d, Y') }}</p>
                            <p class="text-xs text-base-content/50">{{ $boat->updated_at->format('h:i A') }}</p>
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>
        </div>
    </x-card>

    {{-- Booking History --}}
    <x-card shadow>
        <x-slot:title class="flex items-center gap-2">
            <x-icon name="o-calendar-days" class="w-5 h-5" />
            <span>Booking History</span>
        </x-slot:title>
        <x-table :headers="$bookingHeaders" :rows="$bookings">
            @scope('cell_customer', $booking)
                <div>
                    <p class="font-semibold">{{ $booking->user->name ?? 'N/A' }}</p>
                    <p class="text-xs text-base-content/50">{{ $booking->user->email ?? '—' }}</p>
                </div>
            @endscope

            @scope('cell_schedule', $booking)
                <div class="text-sm">
                    <p>{{ $booking->check_in ? Carbon::parse($booking->check_in)->format('M d, Y h:i A') : '—' }}</p>
                    @if ($booking->duration)
                        <p class="text-xs text-base-content/50">{{ $booking->duration }}</p>
                    @endif
                </div>
            @endscope

            @scope('cell_passengers', $booking)
                <x-badge :value="($booking->adults ?? 0) . ' passengers'" class="badge-soft badge-info badge-sm" />
            @endscope

            @scope('cell_amount', $booking)
                <div class="font-semibold">{{ currency_format($booking->price ?? 0) }}</div>
            @endscope

            @scope('cell_status', $booking)
                <x-badge :value="$booking->status->label()" class="{{ $booking->status->badgeColor() }}" />
            @endscope

            @scope('cell_payment', $booking)
                <div class="flex items-center gap-1">
                    <x-badge :value="$booking->payment_status->label()" class="{{ $booking->payment_status->badgeColor() }} badge-sm" />
                    <x-badge :value="$booking->payment_method->label()" class="{{ $booking->payment_method->badgeColor() }} badge-sm" />
                </div>
            @endscope

            @scope('actions', $booking)
                <x-button icon="o-eye" link="{{ route('admin.bookings.boat.show', $booking->id) }}"
                    class="btn-ghost btn-sm" tooltip="View Booking" />
            @endscope

            <x-slot:empty>
                <x-empty icon="o-calendar" message="No bookings yet for this boat" />
            </x-slot:empty>
        </x-table>
    </x-card>
</div>
