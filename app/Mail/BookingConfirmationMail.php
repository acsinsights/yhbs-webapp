<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $propertyName;
    public $propertyType;
    public $recipientName;
    public $recipientType; // 'guest' or 'customer'

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, string $propertyName, string $propertyType, string $recipientName, string $recipientType = 'guest')
    {
        $this->booking = $booking;
        $this->propertyName = $propertyName;
        $this->propertyType = $propertyType;
        $this->recipientName = $recipientName;
        $this->recipientType = $recipientType;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmation - ' . $this->propertyName,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmation',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
