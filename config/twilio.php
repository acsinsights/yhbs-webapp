<?php

return [
    'account_sid' => env('TWILIO_ACCOUNT_SID'),
    'auth_token' => env('TWILIO_AUTH_TOKEN'),

    'whatsapp' => [
        'from' => env('TWILIO_WHATSAPP_FROM', 'whatsapp:+14155238886'),
        'enabled' => env('TWILIO_WHATSAPP_ENABLED', true),
    ],

    'templates' => [
        'booking_confirmation' => "🎉 *Booking Confirmed!*\n\nHi {name},\n\nYour booking #{booking_id} has been confirmed!\n\n📅 Date: {date}\n⏰ Time: {time}\n🚤 {service}: {item_name}\n💰 Amount: KWD {amount}\n\nThank you for choosing {app_name}!\n\nNeed help? Reply to this message.",

        'booking_cancellation_approved' => "✅ *Cancellation Approved*\n\nHi {name},\n\nYour cancellation request for booking #{booking_id} has been approved.\n\n💵 Refund: KWD {refund_amount}\nThe refund will be processed within 5-7 business days.\n\nWe hope to serve you again soon!",

        'booking_cancellation_rejected' => "❌ *Cancellation Request Rejected*\n\nHi {name},\n\nYour cancellation request for booking #{booking_id} has been rejected.\n\n📝 Reason: {reason}\n\nFor more details, please contact our support.",

        'booking_reschedule_approved' => "✅ *Reschedule Approved*\n\nHi {name},\n\nYour reschedule request for booking #{booking_id} has been approved!\n\n🔄 New Date: {new_date}\n⏰ New Time: {new_time}\n\nSee you then!",

        'booking_reschedule_rejected' => "❌ *Reschedule Request Rejected*\n\nHi {name},\n\nYour reschedule request for booking #{booking_id} has been rejected.\n\n📝 Reason: {reason}\n\nFor assistance, please contact support.",

        'payment_received' => "💳 *Payment Received*\n\nHi {name},\n\nWe have received your payment of KWD {amount} for booking #{booking_id}.\n\n🧾 Transaction ID: {transaction_id}\n\nThank you!",
    ],
];
