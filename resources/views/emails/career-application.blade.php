<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Career Application</title>
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
            background: #136497;
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }

        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }

        .header p {
            margin: 10px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }

        .content {
            background: #f8f9fa;
            padding: 30px;
        }

        .section {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .section h2 {
            color: #2c3e50;
            font-size: 18px;
            margin: 0 0 20px 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #136497;
        }

        .info-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #ecf0f1;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #555;
            width: 140px;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }

        .info-value {
            color: #333;
            flex: 1;
        }

        .message-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #136497;
            margin-top: 10px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #7f8c8d;
            font-size: 12px;
        }

        a {
            color: #136497;
            text-decoration: none;
        }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            background: #136497;
            color: white;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>New Career Application</h1>
        <p>Someone has applied for a position at your company</p>
    </div>

    <div class="content">
        <div class="section">
            <h2>Applicant Information</h2>
            <div class="info-row">
                <div class="info-label">NAME:</div>
                <div class="info-value">{{ $application->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">EMAIL:</div>
                <div class="info-value"><a href="mailto:{{ $application->email }}">{{ $application->email }}</a></div>
            </div>
            <div class="info-row">
                <div class="info-label">PHONE:</div>
                <div class="info-value"><a href="tel:{{ $application->phone }}">{{ $application->phone }}</a></div>
            </div>
        </div>

        <div class="section">
            <h2>Position Details</h2>
            <div class="info-row">
                <div class="info-label">POSITION:</div>
                <div class="info-value"><span class="badge">{{ $application->position }}</span></div>
            </div>
            <div class="info-row">
                <div class="info-label">EXPERIENCE:</div>
                <div class="info-value">{{ $application->experience ?? 'Not specified' }}</div>
            </div>
        </div>

        @if ($application->cover_letter)
            <div class="section">
                <h2>Cover Letter</h2>
                <div class="message-box">{{ $application->cover_letter }}</div>
            </div>
        @endif

        <div class="section">
            <h2>Additional Information</h2>
            <div class="info-row">
                <div class="info-label">APPLIED ON:</div>
                <div class="info-value">{{ $application->created_at->format('F d, Y \a\t g:i A') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">RESUME:</div>
                <div class="info-value">
                    @if ($application->resume)
                        ✓ Attached ({{ basename($application->resume) }})
                    @else
                        Not uploaded
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
        <p>This is an automated message. Please do not reply to this email.</p>
    </div>
</body>

</html>
