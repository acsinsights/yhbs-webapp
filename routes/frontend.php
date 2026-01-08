<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\RoomController;
use App\Http\Controllers\Frontend\HouseController;
use App\Http\Controllers\Frontend\BoatController;
use App\Http\Controllers\Frontend\BookingController;

// Frontend Home Page
Route::get('/', [PageController::class, 'home'])->name('home');

// About page
Route::get('/about', [PageController::class, 'about'])->name('about');

// Contact page
Route::get('/contact', [PageController::class, 'contact'])->name('contact');

// Contact form submission
Route::post('/contact', [App\Http\Controllers\Frontend\ContactController::class, 'store'])->name('contact.store');

// Job application page
Route::get('/job-application', [PageController::class, 'jobApplication'])->name('job-application');

// Dynamic policy pages route
Route::get('/policy/{slug}', [PageController::class, 'policyPage'])->name('policy-pages.show');

// Customer Routes
Route::prefix('customer')->name('customer.')->group(function () {
    // Email check endpoint (accessible to everyone)
    Route::post('/check-email', [AuthController::class, 'checkEmail'])->name('check-email');

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
        Route::put('/password', [DashboardController::class, 'updatePassword'])->name('password.change');

        Route::get('/bookings', [DashboardController::class, 'bookings'])->name('bookings');
        Route::get('/bookings/{id}', [DashboardController::class, 'bookingDetails'])->name('booking.details');
        Route::post('/bookings/{id}/cancel', [DashboardController::class, 'cancelBooking'])->name('booking.cancel');
        Route::get('/bookings/{id}/download-receipt', [DashboardController::class, 'downloadReceipt'])->name('booking.download-receipt');

        // Wallet Transactions
        Volt::route('/wallet/transactions', 'frontend.wallet-transactions')->name('wallet.transactions');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');
});

// Guest Email Entry Page (for unauthenticated users going to checkout)
Route::get('/guest-email', [BookingController::class, 'guestEmail'])->name('guest.email');

// Checkout Route (accessible to both guest and authenticated users)
Route::get('/checkout', [BookingController::class, 'checkout'])->name('checkout');

// Booking Routes (require authentication)
Route::middleware(['auth', 'role:customer'])->group(function () {
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

// Boats Routes
Route::get('/boats', [BoatController::class, 'index'])->name('boats.index');
Route::get('/boats/{slug}', [BoatController::class, 'show'])->name('boats.show');
Route::post('/boats/available-time-slots', [BoatController::class, 'getAvailableTimeSlots'])->name('boats.timeslots');

// Blog Routes
Route::get('/blogs', [App\Http\Controllers\Frontend\BlogController::class, 'index'])->name('blogs.index');
Route::get('/blog/{slug}', [App\Http\Controllers\Frontend\BlogController::class, 'show'])->name('blogs.show');

// Policy Pages Routes
Route::get('/policy/{slug}', [App\Http\Controllers\Frontend\PolicyPageController::class, 'show'])->name('policy.show');
