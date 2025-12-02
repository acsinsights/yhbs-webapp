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
