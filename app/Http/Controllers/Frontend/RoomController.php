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
        $query = Room::with(['categories', 'amenities', 'house']);

        // Filter by category
        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.slug', $request->category);
            });
        }

        // Filter by amenities
        if ($request->filled('amenities') && is_array($request->amenities)) {
            foreach ($request->amenities as $amenityId) {
                $query->whereHas('amenities', function ($q) use ($amenityId) {
                    $q->where('amenities.id', $amenityId);
                });
            }
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

        // Filter by number of children
        if ($request->filled('children')) {
            $query->where('children', '>=', $request->children);
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

        $categories = Category::all();
        $amenities = Amenity::all();

        return view('frontend.rooms.index', compact('rooms', 'categories', 'amenities'));
    }

    /**
     * Display the specified room
     */
    public function show($slug)
    {
        $room = Room::with(['categories', 'amenities', 'house'])->where('slug', $slug)->active()->firstOrFail();

        // Get similar rooms (rooms from same house)
        $similarRooms = Room::where('house_id', $room->house_id)
            ->where('id', '!=', $room->id)
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
