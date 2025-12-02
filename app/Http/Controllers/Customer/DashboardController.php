<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    /**
     * Show customer dashboard
     */
    public function index()
    {
        $user = Auth::user();

        // Get bookings data
        $totalBookings = Booking::where('user_id', $user->id)->count();
        $confirmedBookings = Booking::where('user_id', $user->id)
            ->whereIn('status', ['confirmed', 'booked', 'checked_in'])
            ->count();
        $pendingBookings = Booking::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        // Calculate total spent from price field
        $totalSpent = Booking::where('user_id', $user->id)
            ->whereIn('status', ['confirmed', 'booked', 'checked_in', 'checked_out'])
            ->sum('price');

        $recentBookings = Booking::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return view('frontend.customer.dashboard', compact(
            'totalBookings',
            'confirmedBookings',
            'pendingBookings',
            'totalSpent',
            'recentBookings'
        ));
    }

    /**
     * Show customer profile
     */
    public function profile()
    {
        return view('frontend.customer.profile');
    }

    /**
     * Update customer profile
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Profile updated successfully!');
    }

    /**
     * Update customer password
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return back()->with('success', 'Password updated successfully!');
    }

    /**
     * Show customer bookings
     */
    public function bookings()
    {
        $user = Auth::user();
        $bookings = Booking::where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('frontend.customer.bookings', compact('bookings'));
    }

    /**
     * Show booking details
     */
    public function bookingDetails($id)
    {
        $user = Auth::user();
        $booking = Booking::where('id', $id)
            ->where('user_id', $user->id)
            ->with('bookingable')
            ->firstOrFail();

        return view('frontend.customer.booking-details', compact('booking'));
    }
}
