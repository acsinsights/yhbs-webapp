<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Cancellation Request Declined</title>
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
            background: #dc3545;
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
            border-left: 4px solid #dc3545;
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

        .reason-box {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
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
        <h2 style="margin: 0;">❌ Cancellation Request Declined</h2>
    </div>

    <div class="content">
        <p>Hello {{ $booking->user->name ?? 'Customer' }},</p>

        <p>We regret to inform you that your booking cancellation request has been <strong>declined</strong>.</p>

        <div class="reason-box">
            <h4 style="margin-top: 0; color: #721c24;">Reason for Decline:</h4>
            <p style="margin-bottom: 0; color: #721c24;">{{ $rejectionReason }}</p>
        </div>

        <div class="booking-details">
            <h3 style="margin-top: 0; color: #dc3545;">Booking Details</h3>

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
                <span class="label">Total Amount:</span>
                <span class="value">{{ currency_format($booking->total_amount) }}</span>
            </div>
        </div>

        <p>Your booking remains active. If you have any questions or concerns, please contact our support team.</p>

        <p style="text-align: center; margin-top: 30px;">
            <a href="{{ url('/contact') }}"
                style="display: inline-block; padding: 12px 30px; background: #136497; color: white; text-decoration: none; border-radius: 5px;">
                Contact Support
            </a>
        </p>
    </div>

    <div class="footer">
        <p>Thank you for choosing YHBS.</p>
        <p>&copy; {{ date('Y') }} YHBS. All rights reserved.</p>
    </div>
</body>

</html>
