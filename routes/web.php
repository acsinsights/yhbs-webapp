<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::redirect('/', '/admin/dashboard');

Route::name('admin.')->group(function () {
    Volt::route('/login', 'login')->name('login');

    Route::group(['middleware' => ['admin.auth']], function () {
        Volt::route('/dashboard', 'dashboard.index')->name('index');
        Volt::route('/profile', 'admin.profile')->name('profile');

        // Rooms routes
        Route::group(['prefix' => 'rooms'], function () {
            Volt::route('/', 'rooms.index')->name('rooms.index');
            Volt::route('/create', 'rooms.create')->name('rooms.create');
            Volt::route('/{room}/edit', 'rooms.edit')->name('rooms.edit');
        });

        // Yachts routes
        Route::group(['prefix' => 'yachts'], function () {
            Volt::route('/', 'yachts.index')->name('yachts.index');
            Volt::route('/create', 'yachts.create')->name('yachts.create');
            Volt::route('/{yatch}/edit', 'yachts.edit')->name('yachts.edit');
            Volt::route('/{yatch}/show', 'yachts.show')->name('yachts.show');
        });

        // Hotels routes
        Route::group(['prefix' => 'hotels'], function () {
            Volt::route('/', 'hotels.index')->name('hotels.index');
            Volt::route('/{hotel}/edit', 'hotels.edit')->name('hotels.edit');
        });

        // Categories routes
        Route::group(['prefix' => 'categories'], function () {
            Volt::route('/', 'categories.index')->name('categories.index');
            Volt::route('/create', 'categories.create')->name('categories.create');
            Volt::route('/{category}/edit', 'categories.edit')->name('categories.edit');
        });

        // Amenities routes
        Route::group(['prefix' => 'amenities'], function () {
            Volt::route('/', 'amenities.index')->name('amenities.index');
            Volt::route('/create', 'amenities.create')->name('amenities.create');
            Volt::route('/{amenity}/edit', 'amenities.edit')->name('amenities.edit');
        });

        // Bookings routes
        Route::group(['prefix' => 'bookings'], function () {
            Volt::route('/', 'bookings.index')->name('bookings.index');
            Volt::route('/create', 'bookings.create')->name('bookings.create');
        });

        Route::get('/admin/logout', function () {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('admin.login');
        })->name('logout');
    });
});
