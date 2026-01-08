<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

class BookingStatusNotification extends Notification
{
    use Queueable;

    public $booking;
    public $type;
    public $data;

    /**
     * Create a new notification instance.
     *
     * @param Booking $booking
     * @param string $type - cancellation_request, cancellation_approved, cancellation_rejected, reschedule_request, reschedule_approved, reschedule_rejected
     * @param array $data - Additional data like refund_amount, rejection_reason, etc.
     */
    public function __construct(Booking $booking, string $type, array $data = [])
    {
        $this->booking = $booking;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $bookingType = match ($this->booking->bookingable_type) {
            'App\Models\Room' => 'Room',
            'App\Models\House' => 'House',
            'App\Models\Boat' => 'Boat',
            default => 'Property',
        };

        return match ($this->type) {
            // Cancellation notifications
            'cancellation_request' => [
                'booking_id' => $this->booking->id,
                'booking_number' => $this->booking->booking_id,
                'booking_type' => $bookingType,
                'customer_name' => $this->booking->user->name ?? 'Guest',
                'total_amount' => $this->booking->total_amount,
                'cancellation_reason' => $this->booking->cancellation_reason,
                'message' => "Cancellation request for {$bookingType} booking from {$this->booking->user->name}",
                'icon' => 'o-x-circle',
                'url' => route('admin.cancellation-requests'),
            ],

            'cancellation_approved' => [
                'booking_id' => $this->booking->id,
                'booking_number' => $this->booking->booking_id,
                'booking_type' => $bookingType,
                'refund_amount' => $this->data['refund_amount'] ?? 0,
                'message' => $this->getCancellationApprovedMessage($bookingType),
                'icon' => 'o-check-circle',
                'url' => route('customer.booking.details', $this->booking->id),
            ],

            'cancellation_rejected' => [
                'booking_id' => $this->booking->id,
                'booking_number' => $this->booking->booking_id,
                'booking_type' => $bookingType,
                'rejection_reason' => $this->data['rejection_reason'] ?? '',
                'message' => "Your cancellation request for {$bookingType} booking #{$this->booking->booking_id} has been declined.",
                'icon' => 'o-x-circle',
                'url' => route('customer.booking.details', $this->booking->id),
            ],

            // Reschedule notifications
            'reschedule_request' => [
                'booking_id' => $this->booking->id,
                'booking_number' => $this->booking->booking_id,
                'booking_type' => $bookingType,
                'customer_name' => $this->booking->user->name ?? 'Guest',
                'new_check_in' => $this->booking->new_check_in?->format('d M Y'),
                'new_check_out' => $this->booking->new_check_out?->format('d M Y'),
                'reschedule_reason' => $this->booking->reschedule_reason,
                'message' => "Reschedule request for {$bookingType} booking from {$this->booking->user->name}",
                'icon' => 'o-calendar',
                'url' => route('admin.reschedule-requests'),
            ],

            'reschedule_approved' => [
                'booking_id' => $this->booking->id,
                'booking_number' => $this->booking->booking_id,
                'booking_type' => $bookingType,
                'new_check_in' => $this->booking->check_in?->format('d M Y'),
                'new_check_out' => $this->booking->check_out?->format('d M Y'),
                'message' => "Your reschedule request for {$bookingType} booking #{$this->booking->booking_id} has been approved.",
                'icon' => 'o-check-circle',
                'url' => route('customer.booking.details', $this->booking->id),
            ],

            'reschedule_rejected' => [
                'booking_id' => $this->booking->id,
                'booking_number' => $this->booking->booking_id,
                'booking_type' => $bookingType,
                'rejection_reason' => $this->data['rejection_reason'] ?? '',
                'message' => "Your reschedule request for {$bookingType} booking #{$this->booking->booking_id} has been declined.",
                'icon' => 'o-x-circle',
                'url' => route('customer.booking.details', $this->booking->id),
            ],

            default => [
                'message' => 'Booking status updated',
                'icon' => 'o-bell',
            ],
        };
    }

    private function getCancellationApprovedMessage(string $bookingType): string
    {
        $message = "Your cancellation request for {$bookingType} booking #{$this->booking->booking_id} has been approved.";

        $refundAmount = $this->data['refund_amount'] ?? 0;
        if ($refundAmount > 0) {
            $message .= " Refund of " . currency_format($refundAmount) . " has been credited to your wallet.";
        }

        return $message;
    }
}
