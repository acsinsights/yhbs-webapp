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
            ->with('bookingable')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($booking) {
                $bookingable = $booking->bookingable;

                // Handle image path properly
                $propertyImage = asset('frontend/img/innerpages/hotel-dt-room-img1.jpg');
                if ($bookingable && $bookingable->image) {
                    if (str_starts_with($bookingable->image, 'http')) {
                        $propertyImage = $bookingable->image;
                    } elseif (str_starts_with($bookingable->image, 'default/') || str_starts_with($bookingable->image, '/default') || str_starts_with($bookingable->image, 'frontend/') || str_starts_with($bookingable->image, '/frontend')) {
                        $propertyImage = asset($bookingable->image);
                    } elseif (str_starts_with($bookingable->image, 'storage/')) {
                        $propertyImage = asset($bookingable->image);
                    } else {
                        $propertyImage = asset('storage/' . $bookingable->image);
                    }
                }

                return [
                    'id' => $booking->id,
                    'check_in' => $booking->check_in ? $booking->check_in->format('M d, Y') : 'N/A',
                    'check_out' => $booking->check_out ? $booking->check_out->format('M d, Y') : 'N/A',
                    'status' => $booking->status,
                    'total' => $booking->price ?? 0,
                    'room_name' => $bookingable ? ($bookingable->name ?? 'N/A') : 'N/A',
                    'image' => $propertyImage,
                ];
            });

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
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\d\s\-\+\(\)]+$/'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && \Storage::exists($user->avatar)) {
                \Storage::delete($user->avatar);
            }

            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $validated['avatar'] = $avatarPath;
        }

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
            ->with('bookingable')
            ->latest()
            ->paginate(10)
            ->through(function ($booking) {
                // Eager load house relationship only for Room bookings
                if ($booking->bookingable instanceof \App\Models\Room) {
                    $booking->bookingable->load('house');
                }
                return $booking;
            });

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

    /**
     * Cancel a booking
     */
    public function cancelBooking($id)
    {
        $user = Auth::user();
        $booking = Booking::where('id', $id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Check if booking can be cancelled
        if (!$booking->canBeCancelled()) {
            return response()->json([
                'success' => false,
                'message' => 'This booking cannot be cancelled.'
            ], 400);
        }

        $booking->status = \App\Enums\BookingStatusEnum::CANCELLED;
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Booking cancelled successfully!'
        ]);
    }
}
