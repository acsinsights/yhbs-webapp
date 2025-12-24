<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Password Reset OTP - {{ config('app.name') }}</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, sans-serif !important;}
    </style>
    <![endif]-->
</head>

<body
    style="margin: 0; padding: 0; background-color: #f4f4f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
        style="background-color: #f4f4f7;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <!-- Main Container -->
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600"
                    style="max-width: 600px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); overflow: hidden;">

                    <!-- Header with Gradient -->
                    <tr>
                        <td
                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 40px 30px; text-align: center;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-bottom: 20px;">
                                        <!-- Logo Placeholder -->
                                        <div
                                            style="width: 80px; height: 80px; background-color: rgba(255, 255, 255, 0.2); border-radius: 50%; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                                            <span style="color: #ffffff; font-size: 32px; font-weight: bold;">🔐</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <h1
                                            style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700; letter-spacing: -0.5px;">
                                            Password Reset Request</h1>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content Section -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <!-- Greeting -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="padding-bottom: 20px;">
                                        <p
                                            style="margin: 0; color: #1f2937; font-size: 18px; font-weight: 600; line-height: 1.5;">
                                            Hello {{ $userName }}! 👋
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 25px;">
                                        <p style="margin: 0; color: #4b5563; font-size: 16px; line-height: 1.6;">
                                            We received a request to reset your password for your <strong
                                                style="color: #667eea;">{{ config('app.name') }}</strong> account.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 25px;">
                                        <p style="margin: 0; color: #4b5563; font-size: 16px; line-height: 1.6;">
                                            Use the OTP code below to reset your password. This code is valid for
                                            <strong>10 minutes</strong>.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- OTP Box -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td align="center" style="padding: 30px 0;">
                                        <div
                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; padding: 25px; display: inline-block;">
                                            <p
                                                style="margin: 0 0 8px 0; color: rgba(255, 255, 255, 0.9); font-size: 14px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px;">
                                                Your OTP Code</p>
                                            <p
                                                style="margin: 0; color: #ffffff; font-size: 42px; font-weight: 700; letter-spacing: 8px; font-family: 'Courier New', monospace;">
                                                {{ $otp }}</p>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Security Info -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td
                                        style="padding: 25px 20px; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px;">
                                        <p
                                            style="margin: 0 0 8px 0; color: #92400e; font-size: 14px; font-weight: 600;">
                                            ⚠️ Security Notice
                                        </p>
                                        <p style="margin: 0; color: #78350f; font-size: 14px; line-height: 1.5;">
                                            If you didn't request this password reset, please ignore this email or
                                            contact our support team immediately. Your password will remain unchanged.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Additional Info -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
                                style="margin-top: 30px;">
                                <tr>
                                    <td>
                                        <p style="margin: 0; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                            <strong>Note:</strong> This OTP will expire in 10 minutes. Don't share this
                                            code with anyone.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="background-color: #f9fafb; padding: 30px; text-align: center; border-top: 1px solid #e5e7eb;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="padding-bottom: 15px;">
                                        <p style="margin: 0; color: #6b7280; font-size: 14px; line-height: 1.6;">
                                            Need help? Contact our support team
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-bottom: 20px;">
                                        <a href="mailto:{{ config('mail.from.address') }}"
                                            style="color: #667eea; text-decoration: none; font-size: 14px; font-weight: 500;">
                                            {{ config('mail.from.address') }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <p style="margin: 0; color: #9ca3af; font-size: 12px; line-height: 1.5;">
                                            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
                <!-- End Main Container -->
            </td>
        </tr>
    </table>
</body>

</html>
