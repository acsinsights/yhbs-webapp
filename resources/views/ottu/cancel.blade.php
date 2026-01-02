<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Cancelled - {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .cancel-container {
            max-width: 600px;
            margin: 4rem auto;
            padding: 0 1rem;
            text-align: center;
        }

        .error-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            margin: 2rem auto;
            background-color: #fee2e2;
            position: relative;
        }

        .error-icon::before,
        .error-icon::after {
            content: '';
            position: absolute;
            background-color: #ef4444;
            width: 4px;
            height: 40px;
            top: 50%;
            left: 50%;
            border-radius: 2px;
        }

        .error-icon::before {
            transform: translate(-50%, -50%) rotate(45deg);
        }

        .error-icon::after {
            transform: translate(-50%, -50%) rotate(-45deg);
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="cancel-container">
        <!-- Error Icon -->
        <div class="error-icon"></div>

        <!-- Cancel Message -->
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Payment Cancelled</h1>
        <p class="text-lg text-gray-600 mb-8">
            {{ $message ?? 'Your payment was cancelled or failed. No charges have been made.' }}
        </p>

        <!-- Booking Details Card -->
        <div class="bg-white rounded-lg shadow-md p-6 text-left mb-6">
            <h2 class="text-xl font-semibold mb-4 text-center">Booking Information</h2>

            <div class="space-y-3">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Booking ID:</span>
                    <span class="font-medium">#{{ $booking->booking_id }}</span>
                </div>

                <div class="flex justify-between border-b pb-2">
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

                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Amount:</span>
                    <span class="font-bold">{{ number_format($booking->total_amount, 3) }} KWD</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                        Payment Pending
                    </span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('ottu.checkout', $booking->id) }}"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Try Payment Again
            </a>
            <a href="{{ route('bookings.show', $booking->id) }}"
                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                View Booking
            </a>
        </div>

        <!-- Help Section -->
        <div class="mt-8 p-4 bg-yellow-50 rounded-lg text-left">
            <h3 class="font-semibold text-gray-900 mb-2">Need Help?</h3>
            <p class="text-sm text-gray-700 mb-2">
                If you're experiencing issues with payment, please try:
            </p>
            <ul class="text-sm text-gray-700 list-disc list-inside space-y-1">
                <li>Checking your card details and available balance</li>
                <li>Using a different payment method</li>
                <li>Contacting your bank if the payment was declined</li>
                <li>Reaching out to our support team for assistance</li>
            </ul>
        </div>

        <!-- Contact Support -->
        <div class="mt-6">
            <a href="mailto:support@example.com" class="text-blue-600 hover:underline">
                Contact Support
            </a>
        </div>
    </div>
</body>

</html>
