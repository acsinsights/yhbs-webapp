<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Category;
use App\Models\Amenity;
use App\Models\Booking;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of rooms
     */
    public function index(Request $request)
    {
        // Eager load relationships to prevent N+1 queries
        $query = Room::query()->with(['categories', 'amenities']);

        // Filter by category (by ID)
        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }

        // Filter by adults capacity
        if ($request->filled('adults') && $request->adults > 0) {
            $query->where('adults', '>=', $request->adults);
        }

        // Filter by children capacity
        if ($request->filled('children') && $request->children > 0) {
            $query->where('children', '>=', $request->children);
        }

        // Filter by check-in and check-out dates (availability)
        if ($request->filled('check_in') && $request->filled('check_out')) {
            $checkIn = $request->check_in;
            $checkOut = $request->check_out;

            // Exclude rooms that have overlapping bookings
            $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->where(function ($query) use ($checkIn, $checkOut) {
                    // Check for any date overlap
                    $query->where('check_in', '<', $checkOut)
                        ->where('check_out', '>', $checkIn);
                })->whereIn('status', ['confirmed', 'pending']);
            });
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price_per_night', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_per_night', '<=', $request->max_price);
        }

        // Search by name or description
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%')
                    ->orWhere('room_number', 'like', '%' . $request->search . '%');
            });
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'latest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price_per_night', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price_per_night', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'capacity':
                $query->orderBy('adults', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $rooms = $query->active()
            ->paginate(12)
            ->appends($request->all());

        // Only fetch categories once
        $categories = Category::select('id', 'name', 'slug')->get();
        $amenities = Amenity::select('id', 'name')->get();

        return view('frontend.rooms.index', compact('rooms', 'categories', 'amenities'));
    }

    /**
     * Display the specified room
     */
    public function show($slug)
    {
        $room = Room::with(['categories', 'amenities'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        // Get similar rooms
        $similarRooms = Room::where('id', '!=', $room->id)
            ->active()
            ->take(3)
            ->get();

        // Get booked dates for this room
        $bookedDates = Booking::where('bookingable_type', Room::class)
            ->where('bookingable_id', $room->id)
            ->whereIn('status', ['pending', 'booked', 'checked_in'])
            ->get(['check_in', 'check_out'])
            ->flatMap(function ($booking) {
                $dates = [];
                $checkIn = new \DateTime($booking->check_in);
                $checkOut = new \DateTime($booking->check_out);

                while ($checkIn < $checkOut) {
                    $dates[] = $checkIn->format('Y-m-d');
                    $checkIn->modify('+1 day');
                }

                return $dates;
            })
            ->unique()
            ->values()
            ->toArray();

        return view('frontend.rooms.show', compact('room', 'similarRooms', 'bookedDates'));
    }
}
