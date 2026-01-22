<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Include frontend routes
require __DIR__ . '/frontend.php';

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

        // Houses routes
        Route::group(['prefix' => 'houses'], function () {
            Volt::route('/', 'houses.index')->name('houses.index');
            Volt::route('/{house}/show', 'houses.show')->name('houses.show');
            Volt::route('/{house}/edit', 'houses.edit')->name('houses.edit');
        });

        // Boats routes
        Route::group(['prefix' => 'boats'], function () {
            Volt::route('/', 'boats.index')->name('boats.index');
            Volt::route('/{boat}/show', 'boats.show')->name('boats.show');
            Volt::route('/{boat}/edit', 'boats.edit')->name('boats.edit');

            // Boat Service Types
            Route::group(['prefix' => 'service-types'], function () {
                Volt::route('/', 'admin.boat-service-type.index')->name('boats.service-types.index');
                Volt::route('/create', 'admin.boat-service-type.create')->name('boats.service-types.create');
                Volt::route('/{serviceType}/edit', 'admin.boat-service-type.edit')->name('boats.service-types.edit');
            });
        });

        // Categories routes
        Route::group(['prefix' => 'category'], function () {
            Volt::route('/', 'category.index')->name('category.index');
        });

        // Amenities routes
        Route::group(['prefix' => 'amenity'], function () {
            Volt::route('/', 'amenity.index')->name('amenity.index');
        });

        // Coupons routes
        Route::group(['prefix' => 'coupons'], function () {
            Volt::route('/', 'coupons.index')->name('coupons.index');
            Volt::route('/create', 'coupons.create')->name('coupons.create');
            Volt::route('/{coupon}/edit', 'coupons.edit')->name('coupons.edit');
        });

        // Website Settings routes
        Route::group(['prefix' => 'website-settings'], function () {
            Volt::route('/', 'admin.website-settings.index')->name('website-settings.index');
        });

        // Policy Pages routes
        Route::group(['prefix' => 'policy-pages'], function () {
            Volt::route('/', 'admin.policy-pages.index')->name('policy-pages.index');
            Volt::route('/create', 'admin.policy-pages.edit')->name('policy-pages.create');
            Volt::route('/{id}/edit', 'admin.policy-pages.edit')->name('policy-pages.edit');
        });

        // Sliders routes
        Route::group(['prefix' => 'sliders'], function () {
            Volt::route('/', 'admin.sliders.index')->name('sliders.index');
            Volt::route('/create', 'admin.sliders.edit')->name('sliders.create');
            Volt::route('/{id}/edit', 'admin.sliders.edit')->name('sliders.edit');
        });

        // Testimonials routes
        Route::group(['prefix' => 'testimonials'], function () {
            Volt::route('/', 'admin.testimonials.index')->name('testimonials.index');
            Volt::route('/create', 'admin.testimonials.edit')->name('testimonials.create');
            Volt::route('/{id}/edit', 'admin.testimonials.edit')->name('testimonials.edit');
        });

        // Statistics routes
        Route::group(['prefix' => 'statistics'], function () {
            Volt::route('/', 'admin.statistics.index')->name('statistics.index');
            Volt::route('/{statistic}/edit', 'admin.statistics.edit')->name('statistics.edit');
        });

        // Page Meta routes
        Route::group(['prefix' => 'page-meta'], function () {
            Volt::route('/', 'admin.page-meta.index')->name('page-meta.index');
            Volt::route('/{id}/edit', 'admin.page-meta.edit')->name('page-meta.edit');
        });

        // Contact Submissions routes
        Route::group(['prefix' => 'contacts'], function () {
            Volt::route('/', 'admin.contacts.index')->name('contacts.index');
            Volt::route('/{id}/show', 'admin.contacts.show')->name('contacts.show');
        });

        // Career Applications routes
        Route::group(['prefix' => 'career-applications'], function () {
            Volt::route('/', 'admin.career-applications.index')->name('career-applications.index');
            Volt::route('/{id}/show', 'admin.career-applications.show')->name('career-applications.show');
        });

        // Customers routes
        Route::group(['prefix' => 'customers'], function () {
            Volt::route('/', 'admin.customers.index')->name('customers.index');
            Volt::route('/{id}/show', 'admin.customers.show')->name('customers.show');
        });

        // Blogs routes
        Route::group(['prefix' => 'blogs'], function () {
            Volt::route('/', 'blogs.index')->name('blogs.index');
            Volt::route('/{blog}/edit', 'blogs.edit')->name('blogs.edit');
        });

        // Bookings routes
        Route::group(['prefix' => 'bookings'], function () {
            Volt::route('/', 'bookings.index')->name('bookings.index');
            Volt::route('/create', 'bookings.create')->name('bookings.create');

            // Download receipt
            Route::get('/{booking}/download-receipt', [App\Http\Controllers\Admin\BookingController::class, 'downloadReceipt'])
                ->name('booking.download-receipt');
        });

        // Cancellation Requests
        Volt::route('/cancellation-requests', 'admin.cancellation-requests')->name('cancellation-requests');

        // Reschedule Requests
        Volt::route('/reschedule-requests', 'admin.reschedule-requests')->name('reschedule-requests');

        // Room Bookings routes
        Route::group(['prefix' => 'bookings/rooms'], function () {
            Volt::route('/', 'booking.room.index')->name('bookings.room.index');
            Volt::route('/create', 'booking.room.create')->name('bookings.room.create');
            Volt::route('/{booking}/show', 'booking.room.show')->name('bookings.room.show');
            Volt::route('/{booking}/edit', 'booking.room.edit')->name('bookings.room.edit');
        });

        Route::group(['prefix' => 'bookings/house'], function () {
            Volt::route('/', 'booking.house.index')->name('bookings.house.index');
            Volt::route('/create', 'booking.house.create')->name('bookings.house.create');
            Volt::route('/{booking}/show', 'booking.house.show')->name('bookings.house.show');
            Volt::route('/{booking}/edit', 'booking.house.edit')->name('bookings.house.edit');
        });

        // Boat Bookings routes
        Route::group(['prefix' => 'bookings/boat'], function () {
            Volt::route('/', 'booking.boat.index')->name('bookings.boat.index');
            Volt::route('/create', 'booking.boat.create')->name('bookings.boat.create');
            Volt::route('/{booking}/show', 'booking.boat.show')->name('bookings.boat.show');
            Volt::route('/{booking}/edit', 'booking.boat.edit')->name('bookings.boat.edit');
        });

        // Logout route
        Route::get('/logout', function () {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('admin.login');
        })->name('logout');

        // Notification routes
        Route::get('/notifications/mark-read/{id}', function ($id) {
            $notification = auth()->user()->notifications()->find($id);
            if ($notification) {
                $notification->markAsRead();
            }
            return response()->json(['success' => true]);
        })->name('notifications.mark-read');

        Route::get('/notifications/mark-all-read', function () {
            auth()->user()->unreadNotifications->markAsRead();
            return redirect()->back()->with('success', 'All notifications marked as read');
        })->name('notifications.mark-all-read');

        Volt::route('/notifications', 'admin.notifications')->name('notifications');
    });
});

// Ottu Payment Routes
Route::prefix('payment')->name('ottu.')->group(function () {
    Route::get('/checkout/{bookingId}', [App\Http\Controllers\OttuCheckoutController::class, 'checkout'])
        ->middleware('auth')
        ->name('checkout');

    Route::get('/success/{bookingId}', [App\Http\Controllers\OttuCheckoutController::class, 'success'])
        ->middleware('auth')
        ->name('success');

    Route::get('/cancel/{bookingId}', [App\Http\Controllers\OttuCheckoutController::class, 'cancel'])
        ->middleware('auth')
        ->name('cancel');

    Route::post('/webhook', [App\Http\Controllers\OttuCheckoutController::class, 'webhook'])
        ->name('webhook');

    Route::get('/payment-methods', [App\Http\Controllers\OttuCheckoutController::class, 'paymentMethods'])
        ->name('payment-methods');
});

// Checkout Coupon Routes
Route::prefix('checkout')->middleware('auth')->group(function () {
    Route::post('/apply-coupon', [App\Http\Controllers\OttuCheckoutController::class, 'applyCoupon'])
        ->name('checkout.apply-coupon');

    Route::post('/remove-coupon', [App\Http\Controllers\OttuCheckoutController::class, 'removeCoupon'])
        ->name('checkout.remove-coupon');
});
