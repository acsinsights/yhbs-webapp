<?php

use Livewire\Volt\Component;
use App\Services\CouponService;

new class extends Component {
    public string $couponCode = '';
    public ?array $appliedCoupon = null;
    public string $errorMessage = '';
    public string $successMessage = '';
    public bool $isLoading = false;

    // Booking details
    public float $bookingAmount;
    public ?float $pricePerNight = null;
    public ?int $nights = null;
    public ?string $propertyType = null;
    public ?int $propertyId = null;

    public function mount(float $bookingAmount, ?float $pricePerNight = null, ?int $nights = null, ?string $propertyType = null, ?int $propertyId = null): void
    {
        $this->bookingAmount = $bookingAmount;
        $this->pricePerNight = $pricePerNight;
        $this->nights = $nights;
        $this->propertyType = $propertyType;
        $this->propertyId = $propertyId;

        // Check if there's already an applied coupon in session
        if (session()->has('applied_coupon')) {
            $this->appliedCoupon = session('applied_coupon');
        }
    }

    public function applyCoupon(): void
    {
        $this->errorMessage = '';
        $this->successMessage = '';

        if (empty(trim($this->couponCode))) {
            $this->errorMessage = 'Please enter a coupon code';
            return;
        }

        $this->isLoading = true;

        try {
            $couponService = new CouponService();
            $result = $couponService->validateCoupon(strtoupper(trim($this->couponCode)), $this->bookingAmount, $this->pricePerNight, $this->nights, null, $this->propertyType, $this->propertyId);

            if ($result['valid']) {
                // Store coupon in session
                $this->appliedCoupon = [
                    'code' => $result['coupon']->code,
                    'discount_type' => $result['coupon']->discount_type->value,
                    'discount_value' => $result['coupon']->discount_value,
                    'max_discount_amount' => $result['coupon']->max_discount_amount,
                ];

                session(['applied_coupon' => $this->appliedCoupon]);

                $this->successMessage = $result['message'] ?? 'Coupon applied successfully!';
                $this->couponCode = '';

                // Dispatch event to update price breakdown (no page refresh)
                $this->dispatch('coupon-updated', coupon: $this->appliedCoupon);
            } else {
                $this->errorMessage = $result['error'];
            }
        } catch (\Exception $e) {
            $this->errorMessage = 'Failed to apply coupon. Please try again.';
            \Log::error('Coupon application error: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function removeCoupon(): void
    {
        $this->appliedCoupon = null;
        session()->forget('applied_coupon');
        $this->successMessage = 'Coupon removed successfully';
        $this->errorMessage = '';

        // Dispatch event to update price breakdown (no page refresh)
        $this->dispatch('coupon-updated', coupon: null);
    }

    public function getDiscountTextProperty(): string
    {
        if (!$this->appliedCoupon) {
            return '';
        }

        $type = $this->appliedCoupon['discount_type'] ?? 'fixed';
        $value = $this->appliedCoupon['discount_value'] ?? 0;

        if ($type === 'percentage') {
            return $value . '% off';
        }

        return currency_format(round($value)) . ' off';
    }
}; ?>

<div>
    @if ($appliedCoupon)
        <!-- Applied Coupon Badge -->
        <div class="applied-coupon-badge d-flex align-items-center justify-content-between"
            style="padding: 10px; background: #f0f9ff; border: 1px solid #0ea5e9; border-radius: 8px;">
            <div style="flex: 1;">
                <strong style="color: #0369a1;">{{ $appliedCoupon['code'] }}</strong>
                <span style="color: #059669;">applied</span>
                <div class="text-sm" style="color: #059669;">
                    <i class="bi bi-tag-fill"></i> Discount: {{ $this->discountText }}
                </div>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-danger" wire:click="removeCoupon"
                    wire:loading.attr="disabled" style="min-width: 80px;">
                    <span wire:loading.remove wire:target="removeCoupon">
                        <i class="bi bi-x-circle"></i> Remove
                    </span>
                    <span wire:loading wire:target="removeCoupon">
                        <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                        Removing...
                    </span>
                </button>
            </div>
        </div>
    @else
        <!-- Coupon Input -->
        <div class="coupon-input-wrapper">
            <input type="text" wire:model="couponCode" wire:keydown.enter="applyCoupon"
                class="form-control @error('couponCode') is-invalid @enderror" placeholder="Enter coupon code"
                style="text-transform: uppercase;" maxlength="50" {{ $isLoading ? 'disabled' : '' }}>
            <button type="button" class="btn btn-primary" wire:click="applyCoupon" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="applyCoupon">Apply</span>
                <span wire:loading wire:target="applyCoupon">
                    <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
                    Applying...
                </span>
            </button>
        </div>
    @endif

    <!-- Success Message -->
    @if ($successMessage)
        <div class="alert alert-success mt-2 d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ $successMessage }}
        </div>
    @endif

    <!-- Error Message -->
    @if ($errorMessage)
        <div class="alert alert-danger mt-2 d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ $errorMessage }}
        </div>
    @endif
</div>
