<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Booking;

class BookingRescheduleRequestNotification extends Notification
{
    use Queueable;

    public $booking;

    /**
     * Create a new notification instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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

        return [
            'booking_id' => $this->booking->id,
            'booking_number' => $this->booking->booking_id,
            'booking_type' => $bookingType,
            'customer_name' => $this->booking->user->name ?? 'Guest',
            'total_amount' => $this->booking->total_amount,
            'reschedule_reason' => $this->booking->reschedule_reason,
            'new_check_in' => $this->booking->new_check_in?->format('M d, Y'),
            'new_check_out' => $this->booking->new_check_out?->format('M d, Y'),
            'message' => "Reschedule request for {$bookingType} booking from {$this->booking->user->name}",
            'icon' => 'o-arrow-path',
            'url' => route('admin.reschedule-requests'),
        ];
    }
}
