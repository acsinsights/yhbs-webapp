<?php

use Livewire\Volt\Volt;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


Route::redirect('/', '/admin/dashboard');

Route::name('admin.')->group(function () {
    Volt::route('/login', 'login')->name('login');

    Route::group(['middleware' => ['admin.auth']], function () {
        Volt::route('/admin/dashboard', 'dashboard.index')->name('index');
        Volt::route('/admin/dashboard/admin', 'dashboard.admin')->name('dashboard.admin');
        Volt::route('/admin/dashboard/reception', 'dashboard.reception')->name('dashboard.reception');
        Volt::route('/admin/profile', 'admin.profile')->name('profile');

        Route::get('/admin/logout', function () {
            Auth::logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('admin.login');
        })->name('logout');
    });
});
