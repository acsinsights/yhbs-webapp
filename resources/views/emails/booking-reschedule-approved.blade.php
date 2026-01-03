<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reschedule Approved</title>
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

        .fee-box {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
            text-align: center;
        }

        .fee-amount {
            font-size: 24px;
            font-weight: bold;
            color: #856404;
        }

        .date-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
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

        .highlight {
            background: #fff3cd;
            padding: 2px 5px;
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="header">
        <h2 style="margin: 0;">✅ Reschedule Request Approved</h2>
    </div>

    <div class="content">
        <p>Hello {{ $booking->user->name ?? 'Customer' }},</p>

        <p>Great news! Your booking reschedule request has been <strong>approved</strong>. Your booking dates have been
            updated.</p>

        <div class="date-box">
            <h3 style="margin-top: 0; color: #155724;">📆 Your New Booking Dates:</h3>
            <div style="margin: 10px 0;">
                <strong>Check-in:</strong> <span class="highlight">{{ $booking->check_in->format('d M Y') }}</span>
            </div>
            <div style="margin: 10px 0;">
                <strong>Check-out:</strong> <span class="highlight">{{ $booking->check_out->format('d M Y') }}</span>
            </div>
        </div>

        @if ($booking->reschedule_fee > 0)
            <div class="fee-box">
                <p style="margin: 0 0 10px 0; color: #856404;">Reschedule Fee Charged</p>
                <div class="fee-amount">{{ currency_format($booking->reschedule_fee) }}</div>
                <p style="margin: 10px 0 0 0; font-size: 14px; color: #856404;">Has been deducted from your wallet</p>
            </div>
        @endif

        @if ($booking->extra_fee > 0)
            <div class="fee-box" style="background: #ffeeba; border-color: #ffcc00;">
                <p style="margin: 0 0 10px 0; color: #856404;">Additional Fee</p>
                <div class="fee-amount">{{ currency_format($booking->extra_fee) }}</div>
                @if ($booking->extra_fee_remark)
                    <p style="margin: 10px 0 0 0; font-size: 14px; color: #856404;">{{ $booking->extra_fee_remark }}</p>
                @endif
            </div>
        @endif

        <div class="booking-details">
            <h3 style="margin-top: 0; color: #28a745;">Updated Booking Information</h3>

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
                <span class="value">{{ currency_format($booking->total_amount ?: $booking->price ?: 0) }}</span>
            </div>

            @if ($booking->reschedule_fee > 0)
                <div class="detail-row">
                    <span class="label">Reschedule Fee:</span>
                    <span class="value">{{ currency_format($booking->reschedule_fee) }}</span>
                </div>
            @endif

            @if ($booking->extra_fee > 0)
                <div class="detail-row">
                    <span class="label">Extra Fee:</span>
                    <span class="value">{{ currency_format($booking->extra_fee) }}
                        @if ($booking->extra_fee_remark)
                            <br><small style="color: #666;">({{ $booking->extra_fee_remark }})</small>
                        @endif
                    </span>
                </div>
            @endif
        </div>

        <p style="margin-top: 30px;">
            We're looking forward to hosting you on your new dates! If you have any questions, please don't hesitate to
            contact us.
        </p>

        <p style="margin-top: 20px; font-size: 14px; color: #666;">
            <strong>Note:</strong> Please make note of your new check-in and check-out dates.
        </p>
    </div>

    <div class="footer">
        <p>Thank you for choosing YHBS.</p>
        <p>&copy; {{ date('Y') }} YHBS. All rights reserved.</p>
    </div>
</body>

</html>
