<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Cancellation Request</title>
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
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background: #136497;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
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
        <h2 style="margin: 0;">🚨 Booking Cancellation Request</h2>
    </div>

    <div class="content">
        <p>Hello Admin,</p>

        <p>A customer has submitted a cancellation request for their booking. Please review the details below:</p>

        <div class="booking-details">
            <h3 style="margin-top: 0; color: #dc3545;">Booking Information</h3>

            <div class="detail-row">
                <span class="label">Booking ID:</span>
                <span class="value">#{{ $booking->id }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Customer Name:</span>
                <span class="value">{{ $booking->user->name ?? 'N/A' }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Customer Email:</span>
                <span class="value">{{ $booking->user->email ?? 'N/A' }}</span>
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
                <span class="value">{{ currency_format(number_format($booking->total_amount, 2)) }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Request Date:</span>
                <span class="value">{{ $booking->cancellation_requested_at->format('d M Y H:i') }}</span>
            </div>
        </div>

        <div class="reason-box">
            <h4 style="margin-top: 0;">Cancellation Reason:</h4>
            <p style="margin-bottom: 0;">{{ $booking->cancellation_reason }}</p>
        </div>

        <p style="text-align: center; margin-top: 30px;">
            <a href="{{ route('admin.cancellation-requests') }}" class="button">
                Review & Process Request
            </a>
        </p>

        <p style="margin-top: 30px; font-size: 14px; color: #666;">
            <strong>Note:</strong> Please review this request as soon as possible and communicate the decision to the
            customer.
        </p>
    </div>

    <div class="footer">
        <p>This is an automated notification from YHBS Admin Panel.</p>
        <p>&copy; {{ date('Y') }} YHBS. All rights reserved.</p>
    </div>
</body>

</html>
