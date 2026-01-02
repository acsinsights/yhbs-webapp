<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment Successful - {{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .success-container {
            max-width: 600px;
            margin: 4rem auto;
            padding: 0 1rem;
            text-align: center;
        }

        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 3;
            stroke: #22c55e;
            stroke-miterlimit: 10;
            margin: 2rem auto;
            box-shadow: inset 0px 0px 0px #22c55e;
            animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        }

        .checkmark-circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 3;
            stroke-miterlimit: 10;
            stroke: #22c55e;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }

        .checkmark-check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }

        @keyframes stroke {
            100% {
                stroke-dashoffset: 0;
            }
        }

        @keyframes scale {

            0%,
            100% {
                transform: none;
            }

            50% {
                transform: scale3d(1.1, 1.1, 1);
            }
        }

        @keyframes fill {
            100% {
                box-shadow: inset 0px 0px 0px 40px #22c55e;
            }
        }
    </style>
</head>

<body class="bg-gray-50">
    <div class="success-container">
        <!-- Success Icon -->
        <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
            <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none" />
            <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
        </svg>

        <!-- Success Message -->
        <h1 class="text-3xl font-bold text-gray-900 mb-4">Payment Successful!</h1>
        <p class="text-lg text-gray-600 mb-8">
            Your booking has been confirmed. A confirmation email has been sent to your email address.
        </p>

        <!-- Booking Details Card -->
        <div class="bg-white rounded-lg shadow-md p-6 text-left mb-6">
            <h2 class="text-xl font-semibold mb-4 text-center">Booking Details</h2>

            <div class="space-y-3">
                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Booking ID:</span>
                    <span class="font-medium">#{{ $booking->booking_id }}</span>
                </div>

                @if (isset($referenceNumber))
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Reference Number:</span>
                        <span class="font-medium">{{ $referenceNumber }}</span>
                    </div>
                @endif

                @if (isset($orderNo))
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-600">Order Number:</span>
                        <span class="font-medium">{{ $orderNo }}</span>
                    </div>
                @endif

                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Amount Paid:</span>
                    <span class="font-bold text-green-600">{{ number_format($booking->total_amount, 3) }} KWD</span>
                </div>

                <div class="flex justify-between border-b pb-2">
                    <span class="text-gray-600">Payment Date:</span>
                    <span class="font-medium">{{ now()->format('M d, Y H:i') }}</span>
                </div>

                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                        Confirmed
                    </span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="{{ route('bookings.show', $booking->id) }}"
                class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                View Booking Details
            </a>
            <a href="{{ route('dashboard') }}"
                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                Go to Dashboard
            </a>
        </div>

        <!-- Additional Info -->
        <div class="mt-8 p-4 bg-blue-50 rounded-lg">
            <p class="text-sm text-gray-700">
                <strong>What's Next?</strong><br>
                You will receive a confirmation email shortly with all the details of your booking.
                Please keep this for your records.
            </p>
        </div>
    </div>
</body>

</html>
