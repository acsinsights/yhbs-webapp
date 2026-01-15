<?php

namespace App\Helpers;

use App\Services\WhatsAppNotificationService;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class WhatsAppHelper
{
    public static function sendBookingConfirmation(Booking $booking, User $user)
    {
        try {
            if ($user->phone) {
                $service = app(WhatsAppNotificationService::class);
                return $service->sendBookingConfirmation($booking, $user);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp booking confirmation failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
        return false;
    }

    public static function sendCancellationApproved(Booking $booking, User $user)
    {
        try {
            if ($user->phone) {
                $service = app(WhatsAppNotificationService::class);
                return $service->sendCancellationApproved($booking, $user);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp cancellation approved failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
        return false;
    }

    public static function sendCancellationRejected(Booking $booking, User $user, string $reason = '')
    {
        try {
            if ($user->phone) {
                $service = app(WhatsAppNotificationService::class);
                return $service->sendCancellationRejected($booking, $user, $reason);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp cancellation rejected failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
        return false;
    }

    public static function sendRescheduleApproved(Booking $booking, User $user)
    {
        try {
            if ($user->phone) {
                $service = app(WhatsAppNotificationService::class);
                return $service->sendRescheduleApproved($booking, $user);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp reschedule approved failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
        return false;
    }

    public static function sendRescheduleRejected(Booking $booking, User $user, string $reason = '')
    {
        try {
            if ($user->phone) {
                $service = app(WhatsAppNotificationService::class);
                return $service->sendRescheduleRejected($booking, $user, $reason);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp reschedule rejected failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
        return false;
    }

    public static function sendPaymentReceived(Booking $booking, User $user, string $transactionId = '')
    {
        try {
            if ($user->phone) {
                $service = app(WhatsAppNotificationService::class);
                return $service->sendPaymentReceived($booking, $user, $transactionId);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp payment received failed', ['booking_id' => $booking->id, 'error' => $e->getMessage()]);
        }
        return false;
    }

    public static function send(string $phone, string $message)
    {
        try {
            $service = app(WhatsAppNotificationService::class);
            return $service->send($phone, $message);
        } catch (\Exception $e) {
            Log::error('WhatsApp send failed', ['phone' => $phone, 'error' => $e->getMessage()]);
        }
        return false;
    }
}
