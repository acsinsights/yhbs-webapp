<?php

use Livewire\Volt\Component;
use Livewire\Attributes\On;

new class extends Component {
    public $booking;
    public $walletBalance = 0;
    public $appliedCoupon = null;

    // Calculated values
    public $subtotal = 0;
    public $serviceFee = 0;
    public $tax = 0;
    public $discount = 0;
    public $baseAmount = 0;
    public $finalAmount = 0;
    public $totalSavings = 0;
    public $tieredPricingSavings = 0;

    public function mount($booking, $walletBalance = 0, $tieredPricingSavings = 0): void
    {
        $this->booking = $booking;
        $this->walletBalance = $walletBalance;
        $this->tieredPricingSavings = $tieredPricingSavings;

        // Check if there's already an applied coupon in session
        if (session()->has('applied_coupon')) {
            $this->appliedCoupon = session('applied_coupon');
        }

        $this->calculatePrices();
    }

    #[On('coupon-updated')]
    public function updateCoupon($coupon = null): void
    {
        $this->appliedCoupon = $coupon;
        $this->calculatePrices();
    }

    public function calculatePrices(): void
    {
        // Calculate subtotal
        $backend = floatval($this->booking->subtotal ?? 0);
        $calculated = floatval($this->booking->price_per_night ?? 0) * floatval($this->booking->nights ?? 1);
        $this->subtotal = $backend > 0 ? $backend : $calculated;

        $this->serviceFee = floatval($this->booking->service_fee ?? 0);
        $this->tax = floatval($this->booking->tax ?? 0);
        $this->baseAmount = $this->subtotal + $this->serviceFee + $this->tax;

        // Calculate discount if coupon applied
        $this->discount = 0;
        if ($this->appliedCoupon) {
            $discountType = $this->appliedCoupon['discount_type'] ?? 'fixed';
            $discountValue = floatval($this->appliedCoupon['discount_value'] ?? 0);
            $maxDiscount = isset($this->appliedCoupon['max_discount_amount']) && $this->appliedCoupon['max_discount_amount'] ? floatval($this->appliedCoupon['max_discount_amount']) : null;

            if ($discountType === 'percentage') {
                $this->discount = round(($this->baseAmount * $discountValue) / 100);
                if ($maxDiscount && $this->discount > $maxDiscount) {
                    $this->discount = round($maxDiscount);
                }
            } else {
                // Fixed amount
                $this->discount = round($discountValue);
            }

            $this->discount = min($this->discount, $this->baseAmount);
        }

        // Calculate final amount
        $this->finalAmount = max(0, $this->baseAmount - $this->discount);

        // Calculate total savings
        $this->totalSavings = 0;
        if ($this->booking->type !== 'boat') {
            $this->totalSavings += $this->tieredPricingSavings;
        }
        $this->totalSavings += $this->discount;
    }
}; ?>

<div>
    <div class="price-breakdown">
        @if ($booking->type === 'boat')
            <!-- For boats, show subtotal directly without breaking it down -->
            <div class="price-row">
                <span>
                    @if ($booking->service_type === 'hourly')
                        Booking Amount ({{ $booking->duration ?? 1 }} hour(s))
                    @elseif ($booking->service_type === 'ferry_service')
                        Ferry Trip Amount
                    @elseif ($booking->service_type === 'experience')
                        Experience Amount
                    @else
                        Booking Amount
                    @endif
                </span>
                <span>{{ currency_format($subtotal) }}</span>
            </div>
        @else
            <!-- For houses/rooms, show price per night breakdown -->
            <div class="price-row">
                <span>Price per night</span>
                <span>{{ currency_format($booking->price_per_night ?? 0) }}</span>
            </div>
            <div class="price-row">
                <span>× {{ $booking->nights ?? '1' }} nights</span>
                <span>{{ currency_format($subtotal) }}</span>
            </div>
        @endif

        @if ($serviceFee > 0)
            <div class="price-row">
                <span>Service Fee</span>
                <span>{{ currency_format($serviceFee) }}</span>
            </div>
        @endif

        @if ($tax > 0)
            <div class="price-row">
                <span>Tax</span>
                <span>{{ currency_format($tax) }}</span>
            </div>
        @endif

        @if ($appliedCoupon)
            <div class="price-row discount-row">
                <span class="text-success">
                    <i class="bi bi-tag-fill me-1"></i>Coupon Discount
                </span>
                <span class="text-success">
                    -{{ currency_format($discount) }}
                </span>
            </div>
        @endif
    </div>

    <div class="divider"></div>

    @if ($walletBalance > 0)
        <div class="wallet-section mb-3">
            <div class="wallet-card">
                <div class="wallet-header">
                    <div class="wallet-icon-wrapper">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <div class="wallet-info">
                        <h6 class="mb-0 text-white">Wallet Balance</h6>
                        <p class="wallet-amount mb-0">{{ currency_format($walletBalance) }}</p>
                    </div>
                </div>
                <div class="wallet-toggle-section">
                    <label class="wallet-toggle-label">
                        <input class="wallet-checkbox" type="checkbox" id="use_wallet_balance" name="use_wallet_balance"
                            value="1" onchange="toggleWalletUsage(this)">
                        <span class="wallet-label-text">
                            <i class="bi bi-check-circle"></i> Use wallet for this booking
                        </span>
                    </label>
                </div>
                <div class="wallet-note">
                    <i class="bi bi-info-circle"></i>
                    Your wallet balance will be automatically applied to reduce the total amount
                </div>
            </div>
        </div>
        <div class="divider"></div>
    @endif

    <div class="total-price">
        <span>Total Amount</span>
        <span id="totalAmount">{{ currency_format($finalAmount) }}</span>
        <input type="hidden" id="originalTotal" value="{{ $finalAmount }}">
        <input type="hidden" id="walletBalance" value="{{ $walletBalance }}">
    </div>

    @if ($totalSavings > 0)
        <div class="price-row my-3"
            style="background: #d4edda; padding: 12px; border-radius: 8px; border: 2px solid #28a745;">
            <span style="color: #155724; font-weight: 600; font-size: 16px;">
                <i class="bi bi-piggy-bank-fill me-2"></i>You Saved
            </span>
            <span id="totalSavingsDisplay" style="color: #155724; font-weight: 600; font-size: 16px;">
                {{ currency_format($totalSavings) }}
            </span>
        </div>
    @endif

    <!-- Wallet Applied Amount -->
    <div id="walletAppliedRow" class="wallet-applied-row" style="display: none; margin-top: 1rem;">
        <div class="wallet-applied-content">
            <span>
                <i class="bi bi-wallet2 me-2"></i>Wallet Balance Used
            </span>
            <span class="wallet-applied-badge" id="walletAppliedAmount">-{{ currency_format(0) }}</span>
        </div>
    </div>

    <!-- Amount to Pay -->
    <div id="amountToPayRow" class="amount-to-pay-row" style="display: none; margin-top: 0.75rem;">
        <span class="pay-label">
            <i class="bi bi-cash-coin me-2"></i><strong>Amount to Pay</strong>
        </span>
        <span class="pay-amount" id="amountToPay"><strong>{{ currency_format($finalAmount) }}</strong></span>
    </div>

    <script>
        // Store and update base savings
        let componentSavings = @js($totalSavings);
        let componentFinalAmount = @js($finalAmount);

        // Update values on component load
        window.addEventListener('livewire:initialized', () => {
            window.currentBaseSavings = componentSavings;
        });

        // Listen for coupon updates via Livewire
        document.addEventListener('livewire:init', () => {
            Livewire.on('coupon-updated', () => {
                // Wait for DOM to update
                setTimeout(() => {
                    // Update hidden inputs with new values
                    const originalTotalInput = document.getElementById('originalTotal');
                    const totalAmountDisplay = document.getElementById('totalAmount');

                    if (originalTotalInput && totalAmountDisplay) {
                        // Extract amount from display
                        const displayText = totalAmountDisplay.textContent.trim();
                        const amountMatch = displayText.match(/[\d,]+\.?\d*/);
                        if (amountMatch) {
                            const newAmount = parseFloat(amountMatch[0].replace(/,/g, ''));
                            originalTotalInput.value = newAmount;
                        }
                    }

                    // Update base savings from display
                    const totalSavingsDisplay = document.getElementById('totalSavingsDisplay');
                    if (totalSavingsDisplay) {
                        const savingsText = totalSavingsDisplay.textContent.trim();
                        const savingsMatch = savingsText.match(/[\d,]+\.?\d*/);
                        if (savingsMatch) {
                            window.currentBaseSavings = parseFloat(savingsMatch[0].replace(/,/g,
                                ''));
                        }
                    }

                    // Uncheck wallet when coupon is applied/removed
                    const walletCheckbox = document.getElementById('use_wallet_balance');
                    if (walletCheckbox && walletCheckbox.checked) {
                        walletCheckbox.checked = false;
                        toggleWalletUsage(walletCheckbox);
                    }
                }, 150);
            });
        });
    </script>
</div>
