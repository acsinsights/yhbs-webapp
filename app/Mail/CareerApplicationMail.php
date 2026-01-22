<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use App\Models\CareerApplication;

class CareerApplicationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $application;

    /**
     * Create a new message instance.
     */
    public function __construct(CareerApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Career Application Received',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.career-application',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Attach resume if exists
        if ($this->application->resume && \Storage::disk('public')->exists($this->application->resume)) {
            $resumePath = storage_path('app/public/' . $this->application->resume);
            if (file_exists($resumePath)) {
                $attachments[] = Attachment::fromPath($resumePath)
                    ->as('Resume_' . str_replace(' ', '_', $this->application->name) . '.pdf')
                    ->withMime('application/pdf');
            }
        }

        return $attachments;
    }
}
