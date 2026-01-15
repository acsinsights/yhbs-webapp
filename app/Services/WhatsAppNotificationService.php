<?php

namespace App\Services;

use Twilio\Rest\Client;
use Exception;
use Illuminate\Support\Facades\Log;

class WhatsAppNotificationService
{
    protected $twilio;
    protected $from;
    protected $enabled;

    public function __construct()
    {
        $accountSid = config('twilio.account_sid');
        $authToken = config('twilio.auth_token');
        $this->from = config('twilio.whatsapp.from');
        $this->enabled = config('twilio.whatsapp.enabled', true);

        if ($accountSid && $authToken) {
            $this->twilio = new Client($accountSid, $authToken);
        }
    }

    /**
     * Send WhatsApp message (Sandbox - Plain text)
     * For Production: Use sendWithTemplate() instead
     */
    public function send(string $to, string $message)
    {
        if (!$this->enabled) {
            Log::info('WhatsApp notifications are disabled');
            return false;
        }

        if (!$this->twilio) {
            Log::error('Twilio client not initialized. Check credentials.');
            return false;
        }

        try {
            $to = $this->formatPhoneNumber($to);

            $message = $this->twilio->messages->create(
                $to,
                [
                    'from' => $this->from,
                    'body' => $message
                ]
            );

            Log::info('WhatsApp message sent successfully', [
                'to' => $to,
                'sid' => $message->sid
            ]);

            return $message->sid;
        } catch (Exception $e) {
            Log::error('Failed to send WhatsApp message', [
                'to' => $to,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send WhatsApp message using approved template (Production)
     * Used when WhatsApp Business API is enabled
     */
    public function sendWithTemplate(string $to, string $templateSid, array $variables = [])
    {
        if (!$this->enabled) {
            Log::info('WhatsApp notifications are disabled');
            return false;
        }

        if (!$this->twilio) {
            Log::error('Twilio client not initialized. Check credentials.');
            return false;
        }

        try {
            $to = $this->formatPhoneNumber($to);

            $message = $this->twilio->messages->create(
                $to,
                [
                    'from' => $this->from,
                    'contentSid' => $templateSid,
                    'contentVariables' => json_encode($variables)
                ]
            );

            Log::info('WhatsApp template message sent successfully', [
                'to' => $to,
                'template_sid' => $templateSid,
                'sid' => $message->sid
            ]);

            return $message->sid;
        } catch (Exception $e) {
            Log::error('Failed to send WhatsApp template message', [
                'to' => $to,
                'template_sid' => $templateSid,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendBookingConfirmation($booking, $user)
    {
        $template = config('twilio.templates.booking_confirmation');

        $message = $this->replaceTemplateVariables($template, [
            'name' => $user->first_name ?? $user->name,
            'booking_id' => $booking->booking_id,
            'date' => $booking->check_in ? \Carbon\Carbon::parse($booking->check_in)->format('d M Y') : 'N/A',
            'time' => $booking->arrival_time ?? 'N/A',
            'service' => ucfirst($booking->bookingable_type),
            'item_name' => $booking->bookingable->name ?? 'N/A',
            'amount' => number_format($booking->total_amount, 2),
            'app_name' => config('app.name'),
        ]);

        return $this->send($user->phone, $message);
    }

    public function sendCancellationApproved($booking, $user)
    {
        $template = config('twilio.templates.booking_cancellation_approved');

        $message = $this->replaceTemplateVariables($template, [
            'name' => $user->first_name ?? $user->name,
            'booking_id' => $booking->booking_id,
            'refund_amount' => number_format($booking->refund_amount, 2),
        ]);

        return $this->send($user->phone, $message);
    }

    public function sendCancellationRejected($booking, $user, $reason = '')
    {
        $template = config('twilio.templates.booking_cancellation_rejected');

        $message = $this->replaceTemplateVariables($template, [
            'name' => $user->first_name ?? $user->name,
            'booking_id' => $booking->booking_id,
            'reason' => $reason ?: 'Policy restrictions',
        ]);

        return $this->send($user->phone, $message);
    }

    public function sendRescheduleApproved($booking, $user)
    {
        $template = config('twilio.templates.booking_reschedule_approved');

        $message = $this->replaceTemplateVariables($template, [
            'name' => $user->first_name ?? $user->name,
            'booking_id' => $booking->booking_id,
            'new_date' => $booking->check_in ? \Carbon\Carbon::parse($booking->check_in)->format('d M Y') : 'N/A',
            'new_time' => $booking->arrival_time ?? 'N/A',
        ]);

        return $this->send($user->phone, $message);
    }

    public function sendRescheduleRejected($booking, $user, $reason = '')
    {
        $template = config('twilio.templates.booking_reschedule_rejected');

        $message = $this->replaceTemplateVariables($template, [
            'name' => $user->first_name ?? $user->name,
            'booking_id' => $booking->booking_id,
            'reason' => $reason ?: 'Not available',
        ]);

        return $this->send($user->phone, $message);
    }

    public function sendPaymentReceived($booking, $user, $transactionId = '')
    {
        $template = config('twilio.templates.payment_received');

        $message = $this->replaceTemplateVariables($template, [
            'name' => $user->first_name ?? $user->name,
            'amount' => number_format($booking->total_amount, 2),
            'booking_id' => $booking->booking_id,
            'transaction_id' => $transactionId ?: 'N/A',
        ]);

        return $this->send($user->phone, $message);
    }

    protected function formatPhoneNumber(string $phone): string
    {
        $phone = str_replace('whatsapp:', '', $phone);
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (!str_starts_with($phone, '+')) {
            if (strlen($phone) >= 10) {
                $phone = '+' . $phone;
            }
        }

        return 'whatsapp:' . $phone;
    }

    protected function replaceTemplateVariables(string $template, array $variables): string
    {
        foreach ($variables as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }

    public function isEnabled(): bool
    {
        return $this->enabled && $this->twilio !== null;
    }
}
