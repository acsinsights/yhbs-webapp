<?php

use Mary\Traits\Toast;
use Illuminate\View\View;
use Livewire\Volt\Component;
use App\Models\Coupon;
use App\Enums\DiscountTypeEnum;

new class extends Component {
    use Toast;

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

    public function createCoupon(): void
    {
        try {
            $validated = $this->validate(
                [
                    'code' => 'required|string|max:50|unique:coupons,code',
                    'name' => 'required|string|max:255',
                    'description' => 'nullable|string',
                    'discount_type' => 'required|in:percentage,fixed,free_nights',
                    'discount_value' => 'required|numeric|min:0',
                    'min_nights_required' => 'nullable|integer|min:1',
                    'min_booking_amount' => 'nullable|numeric|min:0',
                    'max_discount_amount' => 'nullable|numeric|min:0',
                    'valid_from' => 'required|date',
                    'valid_until' => 'required|date|after:valid_from',
                    'usage_limit' => 'nullable|integer|min:1',
                    'usage_limit_per_user' => 'required|integer|min:1',
                    'is_active' => 'boolean',
                ],
                [
                    'code.required' => 'Coupon code is required',
                    'code.unique' => 'This coupon code already exists',
                    'name.required' => 'Coupon name is required',
                    'discount_value.required' => 'Discount value is required',
                    'discount_value.numeric' => 'Discount value must be a number',
                    'min_nights_required.integer' => 'Minimum nights must be a number',
                    'min_nights_required.min' => 'Minimum nights must be at least 1',
                    'valid_from.required' => 'Valid from date is required',
                    'valid_until.required' => 'Valid until date is required',
                    'valid_until.after' => 'Valid until date must be after valid from date',
                ],
            );

            Coupon::create([
                'code' => strtoupper($this->code),
                'name' => $this->name,
                'description' => $this->description,
                'discount_type' => \App\Enums\DiscountTypeEnum::from($this->discount_type),
                'discount_value' => $this->discount_value,
                'min_nights_required' => $this->min_nights_required ?: null,
                'min_booking_amount' => $this->min_booking_amount ?: 0,
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

            $this->success('Coupon created successfully!', redirectTo: route('admin.coupons.index'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->error('Please fix the validation errors below.');
            throw $e;
        } catch (\Exception $e) {
            $this->error('Error creating coupon: ' . $e->getMessage());
        }
    }

    public function discountTypes(): array
    {
        return [['id' => 'percentage', 'name' => 'Percentage (%)'], ['id' => 'fixed', 'name' => 'Fixed Amount (KWD)'], ['id' => 'free_nights', 'name' => 'Free Nights/Days']];
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
        class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-900 rounded-lg p-6 mb-6 shadow-sm">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center">
                    <svg class="w-8 h-8 mr-3 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                    Create New Coupon
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">Add a new discount coupon to offer special
                    deals to your customers</p>
            </div>
            <x-button label="Back" :link="route('admin.coupons.index')" icon="o-arrow-left" class="btn-outline" />
        </div>
    </div>

    <!-- Main Form Card -->
    <x-card class="shadow-lg border-0">
        <form wire:submit="createCoupon" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information Section -->
                <div class="col-span-2 bg-blue-50 dark:bg-gray-800 rounded-lg p-4 mb-4">
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-300 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Basic Information
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Enter the basic details for the coupon</p>
                </div>

                <x-input label="Coupon Code *" wire:model="code" placeholder="e.g., SUMMER2025"
                    hint="Enter unique coupon code (will be auto-uppercase)" icon="o-tag" class="uppercase" inline />

                <x-input label="Coupon Name *" wire:model="name" placeholder="e.g., Summer Sale 2025" icon="o-gift"
                    inline />

                <div class="col-span-2">
                    <x-textarea label="Description" wire:model="description" placeholder="Describe the offer (optional)"
                        rows="3" hint="This will be displayed to customers" inline />
                </div>

                <!-- Discount Configuration Section -->
                <div class="col-span-2 bg-green-50 dark:bg-gray-800 rounded-lg p-4 mb-4 mt-6">
                    <h3 class="text-lg font-semibold text-green-900 dark:text-green-300 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Discount Configuration
                    </h3>
                </div>

                <x-select label="Discount Type *" wire:model.live="discount_type" :options="$discountTypes"
                    placeholder="Select discount type" icon="o-calculator" inline />

                @if ($discount_type === 'free_nights')
                    <x-input label="Number of Free Nights *" wire:model="discount_value" type="number" step="1"
                        min="1" placeholder="e.g., 1, 2, or 3" suffix="Nights" icon="o-moon"
                        hint="Number of nights/days the customer gets free" inline />

                    <x-input label="Minimum Nights Required" wire:model="min_nights_required" type="number"
                        step="1" min="1" placeholder="e.g., 3 or 5" suffix="Nights" icon="o-calendar"
                        hint="Minimum booking nights required to get free nights (e.g., Book 3 nights get 1 free)"
                        inline />
                @else
                    <x-input label="Discount Value *" wire:model="discount_value" type="number"
                        step="{{ $discount_type === 'percentage' ? '0.01' : '0.001' }}" min="0"
                        placeholder="e.g., 10 or 25.50" :suffix="$discount_type === 'percentage' ? '%' : 'KWD'" icon="o-currency-dollar" inline />
                @endif

                <x-input label="Minimum Booking Amount" wire:model="min_booking_amount" type="number" step="0.001"
                    min="0" placeholder="0.000" suffix="KWD"
                    hint="Minimum order value required to use this coupon" icon="o-banknotes" inline />

                <x-input label="Maximum Discount Cap" wire:model="max_discount_amount" type="number" step="0.001"
                    min="0" placeholder="Leave empty for no limit" suffix="KWD"
                    hint="Maximum discount amount (optional)" icon="o-shield-check" inline />

                <!-- Validity Period Section -->
                <div class="col-span-2 bg-purple-50 dark:bg-gray-800 rounded-lg p-4 mb-4 mt-6">
                    <h3 class="text-lg font-semibold text-purple-900 dark:text-purple-300 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Validity Period
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Set the date range when this coupon is
                        active</p>
                </div>

                <x-input label="Valid From *" wire:model="valid_from" type="date" icon="o-calendar" inline />

                <x-input label="Valid Until *" wire:model="valid_until" type="date" icon="o-calendar" inline />

                <!-- Usage Limits Section -->
                <div class="col-span-2 bg-orange-50 dark:bg-gray-800 rounded-lg p-4 mb-4 mt-6">
                    <h3 class="text-lg font-semibold text-orange-900 dark:text-orange-300 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        Usage Limits
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Control how many times this coupon can be
                        used</p>
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

                <!-- Status Section -->
                <div class="col-span-2 mt-6 bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <x-checkbox label="Active Coupon" wire:model="is_active"
                        hint="Toggle to activate or deactivate this coupon. Inactive coupons cannot be used by customers." />
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between pt-6 border-t border-gray-200 dark:border-gray-700">
                <x-button label="Cancel" :link="route('admin.coupons.index')" class="btn-outline" icon="o-x-mark" />
                <x-button label="Create Coupon" class="btn-primary px-6" type="submit" spinner="createCoupon"
                    icon="o-check-circle" />
            </div>
        </form>
    </x-card>

    <!-- Help Card -->
    <x-card class="mt-6 bg-blue-50 dark:bg-gray-800 border-l-4 border-blue-500">
        <div class="text-sm">
            <h4 class="font-semibold text-blue-900 dark:text-blue-300 mb-2 flex items-center">
                <x-icon name="o-information-circle" class="w-5 h-5 mr-2" />
                Quick Tips
            </h4>
            <ul class="list-disc list-inside space-y-1 text-blue-800 dark:text-blue-200">
                <li><strong>Coupon Code:</strong> Will be automatically converted to uppercase for consistency</li>
                <li><strong>Percentage Discount:</strong> Will be calculated as (booking amount × percentage) / 100</li>
                <li><strong>Fixed Discount:</strong> Will deduct the exact amount from booking total</li>
                <li><strong>Free Nights:</strong> Customer gets X nights/days free (e.g., "Book 3 Get 1 Free")</li>
                <li><strong>Max Discount Cap:</strong> Limits how much discount can be given (useful for percentage
                    discounts)</li>
                <li><strong>Unlimited Usage:</strong> Leave "Total Usage Limit" empty to allow unlimited uses</li>
            </ul>
        </div>
    </x-card>
</div>
