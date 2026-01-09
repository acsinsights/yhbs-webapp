<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .header {
            background: linear-gradient(135deg, #136497 0%, #0d4d75 100%);
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
        }

        .content {
            padding: 30px;
        }

        .greeting {
            font-size: 18px;
            margin-bottom: 20px;
            color: #136497;
        }

        .booking-info {
            background-color: #f8f9fa;
            border-left: 4px solid #136497;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }

        .booking-info h2 {
            margin-top: 0;
            color: #136497;
            font-size: 20px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: bold;
            color: #666;
        }

        .info-value {
            color: #333;
            text-align: right;
        }

        .total-amount {
            background-color: #136497;
            color: #ffffff;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
            font-size: 20px;
            font-weight: bold;
        }

        .guest-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }

        .guest-section h3 {
            color: #136497;
            margin-top: 0;
        }

        .guest-item {
            padding: 10px;
            margin: 10px 0;
            background-color: #ffffff;
            border-radius: 5px;
            border-left: 3px solid #136497;
        }

        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .button {
            display: inline-block;
            padding: 12px 30px;
            background-color: #136497;
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
            font-weight: bold;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }

        .status-booked {
            background-color: #28a745;
            color: #ffffff;
        }

        @media only screen and (max-width: 600px) {
            .info-row {
                flex-direction: column;
            }

            .info-value {
                text-align: left;
                margin-top: 5px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Booking Confirmed!</h1>
            <p style="margin: 10px 0 0 0;">Thank you for choosing YHBS</p>
        </div>

        <div class="content">
            <p class="greeting">Dear {{ $recipientName }},</p>

            @if ($recipientType === 'guest')
                <p>Your booking has been confirmed! We're excited to host you.</p>
            @else
                <p>This is a confirmation of the booking made through your account.</p>
            @endif

            <div class="booking-info">
                <h2>Booking Details</h2>
                <div class="info-row">
                    <span class="info-label">Booking ID:</span>
                    <span class="info-value"><strong>#{{ $booking->booking_id }}</strong></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Property:</span>
                    <span class="info-value">{{ $propertyName }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Type:</span>
                    <span class="info-value">{{ $propertyType }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">
                        @if ($propertyType === 'Boat')
                            Booking Date:
                        @else
                            Check-in:
                        @endif
                    </span>
                    <span class="info-value">{{ \Carbon\Carbon::parse($booking->check_in)->format('d M Y') }}</span>
                </div>
                @if ($propertyType === 'Boat' && $booking->start_time)
                    <div class="info-row">
                        <span class="info-label">Time Slot:</span>
                        <span class="info-value">
                            @php
                                $startTime = $booking->start_time;
                                $duration = 0;

                                if ($booking->service_type === 'hourly' && isset($booking->duration)) {
                                    $duration = (float) $booking->duration;
                                } elseif (
                                    $booking->service_type === 'experience' &&
                                    isset($booking->experience_duration)
                                ) {
                                    $duration =
                                        $booking->experience_duration === 'full'
                                            ? 1
                                            : (float) $booking->experience_duration / 60;
                                } else {
                                    $duration = 1;
                                }

                                try {
                                    $start = \Carbon\Carbon::createFromFormat('H:i', $startTime);
                                    $end = $start->copy()->addHours($duration);
                                    echo $start->format('h:i A') . ' - ' . $end->format('h:i A');
                                } catch (\Exception $e) {
                                    echo $startTime;
                                }
                            @endphp
                        </span>
                    </div>
                @endif
                @if ($propertyType !== 'Boat')
                    <div class="info-row">
                        <span class="info-label">Check-out:</span>
                        <span
                            class="info-value">{{ \Carbon\Carbon::parse($booking->check_out)->format('d M Y') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Nights:</span>
                        <span class="info-value">{{ $booking->nights }}</span>
                    </div>
                @endif
                <div class="info-row">
                    <span class="info-label">Guests:</span>
                    <span class="info-value">{{ $booking->adults }} Adults
                        @if ($booking->children > 0)
                            , {{ $booking->children }} Children
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value">
                        <span class="status-badge status-booked">{{ $booking->status?->label() ?? 'Confirmed' }}</span>
                    </span>
                </div>
            </div>

            @php
                $guests = $booking->guest_details['guests'] ?? [];
            @endphp

            @if (count($guests) > 0)
                <div class="guest-section">
                    <h3>Guest Information</h3>
                    @foreach ($guests as $index => $guest)
                        <div class="guest-item">
                            <strong>Guest {{ $index + 1 }}:</strong><br>
                            <strong>Name:</strong> {{ $guest['name'] ?? 'N/A' }}<br>
                            <strong>Email:</strong> {{ $guest['email'] ?? 'N/A' }}<br>
                            <strong>Phone:</strong> {{ $guest['phone'] ?? 'N/A' }}
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="total-amount">
                Total Amount: {{ currency_format($booking->total_amount) }}
            </div>

            @if ($booking->notes)
                <div class="booking-info">
                    <h2>Special Requests</h2>
                    <p style="margin: 0;">{{ $booking->notes }}</p>
                </div>
            @endif

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ route('booking.confirmation', ['id' => $booking->id]) }}" class="button">
                    View Booking Details
                </a>
            </div>

            <p style="margin-top: 30px; color: #666;">
                If you have any questions or need to make changes to your booking, please contact us at
                <a href="mailto:{{ config('mail.from.address') }}"
                    style="color: #136497;">{{ config('mail.from.address') }}</a>
            </p>
        </div>

        <div class="footer">
            <p style="margin: 0;">© {{ date('Y') }} YHBS. All rights reserved.</p>
            <p style="margin: 5px 0 0 0;">This is an automated email, please do not reply directly to this message.</p>
        </div>
    </div>
</body>

</html>
