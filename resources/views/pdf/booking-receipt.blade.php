<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt - {{ $booking->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }

        .header h1 {
            color: #667eea;
            font-size: 22px;
            margin-bottom: 3px;
        }

        .header p {
            color: #666;
            font-size: 11px;
        }

        .receipt-info {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 12px;
            border-radius: 3px;
        }

        .receipt-info h2 {
            color: #667eea;
            font-size: 13px;
            margin-bottom: 6px;
        }

        .info-grid {
            display: table;
            width: 100%;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            padding: 3px 8px 3px 0;
            font-weight: bold;
            width: 40%;
        }

        .info-value {
            display: table-cell;
            padding: 3px 0;
        }

        .section {
            margin-bottom: 15px;
            page-break-inside: avoid;
        }

        .section-title {
            background-color: #667eea;
            color: white;
            padding: 8px 10px;
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 0;
            border-radius: 3px 3px 0 0;
        }

        .section-content {
            border: 1px solid #e0e0e0;
            border-top: none;
            padding: 10px;
            border-radius: 0 0 3px 3px;
        }

        .detail-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .detail-row {
            display: table-row;
        }

        .detail-cell {
            display: table-cell;
            padding: 6px 5px;

            .detail-cell.label {
                font-weight: bold;
                width: 45%;
                color: #495057;
            }

            .detail-row:last-child .detail-cell {
                border-bottom: none;
            }

            .payment-breakdown {
                background-color: #ffffff;
                padding: 10px;
                border: 1px solid #e0e0e0;
                border-radius: 3px;
            }

            .payment-row {
                display: table;
                width: 100%;
                padding: 3px 0;
            }

            .payment-label {
                display: table-cell;
                width: 70%;
            }

            .payment-amount {
                display: table-cell;
                text-align: right;
                font-weight: bold;
            }

            .divider {
                border-top: 1px solid #ddd;
                margin: 10px 0;
            }

            .total-row {
                background-color: #667eea;
                color: white;
                padding: 10px 12px;
                margin-top: 8px;
                border-radius: 3px;
                font-size: 13px;
                font-weight: bold;
            }

            .total-row .payment-label {
                display: table-cell;
                vertical-align: middle;
            }

            .total-row .payment-amount {
                display: table-cell;
                text-align: right;
                vertical-align: middle;
                font-size: 14px;
            }

            .status-badge {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 3px;
                font-size: 9px;
                font-weight: bold;
                text-transform: uppercase;
                vertical-align: middle;
                line-height: 1.2;
            }

            .status-confirmed,
            .status-booked {
                background-color: #d4edda;
                color: #155724;
            }

            .status-checkedin {
                background-color: #d1ecf1;
                color: #0c5460;
            }

            .status-checkedout {
                background-color: #cfe2ff;
                color: #084298;
            }

            .status-pending {
                background-color: #fff3cd;
                color: #856404;
            }

            .status-paid {
                background-color: #d4edda;
                color: #155724;
            }

            .status-failed {
                background-color: #f8d7da;
                color: #721c24;
            }

            .footer {
                margin-top: 20px;
                padding-top: 12px;
                border-top: 1px solid #ddd;
                text-align: center;
                color: #666;
                font-size: 9px;
            }

            .note-box {
                background-color: #fffbcc;
                border-left: 3px solid #f0ad4e;
                padding: 8px;
                margin-top: 12px;
            }

            .note-box strong {
                color: #856404;
            }

            .logo {
                text-align: center;
                margin-bottom: 15px;
            }

            .logo img {
                max-width: 180px;
                height: auto;
            }
    </style>
</head>

<body>
    <!-- Logo -->
    <div class="logo">
        <img src="{{ public_path('frontend/img/header-logo2.svg') }}" alt="Company Logo">
    </div>

    <p>Confirmation Receipt for Your Booking</p>
    </div>

    <!-- Receipt Information -->
    <div class="receipt-info">
        <h2>Receipt Details</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Booking ID:</div>
                <div class="info-value">#{{ $booking->booking_id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Receipt Date:</div>
                <div class="info-value">{{ now()->format('F d, Y') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Booking Date:</div>
                <div class="info-value">{{ $booking->created_at->format('F d, Y h:i A') }}</div>
            </div>
        </div>
    </div>

    <!-- Customer Information -->
    <div class="section">
        <div class="section-title">Customer Information</div>
        <div class="section-content">
            <div class="detail-grid">
                <div class="detail-row">
                    <div class="detail-cell label">Full Name:</div>
                    <div class="detail-cell">{{ $booking->user->name ?? 'N/A' }}</div>
                </div>
                <div class="detail-row">
                    <div class="detail-cell label">Email:</div>
                    <div class="detail-cell">{{ $booking->user->email ?? 'N/A' }}</div>
                </div>
                @if ($booking->user && $booking->user->phone)
                    <div class="detail-row">
                        <div class="detail-cell label">Phone:</div>
                        <div class="detail-cell">{{ $booking->user->phone }}</div>
                    </div>
                @endif
                <div class="detail-row">
                    <div class="detail-cell label">Number of Guests:</div>
                    <div class="detail-cell">{{ $booking->adults ?? 0 }} Adults, {{ $booking->children ?? 0 }}
                        Children
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Property Information -->
    <div class="section">
        <div class="section-title">Property Information</div>
        <div class="section-content">
            <div class="detail-grid">
                <div class="detail-row">
                    <div class="detail-cell label">Property Name:</div>
                    <div class="detail-cell">{{ $booking->bookingable->name ?? 'N/A' }}</div>
                </div>
                @if ($booking->bookingable && $booking->bookingable->location)
                    <div class="detail-row">
                        <div class="detail-cell label">Location:</div>
                        <div class="detail-cell">{{ $booking->bookingable->location }}</div>
                    </div>
                @endif
                @if ($booking->bookingable_type === 'App\\Models\\Room' && $booking->bookingable->house)
                    <div class="detail-row">
                        <div class="detail-cell label">House:</div>
                        <div class="detail-cell">{{ $booking->bookingable->house->name }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Booking Details -->
    <div class="section">
        <div class="section-title">Booking Details</div>
        <div class="section-content">
            <div class="detail-grid">
                <div class="detail-row">
                    <div class="detail-cell label">Check-in Date:</div>
                    <div class="detail-cell">{{ $booking->check_in ? $booking->check_in->format('F d, Y') : 'N/A' }}
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-cell label">Check-out Date:</div>
                    <div class="detail-cell">{{ $booking->check_out ? $booking->check_out->format('F d, Y') : 'N/A' }}
                    </div>
                </div>
                <div class="detail-row">
                    <div class="detail-cell label">Duration:</div>
                    <div class="detail-cell">
                        @if ($booking->check_in && $booking->check_out)
                            {{ $booking->check_in->diffInDays($booking->check_out) }} Night(s)
                        @else
                            N/A
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Summary -->
    <div class="section">
        <div class="section-title">Payment Summary</div>
        <div class="payment-breakdown">
            @php
                $nights =
                    $booking->check_in && $booking->check_out ? $booking->check_in->diffInDays($booking->check_out) : 1;
                $nights = max(1, $nights);
                $originalSubtotal = $booking->price + ($booking->discount_amount ?? 0);
                $pricePerNight = $nights > 0 ? $originalSubtotal / $nights : $originalSubtotal;
                $subtotal = $pricePerNight * $nights;
            @endphp

            <div class="payment-row">
                <div class="payment-label">Price per night</div>
                <div class="payment-amount">{{ currency_format($pricePerNight) }}</div>
            </div>
            <div class="payment-row">
                <div class="payment-label">× {{ $nights }} {{ $nights > 1 ? 'nights' : 'night' }}</div>
                <div class="payment-amount">{{ currency_format($subtotal) }}</div>
            </div>

            @if ($booking->discount_amount > 0)
                <div class="divider"></div>
                <div class="payment-row" style="color: #28a745;">
                    <div class="payment-label">
                        Discount
                        @if ($booking->coupon)
                            ({{ $booking->coupon->code }})
                        @endif
                    </div>
                    <div class="payment-amount">-{{ currency_format($booking->discount_amount) }}</div>
                </div>
            @endif

            @php
                $walletTransaction = \App\Models\WalletTransaction::where('booking_id', $booking->id)
                    ->where('type', 'debit')
                    ->first();
                $walletUsed = $walletTransaction ? abs($walletTransaction->amount) : 0;
            @endphp

            @if ($walletUsed > 0)
                <div class="payment-row" style="color: #17a2b8;">
                    <div class="payment-label">Wallet Used</div>
                    <div class="payment-amount">-{{ currency_format($walletUsed) }}</div>
                </div>
            @endif
        </div>

        <div class="total-row" style="display: table; width: 100%;">
            <div class="payment-label">Total Amount Paid</div>
            <div class="payment-amount">{{ currency_format($booking->total_amount ?? $booking->price) }}</div>
        </div>

        <div
            style="margin-top: 15px; padding: 12px; background-color: #f8f9fa; border-radius: 5px; border: 1px solid #e0e0e0;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 50%; padding: 5px; vertical-align: middle;">
                        <strong style="color: #333;">Payment Method:</strong>
                        <span
                            style="margin-left: 8px; color: #555;">{{ ucfirst($booking->payment_method->value ?? 'N/A') }}</span>
                    </td>
                    <td style="width: 50%; padding: 5px; text-align: right; vertical-align: middle;">
                        <strong style="color: #333; margin-right: 8px;">Payment Status:</strong>
                        <span style="padding: 5px;"
                            class="status-{{ strtolower($booking->payment_status->value ?? 'pending') }}">
                            {{ $booking->payment_status->label() ?? 'Pending' }}
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    @if ($booking->notes)
        <div class="note-box">
            <strong>Special Requests / Notes:</strong><br>
            {{ $booking->notes }}
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Thank you for choosing our service!</p>
        <p>For any queries, please contact us at support@yhbs.com</p>
        <p style="margin-top: 10px; font-size: 10px;">This is a computer-generated receipt and does not require a
            signature.</p>
    </div>
</body>

</html>
