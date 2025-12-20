<?php

use Mary\Traits\Toast;
use Illuminate\View\View;
use Livewire\Volt\Component;
use App\Models\Coupon;
use App\Enums\DiscountTypeEnum;

new class extends Component {
    use Toast;

    public Coupon $coupon;

    public string $code = '';
    public string $name = '';
    public string $description = '';
    public string $discount_type = 'percentage';
    public string $discount_value = '';
    public string $min_nights_required = '';
    public string $min_booking_amount = '0';
    public string $max_discount_amount = '';
    public string $valid_from = '';
    public string $valid_until = '';
    public string $usage_limit = '';
    public string $usage_limit_per_user = '1';
    public bool $is_active = true;
    public string $applicable_to = 'all';
    public array $applicable_rooms = [];
    public array $applicable_houses = [];
    public array $applicable_yachts = [];

    public function mount(Coupon $coupon): void
    {
        // Refresh coupon to get latest usage count
        $this->coupon = $coupon->fresh();
        $this->code = $this->coupon->code;
        $this->name = $coupon->name;
        $this->description = $coupon->description ?? '';
        $this->discount_type = $coupon->discount_type->value;
        $this->discount_value = (string) $coupon->discount_value;
        $this->min_nights_required = $coupon->min_nights_required ? (string) $coupon->min_nights_required : '';
        $this->min_booking_amount = (string) $coupon->min_booking_amount;
        $this->max_discount_amount = $coupon->max_discount_amount ? (string) $coupon->max_discount_amount : '';

        // Handle both Carbon and string dates
        $this->valid_from = $coupon->valid_from instanceof \Carbon\Carbon ? $coupon->valid_from->format('Y-m-d') : (is_string($coupon->valid_from) ? $coupon->valid_from : '');
        $this->valid_until = $coupon->valid_until instanceof \Carbon\Carbon ? $coupon->valid_until->format('Y-m-d') : (is_string($coupon->valid_until) ? $coupon->valid_until : '');

        $this->usage_limit = $coupon->usage_limit ? (string) $coupon->usage_limit : '';
        $this->usage_limit_per_user = (string) $coupon->usage_limit_per_user;
        $this->is_active = $coupon->is_active;
        $this->applicable_to = $coupon->applicable_to ?? 'all';
        $this->applicable_rooms = $coupon->applicable_rooms ?? [];
        $this->applicable_houses = $coupon->applicable_houses ?? [];
        $this->applicable_yachts = $coupon->applicable_yachts ?? [];
    }

    public function update(): void
    {
        $validated = $this->validate([
            'code' => 'required|string|max:50|unique:coupons,code,' . $this->coupon->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0',
            'min_nights_required' => 'nullable|integer|min:1',
            'min_booking_amount' => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date|after:valid_from',
            'usage_limit' => 'nullable|integer|min:1',
            'usage_limit_per_user' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        $this->coupon->update([
            'code' => strtoupper($this->code),
            'name' => $this->name,
            'description' => $this->description,
            'discount_type' => \App\Enums\DiscountTypeEnum::from($this->discount_type),
            'discount_value' => $this->discount_value,
            'min_nights_required' => $this->min_nights_required ?: null,
            'min_booking_amount' => $this->min_booking_amount,
            'max_discount_amount' => $this->max_discount_amount ?: null,
            'valid_from' => $this->valid_from,
            'valid_until' => $this->valid_until,
            'usage_limit' => $this->usage_limit ?: null,
            'usage_limit_per_user' => $this->usage_limit_per_user,
            'is_active' => $this->is_active,
            'applicable_to' => $this->applicable_to,
            'applicable_rooms' => $this->applicable_to === 'specific' ? $this->applicable_rooms : null,
            'applicable_houses' => $this->applicable_to === 'specific' ? $this->applicable_houses : null,
            'applicable_yachts' => $this->applicable_to === 'specific' ? $this->applicable_yachts : null,
        ]);

        $this->success('Coupon updated successfully.', redirectTo: route('admin.coupons.index'));
    }

    public function discountTypes(): array
    {
        return [['id' => 'percentage', 'name' => 'Percentage (%)'], ['id' => 'fixed', 'name' => 'Fixed Amount (KWD)']];
    }

    public function with(): array
    {
        $rooms = \App\Models\Room::select('id', 'name')->get()->map(fn($r) => ['id' => $r->id, 'name' => $r->name])->toArray();
        $houses = \App\Models\House::select('id', 'name')->get()->map(fn($h) => ['id' => $h->id, 'name' => $h->name])->toArray();
        $yachts = \App\Models\Yacht::select('id', 'name')->get()->map(fn($y) => ['id' => $y->id, 'name' => $y->name])->toArray();

        return [
            'discountTypes' => $this->discountTypes(),
            'rooms' => $rooms,
            'houses' => $houses,
            'yachts' => $yachts,
        ];
    }
}; ?>

<div>
    <!-- Page Header with Gradient -->
    <div
        class="bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-gray-800 dark:to-gray-900 rounded-lg p-6 mb-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-8 h-8 mr-3 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit Coupon: <span class="text-indigo-600 dark:text-indigo-400 ml-2">{{ $coupon->code }}</span>
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Update coupon details, validity, and usage
                    limits</p>
            </div>
            <x-button label="Back" :link="route('admin.coupons.index')" icon="o-arrow-left" class="btn-outline" />
        </div>
    </div>

    <!-- Usage Stats Card -->
    <x-card
        class="mb-6 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900 border-l-4 border-blue-500">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <x-icon name="o-ticket" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total Used</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $coupon->usage_count }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <x-icon name="o-chart-bar" class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Usage Limit</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $coupon->usage_limit ?? '∞' }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <x-icon name="o-users" class="w-6 h-6 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Per User</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $coupon->usage_limit_per_user }}</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <div class="p-3 bg-white dark:bg-gray-700 rounded-lg shadow-sm">
                    <x-icon name="{{ $coupon->is_active ? 'o-check-circle' : 'o-x-circle' }}"
                        class="w-6 h-6 {{ $coupon->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" />
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Status</p>
                    <p
                        class="text-xl font-bold {{ $coupon->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $coupon->is_active ? 'Active' : 'Inactive' }}
                    </p>
                </div>
            </div>
        </div>
    </x-card>

    <x-card>
        <x-form wire:submit="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="col-span-2">
                    <h3 class="text-lg font-semibold mb-4 text-primary">Basic Information</h3>
                </div>

                <x-input label="Coupon Code *" wire:model="code" placeholder="e.g., SUMMER2025"
                    hint="Enter unique coupon code (will be auto-uppercase)" icon="o-tag" class="uppercase" inline />

                <x-input label="Coupon Name *" wire:model="name" placeholder="e.g., Summer Sale 2025" icon="o-gift"
                    inline />

                <div class="col-span-2">
                    <x-textarea label="Description" wire:model="description" placeholder="Describe the offer (optional)"
                        rows="3" hint="This will be displayed to customers" inline />
                </div>

                <!-- Discount Configuration -->
                <div class="col-span-2 mt-4">
                    <h3 class="text-lg font-semibold mb-4 text-primary">Discount Configuration</h3>
                </div>

                <x-select label="Discount Type *" wire:model.live="discount_type" :options="$discountTypes"
                    placeholder="Select discount type" icon="o-calculator" inline />

                <x-input label="Discount Value *" wire:model="discount_value" type="number"
                    step="{{ $discount_type === 'percentage' ? '0.01' : '0.001' }}" min="0"
                    placeholder="e.g., 10 or 25.50" :suffix="$discount_type === 'percentage' ? '%' : 'KWD'" icon="o-currency-dollar" inline />

                <x-input label="Minimum Booking Amount" wire:model="min_booking_amount" type="number" step="0.001"
                    min="0" placeholder="0.000" suffix="KWD"
                    hint="Minimum order value required to use this coupon" icon="o-banknotes" inline />

                <x-input label="Maximum Discount Cap" wire:model="max_discount_amount" type="number" step="0.001"
                    min="0" placeholder="Leave empty for no limit" suffix="KWD"
                    hint="Maximum discount amount (optional)" icon="o-shield-check" inline />

                <!-- Validity Period -->
                <div class="col-span-2 mt-4">
                    <h3 class="text-lg font-semibold mb-4 text-primary">Validity Period</h3>
                </div>

                <x-input label="Valid From *" wire:model="valid_from" type="date" icon="o-calendar" inline />

                <x-input label="Valid Until *" wire:model="valid_until" type="date" icon="o-calendar" inline />

                <!-- Usage Limits -->
                <div class="col-span-2 mt-4">
                    <h3 class="text-lg font-semibold mb-4 text-primary">Usage Limits</h3>
                </div>

                <x-input label="Total Usage Limit" wire:model="usage_limit" type="number" min="1"
                    placeholder="Leave empty for unlimited" hint="Total number of times this coupon can be used"
                    icon="o-users" inline />

                <x-input label="Per User Limit *" wire:model="usage_limit_per_user" type="number" min="1"
                    placeholder="1" hint="How many times each user can use this coupon" icon="o-user" inline />

                <!-- Property Selection Section -->
                <div class="col-span-2 bg-teal-50 dark:bg-gray-800 rounded-lg p-4 mb-4 mt-6">
                    <h3 class="text-lg font-semibold text-teal-900 dark:text-teal-300 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Applicable Properties
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Select which properties can use this
                        coupon</p>
                </div>

                <div class="col-span-2 space-y-3">
                    <label
                        class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-teal-500 {{ $applicable_to === 'all' ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                        <input type="radio" wire:model.live="applicable_to" value="all" class="mt-1 mr-3">
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">Apply to All Properties</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">This coupon will work on all
                                rooms, houses, and yachts</div>
                        </div>
                    </label>

                    <label
                        class="flex items-start p-4 border-2 rounded-lg cursor-pointer transition-all hover:border-teal-500 {{ $applicable_to === 'specific' ? 'border-teal-500 bg-teal-50 dark:bg-teal-900/20' : 'border-gray-200 dark:border-gray-700' }}">
                        <input type="radio" wire:model.live="applicable_to" value="specific" class="mt-1 mr-3">
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-white">Apply to Specific Properties</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">Choose specific properties where
                                this coupon can be used</div>
                        </div>
                    </label>
                </div>

                @if ($applicable_to === 'specific')
                    <div
                        class="col-span-2 space-y-4 mt-4 p-4 border border-teal-200 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-900">
                        <x-choices-offline label="Applicable Rooms" wire:model="applicable_rooms" :options="$rooms"
                            search-function="name" searchable multiple
                            hint="Select rooms where this coupon can be used" />

                        <x-choices-offline label="Applicable Houses" wire:model="applicable_houses" :options="$houses"
                            search-function="name" searchable multiple
                            hint="Select houses where this coupon can be used" />

                        <x-choices-offline label="Applicable Yachts" wire:model="applicable_yachts" :options="$yachts"
                            search-function="name" searchable multiple
                            hint="Select yachts where this coupon can be used" />
                    </div>
                @endif

                <!-- Status -->
                <div class="col-span-2 mt-4">
                    <x-checkbox label="Active" wire:model="is_active"
                        hint="Inactive coupons cannot be used by customers" />
                </div>
            </div>

            <!-- Actions -->
            <x-slot:actions>
                <x-button label="Cancel" :link="route('admin.coupons.index')" class="btn-ghost w-full sm:w-auto" responsive />
                <x-button icon="o-check" label="Update Coupon" type="submit" class="btn-primary w-full sm:w-auto"
                    spinner="update" responsive />
            </x-slot:actions>
        </x-form>
    </x-card>

    <!-- Bookings Using This Coupon -->
    @if ($coupon->bookings()->count() > 0)
        <x-card class="mt-6">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <x-icon name="o-document-text" class="w-5 h-5 mr-2" />
                Recent Bookings Using This Coupon
            </h3>
            <div class="space-y-2">
                @foreach ($coupon->bookings()->latest()->limit(5)->get() as $booking)
                    <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                        <div>
                            <p class="font-medium">Booking #{{ $booking->id }}</p>
                            <p class="text-sm opacity-60">{{ $booking->user->name }} -
                                {{ $booking->created_at->format('d M Y') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm opacity-60">Discount</p>
                            <p class="font-semibold text-success">{{ number_format($booking->discount_amount, 2) }}
                                KWD</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    @endif
</div>
