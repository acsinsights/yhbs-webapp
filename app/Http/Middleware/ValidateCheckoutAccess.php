<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Booking;

class ValidateCheckoutAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bookingId = $request->route('bookingId');

        if (!$bookingId) {
            return redirect()->route('dashboard')
                ->with('error', 'Invalid booking reference.');
        }

        $booking = Booking::find($bookingId);

        if (!$booking) {
            return redirect()->route('dashboard')
                ->with('error', 'Booking not found.');
        }

        // Check if user has access to this booking
        $user = auth()->user();

        if ($booking->user_id !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Unauthorized access to this booking.');
        }

        // Check if booking is already paid
        if ($booking->payment_status === \App\Enums\PaymentStatusEnum::PAID) {
            return redirect()->route('bookings.show', $bookingId)
                ->with('info', 'This booking has already been paid.');
        }

        // Check if booking is cancelled
        if ($booking->booking_status === \App\Enums\BookingStatusEnum::CANCELLED) {
            return redirect()->route('bookings.show', $bookingId)
                ->with('error', 'This booking has been cancelled.');
        }

        // Add booking to request for use in controller
        $request->merge(['booking' => $booking]);

        return $next($request);
    }
}
