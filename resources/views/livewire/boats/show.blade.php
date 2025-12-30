<?php

use Mary\Traits\Toast;
use Livewire\Volt\Component;
use App\Models\Boat;

new class extends Component {
    use Toast;

    public Boat $boat;

    public function mount(Boat $boat): void
    {
        $this->boat = $boat->load('bookings');
    }

    public function delete(): void
    {
        if ($this->boat->bookings()->count() > 0) {
            $this->error('Cannot delete boat with existing bookings.');
            return;
        }

        $this->boat->delete();
        $this->success('Boat deleted successfully!', redirectTo: route('admin.boats.index'));
    }

    public function with(): array
    {
        return [
            'breadcrumbs' => [['label' => 'Dashboard', 'url' => route('admin.index')], ['label' => 'Boats', 'link' => route('admin.boats.index')], ['label' => $this->boat->name]],
        ];
    }
}; ?>

<div>
    <x-header :title="$boat->name" separator>
        <x-slot:middle>
            <x-breadcrumbs :items="$breadcrumbs" class="text-sm text-gray-500" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button icon="o-pencil" label="Edit" link="{{ route('admin.boats.edit', $boat) }}" class="btn-primary"
                responsive />
            <x-button icon="o-arrow-left" label="Back" link="{{ route('admin.boats.index') }}" class="btn-outline"
                responsive />
        </x-slot:actions>
    </x-header>

    <div class="grid gap-5 lg:grid-cols-3">
        {{-- Main Info --}}
        <div class="lg:col-span-2">
            <x-card title="Boat Information" class="mb-5">
                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Boat Name</div>
                        <div class="font-semibold">{{ $boat->name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Service Type</div>
                        <x-badge :value="$boat->service_type_label" class="badge-primary" />
                    </div>
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Capacity</div>
                        <div class="font-semibold">{{ $boat->min_passengers }}-{{ $boat->max_passengers }} passengers
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Location</div>
                        <div class="font-semibold">{{ $boat->location ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Status</div>
                        @if ($boat->is_active)
                            <x-badge value="Active" class="badge-success" />
                        @else
                            <x-badge value="Inactive" class="badge-error" />
                        @endif
                    </div>
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Featured</div>
                        @if ($boat->is_featured)
                            <x-badge value="Yes" class="badge-info" />
                        @else
                            <x-badge value="No" class="badge-ghost" />
                        @endif
                    </div>
                </div>

                @if ($boat->description)
                    <div class="mt-4">
                        <div class="text-sm text-base-content/50 mb-1">Description</div>
                        <div class="text-sm">{{ $boat->description }}</div>
                    </div>
                @endif

                @if ($boat->features)
                    <div class="mt-4">
                        <div class="text-sm text-base-content/50 mb-1">Features</div>
                        <div class="text-sm">{{ $boat->features }}</div>
                    </div>
                @endif
            </x-card>

            {{-- Pricing Information --}}
            <x-card title="Pricing Information">
                @if ($boat->service_type === 'marina_trip' || $boat->service_type === 'taxi')
                    <div class="grid gap-4 md:grid-cols-2">
                        @if ($boat->price_1hour)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">1 Hour</div>
                                <div class="font-semibold text-lg">KD {{ number_format($boat->price_1hour, 2) }}</div>
                            </div>
                        @endif
                        @if ($boat->price_2hours)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">2 Hours</div>
                                <div class="font-semibold text-lg">KD {{ number_format($boat->price_2hours, 2) }}</div>
                            </div>
                        @endif
                        @if ($boat->price_3hours)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">3 Hours</div>
                                <div class="font-semibold text-lg">KD {{ number_format($boat->price_3hours, 2) }}</div>
                            </div>
                        @endif
                        @if ($boat->additional_hour_price)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Additional Hour</div>
                                <div class="font-semibold text-lg">KD
                                    {{ number_format($boat->additional_hour_price, 2) }}</div>
                            </div>
                        @endif
                    </div>
                @elseif($boat->service_type === 'ferry')
                    <div class="grid gap-4 md:grid-cols-2">
                        @if ($boat->price_per_person_adult)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Adult (Per Person)</div>
                                <div class="font-semibold text-lg">KD
                                    {{ number_format($boat->price_per_person_adult, 2) }}</div>
                            </div>
                        @endif
                        @if ($boat->price_per_person_child)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Child (Per Person)</div>
                                <div class="font-semibold text-lg">KD
                                    {{ number_format($boat->price_per_person_child, 2) }}</div>
                            </div>
                        @endif
                        @if ($boat->private_trip_price)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Private Trip (One Way)</div>
                                <div class="font-semibold text-lg">KD {{ number_format($boat->private_trip_price, 2) }}
                                </div>
                            </div>
                        @endif
                        @if ($boat->private_trip_return_price)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Private Trip (Return)</div>
                                <div class="font-semibold text-lg">KD
                                    {{ number_format($boat->private_trip_return_price, 2) }}</div>
                            </div>
                        @endif
                    </div>
                @elseif($boat->service_type === 'limousine')
                    <div class="grid gap-4 md:grid-cols-3">
                        @if ($boat->price_15min)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">15 Minutes</div>
                                <div class="font-semibold text-lg">KD {{ number_format($boat->price_15min, 2) }}</div>
                            </div>
                        @endif
                        @if ($boat->price_30min)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">30 Minutes</div>
                                <div class="font-semibold text-lg">KD {{ number_format($boat->price_30min, 2) }}</div>
                            </div>
                        @endif
                        @if ($boat->price_full_boat)
                            <div>
                                <div class="text-sm text-base-content/50 mb-1">Full Boat (1 Hour)</div>
                                <div class="font-semibold text-lg">KD {{ number_format($boat->price_full_boat, 2) }}
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </x-card>
        </div>

        {{-- Statistics --}}
        <div>
            <x-card title="Statistics" class="mb-5">
                <div class="space-y-4">
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Total Bookings</div>
                        <div class="text-2xl font-bold">{{ $boat->bookings->count() }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Active Bookings</div>
                        <div class="text-2xl font-bold">
                            {{ $boat->bookings->whereIn('status', ['pending', 'confirmed'])->count() }}
                        </div>
                    </div>
                    <div>
                        <div class="text-sm text-base-content/50 mb-1">Total Revenue</div>
                        <div class="text-2xl font-bold">
                            KD
                            {{ number_format($boat->bookings->where('payment_status', 'paid')->sum('total_amount'), 2) }}
                        </div>
                    </div>
                </div>
            </x-card>

            <x-card title="Actions">
                <div class="space-y-2">
                    <x-button label="Create Booking" icon="o-plus-circle"
                        link="{{ route('admin.bookings.boat.create', ['boat_id' => $boat->id]) }}"
                        class="btn-outline w-full" />
                    <x-button label="View Bookings" icon="o-calendar"
                        link="{{ route('admin.bookings.boat.index', ['boat_id' => $boat->id]) }}"
                        class="btn-outline w-full" />
                    <x-button label="Delete Boat" icon="o-trash" wire:click="delete"
                        wire:confirm="Are you sure you want to delete this boat? This action cannot be undone."
                        class="btn-error btn-outline w-full" />
                </div>
            </x-card>

            @if ($boat->image)
                <x-card title="Boat Image" class="mt-5">
                    <img src="{{ asset('storage/' . $boat->image) }}" alt="{{ $boat->name }}"
                        class="w-full rounded-lg">
                </x-card>
            @endif
        </div>
    </div>
</div>
