<?php

use Carbon\Carbon;
use Mary\Traits\Toast;
use App\Models\House;
use Illuminate\View\View;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use Toast;

    public House $house;

    public function mount($house): void
    {
        $this->house = $house instanceof House ? $house : House::findOrFail($house);
    }

    public function delete(): void
    {
        // Delete image if exists
        if ($this->house->image) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $this->house->image));
        }

        $this->house->delete();
        $this->success('House deleted successfully!', redirectTo: route('admin.houses.index'));
    }

    public function rendering(View $view): void
    {
        $view->bookings = $this->house->bookings()->with('user')->orderByDesc('check_in')->get();

        $view->bookingHeaders = [['key' => 'id', 'label' => '#', 'class' => 'w-1'], ['key' => 'customer', 'label' => 'Customer', 'class' => 'whitespace-nowrap'], ['key' => 'check_in', 'label' => 'Check In', 'class' => 'whitespace-nowrap'], ['key' => 'check_out', 'label' => 'Check Out', 'class' => 'whitespace-nowrap'], ['key' => 'amount', 'label' => 'Amount', 'class' => 'whitespace-nowrap'], ['key' => 'payment_status', 'label' => 'Payment Status', 'class' => 'whitespace-nowrap'], ['key' => 'payment_method', 'label' => 'Payment Method', 'class' => 'whitespace-nowrap'], ['key' => 'status', 'label' => 'Status', 'class' => 'whitespace-nowrap']];
    }
}; ?>

@php
    $slides = [];

    // Add main image first if it exists
    if ($house->image) {
        $slides[] = ['image' => asset($house->image)];
    }

    // Add library images
    if ($house->library && is_iterable($house->library)) {
        foreach ($house->library as $item) {
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
    <x-header title="{{ $house->name }}" separator>
        <x-slot:subtitle>
            <p class="text-sm text-base-content/60">House overview and media</p>
        </x-slot:subtitle>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.houses.index') }}"
                class="btn-outline btn-primary" responsive />
            <x-button icon="o-pencil" label="Edit" link="{{ route('admin.houses.edit', $house) }}" class="btn-primary"
                responsive />
            <x-button icon="o-trash" label="Delete" wire:click="delete"
                wire:confirm="Delete this house? This action cannot be undone." class="btn-error" responsive />
        </x-slot:actions>
    </x-header>

    {{-- Main Image Carousel --}}
    <x-card shadow>
        @if (!empty($slides))
            <x-carousel :slides="$slides" />
        @else
            <div class="rounded-lg overflow-hidden bg-base-200 flex items-center justify-center h-96">
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
                    <code class="text-sm break-all">{{ $house->slug }}</code>
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
                    <p class="font-semibold text-sm font-mono">#{{ $house->id }}</p>
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
                    <p class="font-semibold text-sm">{{ $house->created_at->format('M d, Y g:i A') }}</p>
                </div>
            </div>
        </x-card>
    </div>

    {{-- Description --}}
    @if ($house->description)
        <x-card shadow>
            <x-slot:title class="flex items-center gap-2">
                <x-icon name="o-document-text" class="w-5 h-5" />
                <span>Description</span>
            </x-slot:title>

            <div class="text-base-content/80 whitespace-pre-line">
                {!! $house->description !!}
            </div>
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
                            <code class="text-sm font-mono break-all">{{ $house->slug }}</code>
                        </div>
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Record ID</p>
                            <code class="text-sm font-mono">#{{ $house->id }}</code>
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
                            <p class="text-sm font-semibold">{{ $house->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-base-content/50">{{ $house->created_at->format('h:i A') }}</p>
                        </div>
                        <div class="p-3 rounded-lg bg-base-200/50">
                            <p class="text-xs text-base-content/60 uppercase mb-1 tracking-wide">Last Updated</p>
                            <p class="text-sm font-semibold">{{ $house->updated_at->format('M d, Y') }}</p>
                            <p class="text-xs text-base-content/50">{{ $house->updated_at->format('h:i A') }}</p>
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

            @scope('cell_check_in', $booking)
                <div class="text-sm">
                    {{ $booking->check_in ? Carbon::parse($booking->check_in)->format('M d, Y h:i A') : '—' }}
                </div>
            @endscope

            @scope('cell_check_out', $booking)
                <div class="text-sm">
                    {{ $booking->check_out ? Carbon::parse($booking->check_out)->format('M d, Y h:i A') : '—' }}
                </div>
            @endscope

            @scope('cell_amount', $booking)
                <div class="font-semibold">{{ currency_format($booking->price ?? 0) }}</div>
            @endscope

            @scope('cell_payment_status', $booking)
                <x-badge :value="$booking->payment_status->label()" class="{{ $booking->payment_status->badgeColor() }}" />
            @endscope

            @scope('cell_payment_method', $booking)
                <x-badge :value="$booking->payment_method->label()" class="{{ $booking->payment_method->badgeColor() }}" />
            @endscope

            @scope('cell_status', $booking)
                <x-badge :value="$booking->status->label()" class="{{ $booking->status->badgeColor() }}" />
            @endscope

            @scope('actions', $booking)
                <x-button icon="o-eye" link="{{ route('admin.bookings.house.show', $booking->id) }}"
                    class="btn-ghost btn-sm" tooltip="View Booking" />
            @endscope

            <x-slot:empty>
                <x-empty icon="o-calendar" message="No bookings yet for this house" />
            </x-slot:empty>
        </x-table>
    </x-card>
</div>
