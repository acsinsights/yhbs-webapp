<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::redirect('/', '/dashboard');

Route::name('admin.')->group(function () {
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

        // Hotels routes
        Route::group(['prefix' => 'hotels'], function () {
            Volt::route('/', 'hotels.index')->name('hotels.index');
            Volt::route('/{hotel}/show', 'hotels.show')->name('hotels.show');
            Volt::route('/{hotel}/edit', 'hotels.edit')->name('hotels.edit');
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

        // Hotel Bookings routes
        Route::group(['prefix' => 'bookings/hotel'], function () {
            Volt::route('/', 'booking.hotel.index')->name('bookings.hotel.index');
            Volt::route('/create', 'booking.hotel.create')->name('bookings.hotel.create');
            Volt::route('/{booking}/show', 'booking.hotel.show')->name('bookings.hotel.show');
            Volt::route('/{booking}/edit', 'booking.hotel.edit')->name('bookings.hotel.edit');
        });

        // Yacht Bookings routes
        Route::group(['prefix' => 'bookings/yatch'], function () {
            Volt::route('/', 'booking.yatch.index')->name('bookings.yatch.index');
            Volt::route('/create', 'booking.yatch.create')->name('bookings.yatch.create');
            Volt::route('/{booking}/show', 'booking.yatch.show')->name('bookings.yatch.show');
            Volt::route('/{booking}/edit', 'booking.yatch.edit')->name('bookings.yatch.edit');
        });

        Route::get('/admin/logout', function () {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('admin.login');
        })->name('logout');
    });
});
