<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Reschedule Request</title>
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
            background: #ffc107;
            color: #000;
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
            border-left: 4px solid #ffc107;
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

        .date-change {
            background: #e7f3ff;
            border: 1px solid #007bff;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
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

        .highlight {
            background: #fff3cd;
            padding: 2px 5px;
            border-radius: 3px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2 style="margin: 0;">📅 Booking Reschedule Request</h2>
    </div>

    <div class="content">
        <p>Hello Admin,</p>

        <p>A customer has submitted a reschedule request for their booking. Please review the details below:</p>

        <div class="booking-details">
            <h3 style="margin-top: 0; color: #ffc107;">Booking Information</h3>

            <div class="detail-row">
                <span class="label">Booking ID:</span>
                <span class="value">#{{ $booking->booking_id }}</span>
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
                <span class="label">Customer Wallet Balance:</span>
                <span class="value">{{ currency_format($booking->user->wallet_balance ?? 0) }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Property:</span>
                <span class="value">{{ $booking->bookingable->name ?? 'N/A' }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Property Type:</span>
                <span class="value">{{ class_basename($booking->bookingable_type) }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Total Amount:</span>
                <span class="value">{{ currency_format($booking->total_amount ?: $booking->price ?: 0) }}</span>
            </div>

            <div class="detail-row">
                <span class="label">Request Date:</span>
                <span class="value">{{ $booking->reschedule_requested_at->format('d M Y H:i') }}</span>
            </div>
        </div>

        <div class="date-change">
            <h4 style="margin-top: 0; color: #007bff;">📆 Date Changes Requested:</h4>

            <div style="margin: 15px 0;">
                <strong>Current Dates:</strong><br>
                Check-in: {{ $booking->check_in->format('d M Y') }}<br>
                Check-out: {{ $booking->check_out->format('d M Y') }}
            </div>

            <div style="margin: 15px 0;">
                <strong style="color: #28a745;">New Dates:</strong><br>
                Check-in: <span class="highlight">{{ $booking->new_check_in?->format('d M Y') ?? 'N/A' }}</span><br>
                Check-out: <span class="highlight">{{ $booking->new_check_out?->format('d M Y') ?? 'N/A' }}</span>
            </div>

            <div style="margin: 15px 0;">
                <strong>Reschedule Fee:</strong> <span
                    class="highlight">{{ currency_format($booking->reschedule_fee ?? 0) }}</span>
            </div>
        </div>

        <div class="reason-box">
            <h4 style="margin-top: 0;">Reschedule Reason:</h4>
            <p style="margin-bottom: 0;">{{ $booking->reschedule_reason }}</p>
        </div>

        <p style="text-align: center; margin-top: 30px;">
            <a href="{{ route('admin.reschedule-requests') }}" class="button">
                Review & Process Request
            </a>
        </p>

        <p style="margin-top: 30px; font-size: 14px; color: #666;">
            <strong>Note:</strong> Please verify that the customer has sufficient wallet balance to pay the reschedule
            fee before approving.
        </p>
    </div>

    <div class="footer">
        <p>This is an automated notification from YHBS Admin Panel.</p>
        <p>&copy; {{ date('Y') }} YHBS. All rights reserved.</p>
    </div>
</body>

</html>
