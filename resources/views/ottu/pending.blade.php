<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Processing - {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .pending-container {
            max-width: 600px;
            margin: 4rem auto;
            padding: 0 1rem;
            text-align: center;
        }

        .spinner {
            border: 4px solid #f3f4f6;
            border-top: 4px solid #f59e0b;
            border-radius: 50%;
            width: 60px;
            height: 60px;
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
    </style>
</head>

<body class="bg-gray-50">
    <div class="pending-container">
        <!-- Spinner -->
        <div class="spinner"></div>

        <!-- Pending Message -->
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Payment Processing</h1>
        <p class="text-lg text-gray-600 mb-8">
            {{ $message ?? 'Your payment is being processed. Please wait...' }}
        </p>

        <!-- Booking Details Card -->
        <div class="bg-white rounded-lg shadow-md p-6 text-left mb-6">
            <h2 class="text-xl font-semibold mb-4 text-center">Booking Information</h2>

            <div class="space-y-3">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Booking ID:</span>
                    <span class="font-medium">#{{ $booking->id }}</span>
                </div>

                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Amount:</span>
                    <span class="font-bold">{{ number_format($booking->total_amount, 3) }} KWD</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">
                        Processing
                    </span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('bookings.show', $booking->id) }}"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                View Booking
            </a>
            <a href="{{ route('dashboard') }}"
                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                Go to Dashboard
            </a>
        </div>

        <!-- Additional Info -->
        <div class="mt-8 p-4 bg-yellow-50 rounded-lg">
            <p class="text-sm text-gray-700">
                <strong>Please Note:</strong><br>
                Payment verification may take a few minutes. You will receive an email confirmation
                once your payment has been processed successfully.
            </p>
        </div>
    </div>
</body>

</html>
