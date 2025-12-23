<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Barryvdh\DomPDF\Facade\Pdf;

class BookingController extends Controller
{
    /**
     * Download booking receipt as PDF
     */
    public function downloadReceipt($booking)
    {
        $booking = Booking::with(['bookingable', 'user', 'coupon'])->findOrFail($booking);

        // Check if booking has been checked in
        if (!$booking->isCheckedIn() && !$booking->isCheckedOut()) {
            abort(403, 'Receipt is only available after check-in.');
        }

        // Load house relationship if it's a room booking
        if ($booking->bookingable instanceof \App\Models\Room) {
            $booking->bookingable->load('house');
        }

        // Generate PDF
        $pdf = Pdf::loadView('pdf.booking-receipt', compact('booking'));

        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');

        // Download the PDF with a specific filename
        $filename = 'booking-receipt-' . $booking->id . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
