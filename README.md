<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

# Ottu Payment Gateway Integration

This document provides comprehensive instructions for integrating the Ottu Checkout SDK (Version 3) into your Laravel application.

## Table of Contents

1. [Installation](#installation)
2. [Configuration](#configuration)
3. [Database Migration](#database-migration)
4. [Usage](#usage)
5. [Architecture](#architecture)
6. [Testing](#testing)
7. [Webhook Configuration](#webhook-configuration)
8. [Security](#security)
9. [Troubleshooting](#troubleshooting)

## Installation

### 1. Environment Configuration

Update your `.env` file with Ottu credentials:

```env
OTTU_MERCHANT_ID=your-merchant-domain.ottu.net
OTTU_API_KEY=your-public-api-key
OTTU_API_URL=https://sandbox.ottu.net
OTTU_WEBHOOK_SECRET=your-webhook-secret
OTTU_CURRENCY=KWD
```

**Important Notes:**
- For production, change `OTTU_API_URL` to your production URL
- Use the **PUBLIC API KEY** only, never expose your private key
- Generate a strong webhook secret for security

### 2. Run Database Migration

Add payment tracking fields to the bookings table:

```bash
php artisan migrate
```

This adds the following fields:
- `payment_session_id` - Stores Ottu session ID
- `payment_reference` - Stores payment reference number
- `paid_at` - Timestamp of successful payment

## Configuration

### Services Configuration

The Ottu configuration is located in `config/services.php`:

```php
'ottu' => [
    'merchant_id' => env('OTTU_MERCHANT_ID'),
    'api_key' => env('OTTU_API_KEY'),
    'api_url' => env('OTTU_API_URL', 'https://sandbox.ottu.net'),
    'webhook_secret' => env('OTTU_WEBHOOK_SECRET'),
    'currency' => env('OTTU_CURRENCY', 'KWD'),
    'sdk_url' => 'https://assets.ottu.net/checkout/v3/checkout.min.js',
],
```

## Architecture

### Components

#### 1. **OttuService** (`app/Services/OttuService.php`)

Handles all Ottu API interactions:

- `createPaymentSession()` - Creates a new payment session
- `getPaymentSession()` - Retrieves payment session details
- `verifyWebhookSignature()` - Validates webhook authenticity
- `getSupportedPaymentGateways()` - Lists available payment methods
- `cancelPayment()` - Cancels a payment transaction

#### 2. **OttuCheckoutController** (`app/Http/Controllers/OttuCheckoutController.php`)

Manages the checkout flow:

- `checkout()` - Displays payment page
- `success()` - Handles successful payments
- `cancel()` - Handles cancelled/failed payments
- `webhook()` - Processes Ottu webhooks
- `paymentMethods()` - Returns available payment methods

#### 3. **ValidateCheckoutAccess** (`app/Http/Middleware/ValidateCheckoutAccess.php`)

Middleware that:
- Validates booking ownership
- Prevents payment for already paid bookings
- Blocks cancelled bookings
- Ensures user authorization

#### 4. **Blade Views**

- `resources/views/ottu/checkout.blade.php` - Payment page with SDK
- `resources/views/ottu/success.blade.php` - Success confirmation
- `resources/views/ottu/cancel.blade.php` - Cancellation/error page
- `resources/views/ottu/pending.blade.php` - Processing status

## Usage

### Basic Payment Flow

#### 1. Redirect to Checkout

From your booking confirmation page:

```blade
<a href="{{ route('ottu.checkout', $booking->id) }}" 
   class="btn btn-primary">
    Proceed to Payment
</a>
```

#### 2. Programmatic Payment Initiation

```php
use App\Services\OttuService;

$ottuService = new OttuService();

$result = $ottuService->createPaymentSession([
    'amount' => 100.500,
    'currency_code' => 'KWD',
    'customer_email' => 'customer@example.com',
    'customer_phone' => '+96512345678',
    'order_no' => 'ORDER-123',
    'pg_codes' => ['credit-card', 'kpay'],
]);

if ($result['success']) {
    $sessionId = $result['data']['session_id'];
    // Redirect to checkout page with session
}
```

### Customizing Payment Methods

In the controller, modify the `pg_codes`:

```php
'pg_codes' => [
    'credit-card',  // Credit/Debit cards
    'kpay',         // KNET
    'stc-pay',      // STC Pay
    'apple-pay',    // Apple Pay
    'google-pay',   // Google Pay
],
```

### Theme Customization

Modify the `theme` object in `checkout.blade.php`:

```javascript
theme: {
    "pay-button": {
        "background": "#3b82f6",
        "color": "white",
        "border-radius": "8px"
    },
    "amount-box": {
        "background": "#eff6ff",
        "border": "2px solid #3b82f6"
    }
}
```

## Webhook Configuration

### 1. Configure Webhook URL in Ottu Dashboard

Set your webhook URL:
```
https://yourdomain.com/payment/webhook
```

### 2. Webhook Payload Example

```json
{
    "session_id": "abc123...",
    "state": "paid",
    "amount": "100.500",
    "currency_code": "KWD",
    "order_no": "BOOKING-123",
    "reference_number": "REF-123-1234567890",
    "customer_email": "customer@example.com",
    "payment_gateway_info": {
        "pg_name": "mpgs",
        "pg_code": "credit-card"
    }
}
```

### 3. Webhook States

- `paid` - Payment successful
- `authorized` - Payment authorized (capture required)
- `failed` - Payment failed
- `canceled` - Payment cancelled by user
- `pending` - Payment processing

## Security

### Best Practices

1. **Never expose private API keys** - Only use public keys in frontend
2. **Verify webhook signatures** - Always validate incoming webhooks
3. **Use HTTPS** - Ensure all communication is encrypted
4. **Validate payment amounts** - Double-check amounts before processing
5. **Log all transactions** - Keep audit trail of payment activities

### Webhook Signature Verification

The webhook handler automatically verifies signatures:

```php
$signature = $request->header('X-Ottu-Signature');
if (!$this->ottuService->verifyWebhookSignature($payload, $signature)) {
    return response()->json(['error' => 'Invalid signature'], 401);
}
```

## Testing

### Test Cards (Sandbox Environment)

| Card Number | Result |
|------------|--------|
| 5123450000000008 | Success |
| 5555550000000001 | Declined |

### Testing Webhooks

Use tools like [Webhook.site](https://webhook.site) to test webhook delivery:

1. Create a webhook.site URL
2. Update `webhook_url` in payment session
3. Complete test payment
4. Verify webhook payload

### Local Testing with ngrok

```bash
# Start ngrok
ngrok http 8000

# Update webhook URL in Ottu dashboard to ngrok URL
https://your-ngrok-url.ngrok.io/payment/webhook
```

## Routes

All payment routes are defined in `routes/web.php`:

| Route | Method | Purpose |
|-------|--------|---------|
| `/payment/checkout/{bookingId}` | GET | Display checkout page |
| `/payment/success/{bookingId}` | GET | Success callback |
| `/payment/cancel/{bookingId}` | GET | Cancel/error callback |
| `/payment/webhook` | POST | Webhook handler |
| `/payment/payment-methods` | GET | List payment methods |

## Callback Functions

### JavaScript Callbacks

#### successCallback
```javascript
window.successCallback = function(data) {
    // Redirect to success page
    window.location.href = data.redirect_url;
};
```

#### errorCallback
```javascript
window.errorCallback = function(data) {
    // Show error message
    Checkout.showPopup("error", data.message);
};
```

#### cancelCallback
```javascript
window.cancelCallback = function(data) {
    // Handle cancellation
    console.log('Payment cancelled:', data);
};
```

#### beforePayment Hook
```javascript
window.beforePayment = function(data) {
    return new Promise(function(resolve, reject) {
        // Perform pre-payment validations
        // Freeze cart, validate inventory, etc.
        resolve(true);
    });
};
```

#### validatePayment Hook
```javascript
window.validatePayment = function() {
    return new Promise(function(resolve, reject) {
        if (!termsAccepted) {
            reject(new Error("Terms not accepted"));
            return;
        }
        resolve(true);
    });
};
```

## Troubleshooting

### Common Issues

#### 1. Payment SDK Not Loading

**Problem:** Checkout page shows spinner indefinitely

**Solutions:**
- Check OTTU_MERCHANT_ID is correct
- Verify OTTU_API_KEY is valid (use public key)
- Check browser console for JavaScript errors
- Ensure SDK URL is accessible

#### 2. Webhook Not Receiving

**Problem:** Payments succeed but booking status not updated

**Solutions:**
- Verify webhook URL is publicly accessible
- Check webhook signature secret matches
- Review Laravel logs: `storage/logs/laravel.log`
- Test webhook URL with Postman/Insomnia

#### 3. Payment Button Disabled

**Problem:** Unable to click Pay button

**Solutions:**
- Check if terms checkbox is implemented in validatePayment
- Verify payment amount is greater than zero
- Check for JavaScript errors
- Ensure payment gateway is configured in Ottu

#### 4. Apple Pay/Google Pay Not Showing

**Problem:** Wallet buttons not visible

**Solutions:**
- Verify device supports Apple Pay/Google Pay
- Check merchant configuration in Ottu dashboard
- Ensure SSL certificate is valid
- Verify domain is registered with Apple/Google

### Debug Mode

Enable detailed logging:

```php
// In OttuService.php, add to constructor:
Log::channel('daily')->info('Ottu Service Initialized', [
    'merchant_id' => $this->merchantId,
    'api_url' => $this->apiUrl,
]);
```

### Logs Location

- Laravel logs: `storage/logs/laravel.log`
- Payment transactions: Check Ottu dashboard
- Webhook logs: Ottu dashboard > Webhooks section

## Support

### Resources

- [Ottu Official Documentation](https://docs.ottu.com)
- [Checkout SDK Web v3 Docs](https://docs.ottu.com/developer/checkout-sdk/web)
- [Checkout API Documentation](https://docs.ottu.com/developer/checkout-api)

### Contact

For issues with integration:
1. Check logs and error messages
2. Review this documentation
3. Contact Ottu support at support@ottu.com
4. Reference your merchant ID when seeking help

## Additional Features

### Multiple Payment Gateways

Configure multiple payment options:

```php
'pg_codes' => [
    'credit-card',
    'kpay',
    'stc-pay',
    'urpay',
],
```

### Payment Gateway Fees

Display fees before payment:

```php
$result = $ottuService->createPaymentSession([
    // ... other data
    'include_fee' => true,
]);
```

### Recurring Payments

For subscription-based services:

```php
$result = $ottuService->createPaymentSession([
    'type' => 'payment_request',
    'amount' => 50.000,
    'recurring' => true,
    'recurring_interval' => 'monthly',
]);
```

## Changelog

### Version 1.0 (December 2025)
- Initial Ottu SDK v3 integration
- Support for Apple Pay, Google Pay, STC Pay, urPay
- Webhook handler implementation
- Theme customization support
- Security middleware
- Comprehensive documentation

---

**Last Updated:** December 12, 2025  
**Version:** 1.0  
**Ottu SDK Version:** 3.x
# yhbs-webapp
