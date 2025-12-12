# Example Ottu Payment Integration Button
# Add this to your booking views to redirect to payment

{{-- Example 1: Simple Payment Button --}}
@if ($booking->payment_status !== \App\Enums\PaymentStatusEnum::PAID)
    <a href="{{ route('ottu.checkout', $booking->id) }}"
        class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition duration-200">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
        </svg>
        Pay Now - {{ number_format($booking->total_amount, 3) }} KWD
    </a>
@else
    <span class="inline-flex items-center px-6 py-3 bg-green-100 text-green-800 rounded-lg">
        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                clip-rule="evenodd" />
        </svg>
        Paid
    </span>
@endif

{{-- Example 2: Payment Card with Details --}}
<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold mb-4">Payment Information</h3>

    <div class="space-y-3 mb-6">
        <div class="flex justify-between">
            <span class="text-gray-600">Total Amount:</span>
            <span class="font-bold text-lg">{{ number_format($booking->total_amount, 3) }} KWD</span>
        </div>

        <div class="flex justify-between">
            <span class="text-gray-600">Payment Status:</span>
            <span
                class="px-3 py-1 rounded-full text-sm font-medium
                @if ($booking->payment_status === \App\Enums\PaymentStatusEnum::PAID) bg-green-100 text-green-800
                @elseif($booking->payment_status === \App\Enums\PaymentStatusEnum::PENDING)
                    bg-yellow-100 text-yellow-800
                @else
                    bg-red-100 text-red-800 @endif">
                {{ $booking->payment_status->value ?? 'Unpaid' }}
            </span>
        </div>

        @if ($booking->payment_reference)
            <div class="flex justify-between">
                <span class="text-gray-600">Reference:</span>
                <span class="font-mono text-sm">{{ $booking->payment_reference }}</span>
            </div>
        @endif
    </div>

    @if ($booking->payment_status !== \App\Enums\PaymentStatusEnum::PAID)
        <a href="{{ route('ottu.checkout', $booking->id) }}"
            class="block w-full text-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
            Proceed to Payment
        </a>
    @endif
</div>

{{-- Example 3: Livewire Component Usage --}}
{{-- In your Livewire component --}}
<div>
    @if ($booking->payment_status !== \App\Enums\PaymentStatusEnum::PAID)
        <button wire:click="initiatePayment" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
            Pay {{ number_format($booking->total_amount, 3) }} KWD
        </button>
    @endif
</div>

{{-- In your Livewire class --}}
{{--
public function initiatePayment()
{
    return redirect()->route('ottu.checkout', $this->booking->id);
}
--}}

{{-- Example 4: Payment Options Display --}}
<div class="bg-gray-50 rounded-lg p-6">
    <h3 class="text-lg font-semibold mb-4">We Accept</h3>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded p-3 text-center">
            <span class="text-sm">Credit Card</span>
        </div>
        <div class="bg-white rounded p-3 text-center">
            <span class="text-sm">KNET</span>
        </div>
        <div class="bg-white rounded p-3 text-center">
            <span class="text-sm">Apple Pay</span>
        </div>
        <div class="bg-white rounded p-3 text-center">
            <span class="text-sm">Google Pay</span>
        </div>
    </div>

    @if ($booking->payment_status !== \App\Enums\PaymentStatusEnum::PAID)
        <div class="mt-6">
            <a href="{{ route('ottu.checkout', $booking->id) }}"
                class="block w-full text-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg">
                Choose Payment Method
            </a>
        </div>
    @endif
</div>

{{-- Example 5: Payment History Display --}}
@if ($booking->paid_at)
    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-green-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            <div class="ml-3">
                <h4 class="text-green-900 font-semibold">Payment Confirmed</h4>
                <p class="text-green-700 text-sm mt-1">
                    Paid on {{ $booking->paid_at->format('M d, Y \a\t H:i') }}
                </p>
                @if ($booking->payment_reference)
                    <p class="text-green-600 text-xs mt-1 font-mono">
                        Ref: {{ $booking->payment_reference }}
                    </p>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- Example 6: Payment Reminder --}}
@if ($booking->payment_status === \App\Enums\PaymentStatusEnum::PENDING)
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-6 h-6 text-yellow-600 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                    clip-rule="evenodd" />
            </svg>
            <div class="ml-3">
                <h4 class="text-yellow-900 font-semibold">Payment Pending</h4>
                <p class="text-yellow-700 text-sm mt-1">
                    Complete your payment to confirm this booking.
                </p>
                <a href="{{ route('ottu.checkout', $booking->id) }}"
                    class="inline-block mt-3 text-sm text-yellow-800 underline hover:text-yellow-900">
                    Complete Payment Now →
                </a>
            </div>
        </div>
    </div>
@endif
