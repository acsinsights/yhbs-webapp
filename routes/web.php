<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Frontend Home Page
Route::get('/', function () {
    return view('frontend.home');
})->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Volt::route('/login', 'login')->name('login');

    // Password reset routes
    Volt::route('/password/reset/{token}', 'password.reset')->name('password.reset');
    Volt::route('/password/reset', 'password.reset-request')->name('password.request');

    Route::group(['middleware' => ['admin.auth']], function () {
        Volt::route('/dashboard', 'dashboard.index')->name('index');
        Volt::route('/profile', 'admin.profile')->name('profile');

        // Rooms routes
        Route::group(['prefix' => 'rooms'], function () {
            Volt::route('/', 'rooms.index')->name('rooms.index');
            Volt::route('/{room}/show', 'rooms.show')->name('rooms.show');
            Volt::route('/{room}/edit', 'rooms.edit')->name('rooms.edit');
        });

        // Yatch routes
        Route::group(['prefix' => 'yatches'], function () {
            Volt::route('/', 'yatch.index')->name('yatch.index');
            Volt::route('/{yatch}/show', 'yatch.show')->name('yatch.show');
            Volt::route('/{yatch}/edit', 'yatch.edit')->name('yatch.edit');
        });

        // Houses routes
        Route::group(['prefix' => 'houses'], function () {
            Volt::route('/', 'houses.index')->name('houses.index');
            Volt::route('/{house}/show', 'houses.show')->name('houses.show');
            Volt::route('/{house}/edit', 'houses.edit')->name('houses.edit');
        });

        // Categories routes
        Route::group(['prefix' => 'category'], function () {
            Volt::route('/', 'category.index')->name('category.index');
        });

        // Amenities routes
        Route::group(['prefix' => 'amenity'], function () {
            Volt::route('/', 'amenity.index')->name('amenity.index');
        });

        // Website Settings routes
        Route::group(['prefix' => 'website-settings'], function () {
            Volt::route('/', 'website-settings.index')->name('website-settings.index');
        });

        // Bookings routes
        Route::group(['prefix' => 'bookings'], function () {
            Volt::route('/', 'bookings.index')->name('bookings.index');
            Volt::route('/create', 'bookings.create')->name('bookings.create');
        });

        // Room Bookings routes
        Route::group(['prefix' => 'bookings/rooms'], function () {
            Volt::route('/', 'booking.house.index')->name('bookings.house.index');
            Volt::route('/create', 'booking.house.create')->name('bookings.house.create');
            Volt::route('/{booking}/show', 'booking.house.show')->name('bookings.house.show');
            Volt::route('/{booking}/edit', 'booking.house.edit')->name('bookings.house.edit');
        });

        // Yacht Bookings routes
        Route::group(['prefix' => 'bookings/yatch'], function () {
            Volt::route('/', 'booking.yatch.index')->name('bookings.yatch.index');
            Volt::route('/create', 'booking.yatch.create')->name('bookings.yatch.create');
            Volt::route('/{booking}/show', 'booking.yatch.show')->name('bookings.yatch.show');
            Volt::route('/{booking}/edit', 'booking.yatch.edit')->name('bookings.yatch.edit');
        });

        Route::get('/logout', function () {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('admin.login');
        })->name('logout');
    });
});

// Customer Routes
Route::prefix('customer')->name('customer.')->group(function () {
    // Guest routes (Login, Register, Forgot Password)
    Route::middleware('guest')->group(function () {
        // Login
        Route::get('/login', function () {
            return view('frontend.auth.login');
        })->name('login');

        Route::post('/login', function () {
            // Handle login logic here
            return redirect()->route('customer.dashboard');
        })->name('login.submit');

        // Register
        Route::get('/register', function () {
            return view('frontend.auth.register');
        })->name('register');

        Route::post('/register', function () {
            // Handle registration logic here
            return redirect()->route('customer.dashboard');
        })->name('register.submit');

        // Forgot Password
        Route::get('/forgot-password', function () {
            return view('frontend.auth.forgot-password');
        })->name('forgot-password');

        Route::post('/forgot-password', function () {
            // Handle forgot password logic here
            return back()->with('success', 'Password reset link sent to your email!');
        })->name('forgot-password.submit');
    });

    // Authenticated customer routes
    Route::middleware('auth')->group(function () {
        // Dashboard
        Route::get('/dashboard', function () {
            $totalBookings = 0;
            $confirmedBookings = 0;
            $pendingBookings = 0;
            $totalSpent = 0;
            $recentBookings = [];

            return view('frontend.customer.dashboard', compact(
                'totalBookings',
                'confirmedBookings',
                'pendingBookings',
                'totalSpent',
                'recentBookings'
            ));
        })->name('dashboard');

        // Profile
        Route::get('/profile', function () {
            return view('frontend.customer.profile');
        })->name('profile');

        Route::put('/profile', function () {
            // Handle profile update logic here
            return back()->with('success', 'Profile updated successfully!');
        })->name('profile.update');

        Route::put('/password', function () {
            // Handle password change logic here
            return back()->with('success', 'Password changed successfully!');
        })->name('password.update');

        // Bookings
        Route::get('/bookings', function () {
            $bookings = [];
            return view('frontend.customer.bookings', compact('bookings'));
        })->name('bookings');

        Route::get('/bookings/{id}', function ($id) {
            // Fetch booking details
            return view('frontend.customer.booking-details');
        })->name('booking.details');

        // Logout
        Route::post('/logout', function () {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('home');
        })->name('logout');

        Route::get('/logout', function () {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('home');
        })->name('logout.get');
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

// Frontend pages routes
Route::get('/rooms', function () {
    return view('frontend.rooms.index');
})->name('rooms.index');

Route::get('/yachts', function () {
    return view('frontend.yachts.index');
})->name('yachts.index');
