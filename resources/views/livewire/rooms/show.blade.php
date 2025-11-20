<?php

use App\Models\Room;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Illuminate\View\View;
use Carbon\Carbon;

new class extends Component {
    use Toast;

    public Room $room;

    public function mount($room): void
    {
        $this->room = $room instanceof Room ? $room->load(['hotel', 'categories', 'amenities']) : Room::with(['hotel', 'categories', 'amenities'])->findOrFail($room);
    }

    public function delete(): void
    {
        if ($this->room->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete(str_replace('/storage/', '', $this->room->image));
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
    if ($room->library && is_iterable($room->library)) {
        foreach ($room->library as $item) {
            if (is_string($item) && $item !== '') {
                $slides[] = ['image' => asset($item)];
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
            <x-button icon="o-pencil" label="Edit" link="{{ route('admin.rooms.edit', $room) }}" class="btn-primary" />
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.rooms.index') }}" class="btn-ghost" />
            <x-button icon="o-trash" label="Delete" wire:click="delete"
                wire:confirm="Delete this room? This action cannot be undone." class="btn-error" />
        </x-slot:actions>
    </x-header>

    <x-card>
        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-4">
                <div>
                    <p class="text-sm text-base-content/60 mb-1">Room Number</p>
                    <code class="px-3 py-1 rounded bg-base-200 text-sm font-mono">{{ $room->room_number }}</code>
                </div>
                <div>
                    <p class="text-sm text-base-content/60 mb-1">Slug</p>
                    <code class="px-3 py-1 rounded bg-base-200 text-sm">{{ $room->slug }}</code>
                </div>
                <div class="space-y-1">
                    <p class="text-sm text-base-content/60">Hotel</p>
                    <p class="font-semibold">{{ $room->hotel->name ?? 'N/A' }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-sm text-base-content/60">Capacity</p>
                    <p class="font-semibold">
                        {{ $room->adults ?? 0 }} adults · {{ $room->children ?? 0 }} children
                    </p>
                </div>
                <div class="space-y-1">
                    <p class="text-sm text-base-content/60">Status</p>
                    <x-badge :value="$room->is_active ? 'Active' : 'Inactive'"
                        class="badge-soft {{ $room->is_active ? 'badge-success' : 'badge-error' }}" />
                </div>
                @if ($room->price)
                <div>
                    <p class="text-sm text-base-content/60 mb-1">Price</p>
                    <p class="text-2xl font-bold text-primary">{{ currency_format($room->price) }}</p>
                    @if ($room->discount_price)
                        <p class="text-lg font-semibold text-success">
                            {{ currency_format($room->discount_price) }}</p>
                            @php
                                $discountPercent = round((($room->price - $room->discount_price) / $room->price) * 100);
                            @endphp
                            <x-badge :value="$discountPercent . '% OFF'" class="badge-success badge-sm mt-1" />
                        @endif
                    </div>
                @endif
                <div class="space-y-1">
                    <p class="text-sm text-base-content/60">Created</p>
                    <p class="font-semibold">{{ $room->created_at->format('M d, Y') }}</p>
                </div>
                <div class="space-y-1">
                    <p class="text-sm text-base-content/60">Updated</p>
                    <p class="font-semibold">{{ $room->updated_at->format('M d, Y') }}</p>
                </div>
            </div>
            <div class="aspect-video rounded-xl overflow-hidden bg-base-200 flex items-center justify-center">
                @if ($room->image)
                    <img src="{{ asset($room->image) }}" alt="{{ $room->name }}"
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

    @if ($room->description)
        <x-card>
            <x-slot:title>Description</x-slot:title>
            <p class="text-base-content/80 whitespace-pre-line">{{ $room->description }}</p>
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
                            <span class="font-mono text-sm break-all">{{ $room->slug }}</span>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Room Number</p>
                            <span class="font-mono text-sm">{{ $room->room_number }}</span>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Record ID</p>
                            <span class="font-mono text-sm">{{ $room->id }}</span>
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>

            <x-collapse separator>
                <x-slot:heading>Categories & Amenities</x-slot:heading>
                <x-slot:content>
                    <div class="space-y-2">
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Categories</p>
                            <div class="flex flex-wrap gap-1">
                                @forelse ($room->categories as $category)
                                    <x-badge :value="$category->name" class="badge-soft badge-sm" />
                                @empty
                                    <span class="text-base-content/50 text-sm">No categories assigned</span>
                                @endforelse
                            </div>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Amenities</p>
                            <div class="flex flex-wrap gap-1">
                                @forelse ($room->amenities as $amenity)
                                    <x-badge :value="$amenity->name" class="badge-soft badge-sm" />
                                @empty
                                    <span class="text-base-content/50 text-sm">No amenities assigned</span>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>

            <x-collapse separator>
                <x-slot:heading>SEO</x-slot:heading>
                <x-slot:content>
                    <div class="grid gap-4">
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Meta Description</p>
                            <p class="text-sm">{{ $room->meta_description ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-base-content/60 uppercase mb-1">Meta Keywords</p>
                            <p class="text-sm">{{ $room->meta_keywords ?? '—' }}</p>
                        </div>
                    </div>
                </x-slot:content>
            </x-collapse>

            <x-collapse separator>
                <x-slot:heading>Pricing</x-slot:heading>
                <x-slot:content>
                    <div class="grid gap-4 md:grid-cols-2">
                        @if ($room->price)
                            <div>
                                <p class="text-xs text-base-content/60 uppercase mb-1">Regular Price</p>
                                <span class="text-xl font-bold text-primary">{{ currency_format($room->price) }}</span>
                            </div>
                        @endif
                        @if ($room->discount_price)
                            <div>
                                <p class="text-xs text-base-content/60 uppercase mb-1">Discount Price</p>
                                <span class="text-xl font-bold text-success">{{ currency_format($room->discount_price) }}</span>
                                @if ($room->price)
                                    @php
                                        $discountPercent = round(
                                            (($room->price - $room->discount_price) / $room->price) * 100,
                                        );
                                    @endphp
                                    <x-badge :value="$discountPercent . '% OFF'" class="badge-success badge-sm mt-1" />
                                @endif
                            </div>
                        @endif
                    </div>
                </x-slot:content>
            </x-collapse>
        </div>
    </x-card>

    <x-card>
        <x-slot:title>Booking History</x-slot:title>
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
                @php
                    $statusColors = [
                        'pending' => 'badge-warning',
                        'booked' => 'badge-primary',
                        'checked_in' => 'badge-info',
                        'cancelled' => 'badge-error',
                        'checked_out' => 'badge-success',
                    ];
                    $color = $statusColors[$booking->status] ?? 'badge-ghost';
                @endphp
                <x-badge :value="ucfirst(str_replace('_', ' ', $booking->status))" class="badge-soft {{ $color }}" />
            @endscope

            @scope('cell_payment', $booking)
                <div class="flex flex-col gap-1">
                    <x-badge :value="ucfirst($booking->payment_status)" class="badge-soft badge-sm badge-info" />
                    <span class="text-xs text-base-content/50">{{ ucfirst($booking->payment_method ?? '—') }}</span>
                </div>
            @endscope

            @scope('actions', $booking)
                <x-button icon="o-eye" link="{{ route('admin.bookings.hotel.show', $booking->id) }}"
                    class="btn-ghost btn-sm" tooltip="View Booking" />
            @endscope

            <x-slot:empty>
                <x-empty icon="o-calendar" message="No bookings yet for this room" />
            </x-slot:empty>
        </x-table>
    </x-card>
</div>
