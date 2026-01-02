<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment Checkout - {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Ottu Checkout SDK -->
    <script src="{{ $sdkUrl }}" data-error="errorCallback" data-cancel="cancelCallback" data-success="successCallback"
        data-beforepayment="beforePayment" data-validatepayment="validatePayment"></script>

    <style>
        .checkout-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .booking-summary {
            background: #f9fafb;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-item:last-child {
            border-bottom: none;
            font-weight: bold;
            font-size: 1.25rem;
        }

        #checkout {
            min-height: 400px;
        }

        .terms-checkbox {
            margin: 1.5rem 0;
            padding: 1rem;
            background: #fffbeb;
            border-radius: 0.5rem;
        }

        .spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 2rem auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        #coupon-code {
            text-transform: uppercase;
        }

        .coupon-success {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }

        .coupon-error {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="checkout-container">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Complete Your Payment</h1>
            <p class="text-gray-600">Secure payment powered by Ottu</p>
        </div>

        <!-- Booking Summary -->
        <div class="booking-summary">
            <h2 class="text-xl font-semibold mb-4">Booking Summary</h2>

            <div class="summary-item">
                <span class="text-gray-600">Booking ID:</span>
                <span class="font-medium">#{{ $booking->booking_id }}</span>
            </div>

            <div class="summary-item">
                <span class="text-gray-600">
                    @if ($booking->yacht)
                        Yacht:
                    @elseif($booking->house)
                        House:
                    @else
                        Room:
                    @endif
                </span>
                <span class="font-medium">
                    {{ $booking->yacht->name ?? ($booking->house->name ?? ($booking->room->name ?? 'N/A')) }}
                </span>
            </div>

            <div class="summary-item">
                <span class="text-gray-600">Check-in:</span>
                <span class="font-medium">{{ $booking->check_in_date->format('M d, Y') }}</span>
            </div>

            <div class="summary-item">
                <span class="text-gray-600">Check-out:</span>
                <span class="font-medium">{{ $booking->check_out_date->format('M d, Y') }}</span>
            </div>

            <div class="summary-item">
                <span class="text-gray-600">Subtotal:</span>
                <span class="font-medium">{{ number_format($booking->price, 3) }} KWD</span>
            </div>

            @if ($booking->discount_amount > 0)
                <div class="summary-item text-green-600">
                    <span>Discount ({{ $booking->coupon->code }}):</span>
                    <span>-{{ number_format($booking->discount_amount, 3) }} KWD</span>
                </div>
            @endif

            <div class="summary-item">
                <span class="text-gray-600">Total Amount:</span>
                <span class="text-blue-600 font-bold">{{ number_format($booking->total_amount, 3) }} KWD</span>
            </div>
        </div>

        <!-- Coupon Section -->
        <div class="bg-white rounded-lg shadow-sm p-6 mb-6" id="coupon-section">
            <h3 class="text-lg font-semibold mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z">
                    </path>
                </svg>
                Have a Coupon Code?
            </h3>

            @if ($booking->discount_amount > 0)
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <p class="font-semibold text-green-800">Coupon Applied Successfully!</p>
                                <p class="text-sm text-green-600">Code: <strong>{{ $booking->coupon->code }}</strong>
                                </p>
                                <p class="text-xs text-green-500 mt-1">{{ $booking->coupon->name }}</p>
                            </div>
                        </div>
                        <button onclick="removeCoupon()" class="text-red-500 hover:text-red-700 text-sm underline">
                            Remove
                        </button>
                    </div>
                </div>
            @else
                <form id="coupon-form" class="flex gap-3" onsubmit="applyCoupon(event)">
                    <div class="flex-1">
                        <input type="text" id="coupon-code" placeholder="Enter coupon code"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent uppercase"
                            style="text-transform: uppercase;">
                    </div>
                    <button type="submit" id="apply-coupon-btn"
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 shadow-sm hover:shadow-md">
                        Apply
                    </button>
                </form>
                <div id="coupon-message" class="mt-3 text-sm"></div>
            @endif
        </div>

        <!-- Terms and Conditions -->
        <div class="terms-checkbox">
            <label class="flex items-start cursor-pointer">
                <input type="checkbox" id="termsCheckbox" class="mt-1 mr-3 h-5 w-5 text-blue-600 rounded">
                <span class="text-sm text-gray-700">
                    I agree to the <a href="#" class="text-blue-600 underline">terms and conditions</a>
                    and understand the <a href="#" class="text-blue-600 underline">cancellation policy</a>
                </span>
            </label>
        </div>

        <!-- Ottu Checkout SDK Container -->
        <div id="checkout" class="bg-white rounded-lg shadow-sm p-6">
            <div class="spinner"></div>
            <p class="text-center text-gray-600 mt-4">Loading payment methods...</p>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 text-center text-sm text-gray-600">
            <svg class="inline-block w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                    clip-rule="evenodd" />
            </svg>
            Your payment information is encrypted and secure
        </div>
    </div>

    <script>
        // CSRF Token for AJAX requests
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        // Apply Coupon Function
        function applyCoupon(event) {
            event.preventDefault();

            const couponCode = document.getElementById('coupon-code').value.trim().toUpperCase();
            const messageDiv = document.getElementById('coupon-message');
            const applyBtn = document.getElementById('apply-coupon-btn');

            if (!couponCode) {
                showMessage('Please enter a coupon code', 'error');
                return;
            }

            // Disable button and show loading
            applyBtn.disabled = true;
            applyBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> Applying...';

            // Send AJAX request to validate and apply coupon
            fetch('{{ route('checkout.apply-coupon') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        booking_id: {{ $booking->id }},
                        coupon_code: couponCode
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Reload page to show updated prices
                        window.location.reload();
                    } else {
                        showMessage(data.message, 'error');
                        applyBtn.disabled = false;
                        applyBtn.innerHTML = 'Apply';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showMessage('Something went wrong. Please try again.', 'error');
                    applyBtn.disabled = false;
                    applyBtn.innerHTML = 'Apply';
                });
        }

        // Remove Coupon Function
        function removeCoupon() {
            if (!confirm('Are you sure you want to remove this coupon?')) {
                return;
            }

            fetch('{{ route('checkout.remove-coupon') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        booking_id: {{ $booking->id }}
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message || 'Failed to remove coupon');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Something went wrong. Please try again.');
                });
        }

        // Show Message Helper
        function showMessage(message, type) {
            const messageDiv = document.getElementById('coupon-message');
            messageDiv.innerHTML = `
                <div class="p-3 rounded-lg ${type === 'error' ? 'coupon-error text-red-800' : 'coupon-success text-green-800'}">
                    <p class="flex items-center">
                        ${type === 'error' ?
                            '<svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>' :
                            '<svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>'
                        }
                        ${message}
                    </p>
                </div>
            `;
        }

        // Callback Functions
        window.errorCallback = function(data) {
            console.error('Payment error:', data);

            const validFormsOfPayments = ['token_pay', 'redirect'];
            if (validFormsOfPayments.includes(data.form_of_payment) || data.challenge_occurred) {
                const message = data.message || "Oops, something went wrong. Please refresh and try again.";
                window.Checkout.showPopup("error", message);
            }

            // Optionally redirect to error page
            // window.location.href = "{{ route('ottu.cancel', $booking->id) }}?message=" + encodeURIComponent(data.message);
        };

        window.successCallback = function(data) {
            console.log('Payment success:', data);

            if (data.redirect_url) {
                window.location.href = data.redirect_url;
            } else {
                window.location.href = "{{ route('ottu.success', $booking->id) }}";
            }
        };

        window.cancelCallback = function(data) {
            console.log('Payment cancelled:', data);

            if (data.payment_gateway_info && data.payment_gateway_info.pg_name === "kpay") {
                window.Checkout.showPopup("error", '', data.payment_gateway_info.pg_response);
            } else if (data.form_of_payment === "token_pay" || data.challenge_occurred) {
                const message = data.message || "Payment was cancelled. Please try again.";
                window.Checkout.showPopup("error", message);
            }
        };

        window.beforePayment = function(data) {
            return new Promise(function(resolve, reject) {
                // You can add any pre-payment validation or API calls here
                // For example, freeze the basket, validate inventory, etc.

                if (data && data.redirect_url) {
                    window.Checkout.showPopup(
                        'redirect',
                        data.message || 'Redirecting to the payment page',
                        null
                    );
                }

                resolve(true);
            });
        };

        window.validatePayment = function() {
            return new Promise(function(resolve, reject) {
                const termsAccepted = document.getElementById("termsCheckbox").checked;

                if (!termsAccepted) {
                    alert("Please accept the terms and conditions before proceeding.");
                    reject(new Error("Terms not accepted"));
                    return;
                }

                resolve(true);
            });
        };

        // Initialize Ottu Checkout SDK
        document.addEventListener('DOMContentLoaded', function() {
            try {
                Checkout.init({
                    selector: "checkout",
                    merchant_id: "{{ $merchantId }}",
                    session_id: "{{ $sessionId }}",
                    apiKey: "{{ $apiKey }}",
                    lang: "{{ app()->getLocale() }}",
                    @if ($setupPreload)
                        setupPreload: {!! json_encode($setupPreload) !!},
                    @endif
                    formsOfPayment: [
                        'applePay',
                        'googlePay',
                        'tokenPay',
                        'ottuPG',
                        'redirect',
                        'stcPay',
                        'urPay'
                    ],
                    displayMode: 'column',
                    theme: {
                        "pay-button": {
                            "background": "#3b82f6",
                            "color": "white"
                        },
                        "amount-box": {
                            "background": "#eff6ff"
                        },
                        "selected-method": {
                            "border": "2px solid #3b82f6"
                        }
                    }
                });
            } catch (error) {
                console.error('Failed to initialize Ottu SDK:', error);
                document.getElementById('checkout').innerHTML =
                    '<div class="text-center text-red-600"><p>Failed to load payment methods. Please refresh the page.</p></div>';
            }
        });
    </script>
</body>

</html>
