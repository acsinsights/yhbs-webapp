<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Frontend\RoomController;
use App\Http\Controllers\Frontend\HouseController;
use App\Http\Controllers\Frontend\YachtController;
use App\Http\Controllers\Frontend\BookingController;

// Frontend Home Page
Route::get('/', function () {
    $houses = \App\Models\House::with('rooms')->active()->take(3)->get();
    return view('frontend.home', compact('houses'));
})->name('home');

// about page
Route::get('/about', function () {
    return view('frontend.about');
})->name('about');

// contact page
Route::get('/contact', function () {
    return view('frontend.contact');
})->name('contact');

// contact form submission
Route::post('/contact', [App\Http\Controllers\Frontend\ContactController::class, 'store'])->name('contact.store');

// job application page
Route::get('/job-application', function () {
    return view('frontend.job-application');
})->name('job-application');

// privacy policy page
Route::get('/privacy-policy', function () {
    return view('frontend.privacy-policy');
})->name('privacy-policy');

// terms and condition page
Route::get('/terms-condition', function () {
    return view('frontend.terms-condition');
})->name('terms-condition');

// Customer Routes
Route::prefix('customer')->name('customer.')->group(function () {
    // Guest routes (Login, Register, Forgot Password)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

        Route::get('/verify-registration-otp', [AuthController::class, 'showVerifyRegistrationOtp'])->name('verify-registration-otp');
        Route::post('/verify-registration-otp', [AuthController::class, 'verifyRegistrationOtp'])->name('verify-registration-otp.submit');
        Route::post('/resend-registration-otp', [AuthController::class, 'resendRegistrationOtp'])->name('resend-registration-otp');

        Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password.submit');

        Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
    });

    // Authenticated customer routes
    Route::middleware(['auth', 'role:customer'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [DashboardController::class, 'updatePassword'])->name('password.update');

        Route::get('/bookings', [DashboardController::class, 'bookings'])->name('bookings');
        Route::get('/bookings/{id}', [DashboardController::class, 'bookingDetails'])->name('booking.details');
        Route::post('/bookings/{id}/cancel', [DashboardController::class, 'cancelBooking'])->name('booking.cancel');
        Route::get('/bookings/{id}/download-receipt', [DashboardController::class, 'downloadReceipt'])->name('booking.download-receipt');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');
});

// Booking Routes (require authentication)
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/checkout', [BookingController::class, 'checkout'])->name('checkout');
    Route::post('/booking/confirm', [BookingController::class, 'confirm'])->name('booking.confirm');
    Route::get('/booking/confirmation/{id}', [BookingController::class, 'confirmation'])->name('booking.confirmation');

    // Frontend Coupon Routes
    Route::post('/booking/apply-coupon', [BookingController::class, 'applyCoupon'])->name('booking.apply-coupon');
    Route::post('/booking/remove-coupon', [BookingController::class, 'removeCoupon'])->name('booking.remove-coupon');
});

// Rooms Routes
Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/rooms/{slug}', [RoomController::class, 'show'])->name('rooms.show');

// Houses Routes
Route::get('/houses', [HouseController::class, 'index'])->name('houses.index');
Route::get('/houses/{slug}', [HouseController::class, 'show'])->name('houses.show');

// Yachts Routes
Route::get('/yachts', [YachtController::class, 'index'])->name('yachts.index');
Route::get('/yachts/{id}', [YachtController::class, 'show'])->name('yachts.show');

// Blog Routes
Route::get('/blogs', [App\Http\Controllers\Frontend\BlogController::class, 'index'])->name('blogs.index');
Route::get('/blog/{slug}', [App\Http\Controllers\Frontend\BlogController::class, 'show'])->name('blogs.show');

// Password Reset Routes (handled by Customer AuthController)
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
