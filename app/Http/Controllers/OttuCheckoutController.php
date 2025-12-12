<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\OttuService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class OttuCheckoutController extends Controller
{
    protected OttuService $ottuService;

    public function __construct(OttuService $ottuService)
    {
        $this->ottuService = $ottuService;
    }

    /**
     * Display the checkout page
     *
     * @param Request $request
     * @param int $bookingId
     * @return View|RedirectResponse
     */
    public function checkout(Request $request, int $bookingId): View|RedirectResponse
    {
        $booking = Booking::with(['user', 'yacht', 'house', 'room'])->findOrFail($bookingId);

        // Check if booking belongs to authenticated user or is authorized
        if ($booking->user_id !== auth()->id() && !auth()->user()->hasRole('admin')) {
            abort(403, 'Unauthorized access to this booking');
        }

        // Check if booking is already paid
        if ($booking->payment_status === \App\Enums\PaymentStatusEnum::PAID) {
            return redirect()->route('bookings.show', $bookingId)
                ->with('info', 'This booking has already been paid.');
        }

        // Create payment session with Ottu
        $paymentData = [
            'amount' => $booking->total_amount,
            'currency_code' => config('services.ottu.currency', 'KWD'),
            'customer_id' => $booking->user->id,
            'customer_email' => $booking->user->email,
            'customer_phone' => $booking->user->phone ?? null,
            'customer_first_name' => $booking->user->name ?? 'Customer',
            'customer_last_name' => '',
            'order_no' => "BOOKING-{$booking->id}",
            'reference_number' => "REF-{$booking->id}-" . time(),
            'webhook_url' => route('ottu.webhook'),
            'redirect_url' => route('ottu.success', ['bookingId' => $booking->id]),
            'pg_codes' => $request->input('pg_codes', ['credit-card']), // Default payment method
            'language' => app()->getLocale(),
            'extra' => [
                'booking_id' => $booking->id,
                'booking_type' => $booking->yacht_id ? 'yacht' : ($booking->house_id ? 'house' : 'room'),
            ],
        ];

        $result = $this->ottuService->createPaymentSession($paymentData);

        if (!$result['success']) {
            return redirect()->back()
                ->with('error', $result['error'] ?? 'Failed to initialize payment. Please try again.');
        }

        $sessionData = $result['data'];

        // Store session_id in booking for reference
        $booking->update([
            'payment_session_id' => $sessionData['session_id'] ?? null,
        ]);

        return view('ottu.checkout', [
            'booking' => $booking,
            'sessionId' => $sessionData['session_id'],
            'merchantId' => config('services.ottu.merchant_id'),
            'apiKey' => config('services.ottu.api_key'),
            'sdkUrl' => config('services.ottu.sdk_url'),
            'setupPreload' => $sessionData['sdk_setup_preload_payload'] ?? null,
        ]);
    }

    /**
     * Handle successful payment callback
     *
     * @param Request $request
     * @param int $bookingId
     * @return View|RedirectResponse
     */
    public function success(Request $request, int $bookingId): View|RedirectResponse
    {
        $booking = Booking::findOrFail($bookingId);

        $sessionId = $request->query('session_id');
        $orderNo = $request->query('order_no');
        $referenceNumber = $request->query('reference_number');

        // Verify payment status with Ottu
        if ($sessionId) {
            $result = $this->ottuService->getPaymentSession($sessionId);

            if ($result['success']) {
                $paymentData = $result['data'];

                // Check payment state
                if (in_array($paymentData['state'], ['paid', 'authorized'])) {
                    // Update booking payment status
                    $booking->update([
                        'payment_status' => \App\Enums\PaymentStatusEnum::PAID,
                        'booking_status' => \App\Enums\BookingStatusEnum::CONFIRMED,
                        'payment_reference' => $referenceNumber,
                        'paid_at' => now(),
                    ]);

                    return view('ottu.success', [
                        'booking' => $booking,
                        'orderNo' => $orderNo,
                        'referenceNumber' => $referenceNumber,
                    ]);
                }
            }
        }

        // If we couldn't verify payment, show pending status
        return view('ottu.pending', [
            'booking' => $booking,
            'message' => 'Payment is being processed. You will receive a confirmation shortly.',
        ]);
    }

    /**
     * Handle payment cancellation or error
     *
     * @param Request $request
     * @param int $bookingId
     * @return View
     */
    public function cancel(Request $request, int $bookingId): View
    {
        $booking = Booking::findOrFail($bookingId);

        return view('ottu.cancel', [
            'booking' => $booking,
            'message' => $request->query('message', 'Payment was cancelled or failed. Please try again.'),
        ]);
    }

    /**
     * Handle Ottu webhook notifications
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function webhook(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            // Get raw payload for signature verification
            $payload = $request->getContent();
            $signature = $request->header('X-Ottu-Signature');

            // Verify webhook signature
            if ($signature && !$this->ottuService->verifyWebhookSignature($payload, $signature)) {
                Log::warning('Invalid Ottu webhook signature');
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $data = $request->all();

            Log::info('Ottu webhook received', ['data' => $data]);

            // Extract booking ID from order_no or extra data
            $orderNo = $data['order_no'] ?? null;
            $bookingId = null;

            if ($orderNo && str_starts_with($orderNo, 'BOOKING-')) {
                $bookingId = (int) str_replace('BOOKING-', '', $orderNo);
            } elseif (isset($data['extra']['booking_id'])) {
                $bookingId = $data['extra']['booking_id'];
            }

            if (!$bookingId) {
                Log::error('Booking ID not found in webhook data', ['data' => $data]);
                return response()->json(['error' => 'Booking ID not found'], 400);
            }

            $booking = Booking::find($bookingId);

            if (!$booking) {
                Log::error('Booking not found', ['booking_id' => $bookingId]);
                return response()->json(['error' => 'Booking not found'], 404);
            }

            // Update booking based on payment state
            $state = $data['state'] ?? null;

            switch ($state) {
                case 'paid':
                case 'authorized':
                    $booking->update([
                        'payment_status' => \App\Enums\PaymentStatusEnum::PAID,
                        'booking_status' => \App\Enums\BookingStatusEnum::CONFIRMED,
                        'payment_reference' => $data['reference_number'] ?? null,
                        'paid_at' => now(),
                    ]);
                    break;

                case 'failed':
                case 'error':
                    $booking->update([
                        'payment_status' => \App\Enums\PaymentStatusEnum::FAILED,
                    ]);
                    break;

                case 'canceled':
                    $booking->update([
                        'payment_status' => \App\Enums\PaymentStatusEnum::CANCELLED,
                    ]);
                    break;

                case 'pending':
                    $booking->update([
                        'payment_status' => \App\Enums\PaymentStatusEnum::PENDING,
                    ]);
                    break;
            }

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('Ottu webhook processing error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Get available payment methods
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentMethods(): \Illuminate\Http\JsonResponse
    {
        $result = $this->ottuService->getSupportedPaymentGateways();

        if ($result['success']) {
            return response()->json($result['data']);
        }

        return response()->json(['error' => $result['error']], 500);
    }
}
