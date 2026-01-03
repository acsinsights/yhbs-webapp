<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cancellation Approved</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: #28a745;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            background: #f8f9fa;
            padding: 30px;
            border: 1px solid #dee2e6;
        }

        .booking-details {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .label {
            font-weight: bold;
            color: #666;
        }

        .value {
            color: #333;
        }

        .refund-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }

        .refund-amount {
            font-size: 28px;
            font-weight: bold;
            color: #155724;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2 style="margin: 0;">✅ Cancellation Approved</h2>
    </div>

    <div class="content">
        <p>Hello {{ $booking->user->name ?? 'Customer' }},</p>

        <p>Your booking cancellation request has been <strong>approved</strong>. We're sorry to see your plans changed.
        </p>

        <div class="refund-box">
            <p style="margin: 0 0 10px 0; color: #155724;">Refund Amount</p>
            <div class="refund-amount">{{ currency_format($booking->refund_amount) }}</div>
            <p style="margin: 10px 0 0 0; font-size: 14px; color: #155724;">Has been credited to your wallet</p>
        </div>

        <div class="booking-details">
            <h3 style="margin-top: 0; color: #28a745;">Cancelled Booking Details</h3>

            <div class="detail-row">
                <span class="label">Booking ID:</span>
                <span class="value">#{{ $booking->booking_id }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Property:</span>
                <span class="value">{{ $booking->bookingable->name ?? 'N/A' }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Check-in Date:</span>
                <span class="value">{{ $booking->check_in->format('d M Y') }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Check-out Date:</span>
                <span class="value">{{ $booking->check_out->format('d M Y') }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Original Amount:</span>
                <span class="value">{{ currency_format($booking->total_amount) }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Cancelled On:</span>
                <span class="value">{{ $booking->cancelled_at->format('d M Y H:i') }}</span>
            </div>
        </div>

        <p>You can use your wallet balance for future bookings. We hope to serve you again soon!</p>

        <p style="text-align: center; margin-top: 30px;">
            <a href="{{ url('/customer/bookings') }}"
                style="display: inline-block; padding: 12px 30px; background: #136497; color: white; text-decoration: none; border-radius: 5px;">
                View My Bookings
            </a>
        </p>
    </div>

    <div class="footer">
        <p>Thank you for choosing YHBS.</p>
        <p>&copy; {{ date('Y') }} YHBS. All rights reserved.</p>
    </div>
</body>

</html>
