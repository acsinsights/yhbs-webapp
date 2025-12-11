<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use App\Models\Room;
use Illuminate\View\View;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use Toast;

    public Room $room;

    public function mount($room): void
    {
        $this->room = $room instanceof Room ? $room->load(['house', 'categories', 'amenities']) : Room::with(['house', 'categories', 'amenities'])->findOrFail($room);
    }

    public function delete(): void
    {
        if ($this->room->image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $this->room->image));
        }

        $this->room->delete();
        $this->success('Room deleted successfully!', redirectTo: route('admin.rooms.index'));
    }

    public function rendering(View $view): void
    {
        $view->bookings = $this->room->bookings()->with('user')->orderByDesc('check_in')->get();

        $view->bookingHeaders = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'customer', 'label' => 'Customer', 'class' => 'whitespace-nowrap'], ['key' => 'schedule', 'label' => 'Stay', 'class' => 'whitespace-nowrap'], ['key' => 'guests', 'label' => 'Guests', 'class' => 'whitespace-nowrap'], ['key' => 'amount', 'label' => 'Amount', 'class' => 'whitespace-nowrap'], ['key' => 'status', 'label' => 'Status', 'class' => 'whitespace-nowrap'], ['key' => 'payment', 'label' => 'Payment', 'class' => 'whitespace-nowrap']];
    }
}; ?>

@php
    $slides = [];

    // Add main image first if it exists
    if ($room->image) {
        $slides[] = ['image' => asset($room->image)];
    }

    // Add library images
    if ($room->library && is_iterable($room->library)) {
        foreach ($room->library as $item) {
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
    <x-header title="{{ $room->name }}" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/60">Room overview, media, and booking history</p>
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-pencil" label="Edit" link="{{ route('admin.rooms.edit', $room) }}" class="btn-primary"
                responsive />
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.rooms.index') }}"
                class="btn-outline btn-primary" responsive />
            <x-button icon="o-trash" label="Delete" wire:click="delete"
                wire:confirm="Delete this room? This action cannot be undone." class="btn-error" responsive />
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
                    <x-icon name="o-building-office" class="w-6 h-6 text-primary" />
                </div>
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">House</p>
                    <p class="font-semibold text-sm">{{ $room->house->name ?? 'N/A' }}</p>
                </div>
            </div>
        </x-card>

        <x-card shadow class="border-l-4 border-l-info">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-info/10">
                    <x-icon name="o-hashtag" class="w-6 h-6 text-info" />
                </div>
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Room Number</p>
                    <p class="font-semibold text-sm font-mono">{{ $room->room_number }}</p>
                </div>
            </div>
        </x-card>

        <x-card shadow class="border-l-4 border-l-success">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-success/10">
                    <x-icon name="o-users" class="w-6 h-6 text-success" />
                </div>
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Capacity</p>
                    <p class="font-semibold text-sm">
                        {{ $room->adults ?? 0 }} adults, {{ $room->children ?? 0 }} children
                    </p>
                </div>
            </div>
        </x-card>

        <x-card shadow class="border-l-4 {{ $room->is_active ? 'border-l-success' : 'border-l-error' }}">
            <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg {{ $room->is_active ? 'bg-success/10' : 'bg-error/10' }}">
                    <x-icon name="o-check-circle"
                        class="w-6 h-6 {{ $room->is_active ? 'text-success' : 'text-error' }}" />
                </div>
                <div>
                    <p class="text-xs text-base-content/60 uppercase tracking-wide">Status</p>
                    <x-badge :value="$room->is_active ? 'Active' : 'Inactive'"
                        class="badge-soft {{ $room->is_active ? 'badge-success' : 'badge-error' }} badge-sm" />
                </div>
            </div>
        </x-card>
    </div>

    {{-- Pricing Card --}}
    @if ($room->price_per_night)
        <x-card shadow>
            <x-slot:title class="flex items-center gap-2">
                <x-icon name="o-currency-dollar" class="w-5 h-5" />
                <span>Pricing</span>
            </x-slot:title>
            <div class="grid gap-6">
                <div class="flex items-baseline gap-4">
                    <div>
                        <p class="text-xs text-base-content/60 mb-1">Price Per Night</p>
                        <p class="text-3xl font-bold text-primary">{{ currency_format($room->price_per_night) }}</p>
                    </div>
                </div>

                @if ($room->price_per_night || $room->price_per_2night || $room->price_per_3night || $room->additional_night_price)
                    <div class="divider my-0"></div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @if ($room->price_per_night)
                            <div>
                                <p class="text-xs text-base-content/60 mb-1">Per Night</p>
                                <p class="text-lg font-semibold">{{ currency_format($room->price_per_night) }}</p>
                            </div>
                        @endif
                        @if ($room->price_per_2night)
                            <div>
                                <p class="text-xs text-base-content/60 mb-1">2 Nights</p>
                                <p class="text-lg font-semibold">{{ currency_format($room->price_per_2night) }}</p>
                            </div>
                        @endif
                        @if ($room->price_per_3night)
                            <div>
                                <p class="text-xs text-base-content/60 mb-1">3 Nights</p>
                                <p class="text-lg font-semibold">{{ currency_format($room->price_per_3night) }}</p>
                            </div>
                        @endif
                        @if ($room->additional_night_price)
                            <div>
                                <p class="text-xs text-base-content/60 mb-1">Additional Night</p>
                                <p class="text-lg font-semibold">{{ currency_format($room->additional_night_price) }}
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </x-card>
    @endif

    @if ($room->description)
        <x-card>
            <x-slot:title>Description</x-slot:title>
            <p class="text-base-content/80 whitespace-pre-line">{{ $room->description }}</p>
        </x-card>
    @endif

    {{-- Categories & Amenities --}}
    <div class="grid gap-4 md:grid-cols-2">
        <x-card shadow>
            <x-slot:title class="flex items-center gap-2">
                <x-icon name="o-tag" class="w-5 h-5" />
                <span>Categories</span>
            </x-slot:title>
            <div class="flex flex-wrap gap-2">
                @forelse ($room->categories as $category)
                    <x-badge :value="$category->name" class="badge-soft badge-primary" />
                @empty
                    <p class="text-sm text-base-content/50">No categories assigned</p>
                @endforelse
            </div>
        </x-card>

        <x-card shadow>
            <x-slot:title class="flex items-center gap-2">
                <x-icon name="o-sparkles" class="w-5 h-5" />
                <span>Amenities</span>
            </x-slot:title>
            <div class="flex flex-wrap gap-2">
                @forelse ($room->amenities as $amenity)
                    <x-badge :value="$amenity->name" class="badge-soft badge-secondary" />
                @empty
                    <p class="text-sm text-base-content/50">No amenities assigned</p>
                @endforelse
            </div>
        </x-card>
    </div>

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
                            <code class="text-sm font-mono break-all">{{ $room->slug }}</code>
                        </div>
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Room Number</p>
                            <code class="text-sm font-mono">{{ $room->room_number }}</code>
                        </div>
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Record ID</p>
                            <code class="text-sm font-mono">#{{ $room->id }}</code>
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
                            <p class="text-sm">{{ $room->meta_description ?? '—' }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Meta Keywords</p>
                            <p class="text-sm">{{ $room->meta_keywords ?? '—' }}</p>
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
                            <p class="text-sm font-semibold">{{ $room->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-base-content/50">{{ $room->created_at->format('h:i A') }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Updated</p>
                            <p class="text-sm font-semibold">{{ $room->updated_at->format('M d, Y') }}</p>
                            <p class="text-xs text-base-content/50">{{ $room->updated_at->format('h:i A') }}</p>
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
                    <p class="text-xs text-base-content/50">
                        {{ $booking->check_out ? Carbon::parse($booking->check_out)->format('M d, Y h:i A') : '—' }}
                    </p>
                </div>
            @endscope

            @scope('cell_guests', $booking)
                <div class="flex gap-1">
                    <x-badge :value="$booking->adults . ' Adults'" class="badge-soft badge-primary badge-sm" />
                    <x-badge :value="$booking->children . ' Children'" class="badge-soft badge-secondary badge-sm" />
                </div>
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
                <x-button icon="o-eye" link="{{ route('admin.bookings.house.show', $booking->id) }}"
                    class="btn-ghost btn-sm" tooltip="View Booking" />
            @endscope

            <x-slot:empty>
                <x-empty icon="o-calendar" message="No bookings yet for this room" />
            </x-slot:empty>
        </x-table>
    </x-card>
</div>
