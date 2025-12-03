<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Category;
use App\Models\Amenity;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of rooms
     */
    public function index(Request $request)
    {
        $query = Room::with(['categories', 'amenities', 'house']);

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }

        // Filter by price range
        if ($request->filled('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by capacity (using adults field)
        if ($request->filled('capacity')) {
            $query->where('adults', '>=', $request->capacity);
        }

        // Search by name or description
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $rooms = $query->where('is_active', true)
            ->paginate(12);

        $categories = Category::all();
        $amenities = Amenity::all();

        return view('frontend.rooms.index', compact('rooms', 'categories', 'amenities'));
    }

    /**
     * Display the specified room
     */
    public function show($id)
    {
        $room = Room::with(['categories', 'amenities', 'house'])->findOrFail($id);

        // Get similar rooms (rooms from same house)
        $similarRooms = Room::where('house_id', $room->house_id)
            ->where('id', '!=', $room->id)
            ->where('is_active', true)
            ->take(3)
            ->get();

        // Get booked dates for this room
        $bookedDates = \App\Models\Booking::where('bookingable_type', Room::class)
            ->where('bookingable_id', $id)
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
