<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Customer\AuthController;
use App\Http\Controllers\Customer\DashboardController;
use App\Http\Controllers\Frontend\RoomController;
use App\Http\Controllers\Frontend\YachtController;

// Frontend Home Page
Route::get('/', function () {
    return view('frontend.home');
})->name('home');

// Customer Routes
Route::prefix('customer')->name('customer.')->group(function () {
    // Guest routes (Login, Register, Forgot Password)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.submit');

        Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('forgot-password');
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('forgot-password.submit');
    });

    // Authenticated customer routes
    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [DashboardController::class, 'updatePassword'])->name('password.update');

        Route::get('/bookings', [DashboardController::class, 'bookings'])->name('bookings');
        Route::get('/bookings/{id}', [DashboardController::class, 'bookingDetails'])->name('booking.details');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');
    });
});

// Booking Routes (accessible to guests and authenticated users)
Route::get('/checkout', function () {
    $booking = (object) [
        'property_image' => null,
        'property_name' => 'Sample Property',
        'location' => 'Sample Location',
        'check_in' => date('Y-m-d'),
        'check_out' => date('Y-m-d', strtotime('+3 days')),
        'nights' => 3,
        'guests' => 2,
        'price_per_night' => 150,
        'service_fee' => 15,
        'tax' => 20,
        'total' => 485,
    ];

    return view('frontend.checkout', compact('booking'));
})->name('checkout');

Route::post('/booking/confirm', function () {
    // Handle booking confirmation logic here
    return redirect()->route('booking.confirmation', ['id' => 1]);
})->name('booking.confirm');

Route::get('/booking/confirmation/{id}', function ($id) {
    // Fetch booking details
    $booking = (object) [
        'id' => $id,
        'reference' => 'YHBS' . str_pad($id, 4, '0', STR_PAD_LEFT),
        'property_image' => null,
        'property_name' => 'Luxury Room',
        'location' => 'Sample Location',
        'check_in' => date('Y-m-d'),
        'check_out' => date('Y-m-d', strtotime('+3 days')),
        'nights' => 3,
        'guests' => 2,
        'customer_name' => auth()->user()->name ?? 'Guest',
        'customer_email' => auth()->user()->email ?? 'guest@example.com',
        'customer_phone' => 'N/A',
        'payment_method' => 'card',
        'price_per_night' => 150,
        'service_fee' => 15,
        'tax' => 20,
        'total' => 485,
    ];

    return view('frontend.booking-confirmation', compact('booking'));
})->name('booking.confirmation');

// Rooms Routes
Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
Route::get('/rooms/{id}', [RoomController::class, 'show'])->name('rooms.show');

// Yachts Routes
Route::get('/yachts', [YachtController::class, 'index'])->name('yachts.index');
Route::get('/yachts/{id}', [YachtController::class, 'show'])->name('yachts.show');
