<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\House;
use App\Models\Booking;
use Illuminate\Http\Request;

class HouseController extends Controller
{
    /**
     * Display a listing of houses
     */
    public function index(Request $request)
    {
        $query = House::with(['rooms']);

        // Filter by capacity (using adults field)
        if ($request->filled('capacity')) {
            $query->where('adults', '>=', $request->capacity);
        }

        // Filter by check-in and check-out dates (availability)
        if ($request->filled('check_in') && $request->filled('check_out')) {
            $checkIn = $request->check_in;
            $checkOut = $request->check_out;

            // Get houses that have all rooms available for the date range
            $query->whereDoesntHave('rooms.bookings', function ($q) use ($checkIn, $checkOut) {
                $q->where(function ($q) use ($checkIn, $checkOut) {
                    $q->whereBetween('check_in', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out', [$checkIn, $checkOut])
                        ->orWhere(function ($q) use ($checkIn, $checkOut) {
                            $q->where('check_in', '<=', $checkIn)
                                ->where('check_out', '>=', $checkOut);
                        });
                })->whereIn('status', ['pending', 'booked', 'checked_in']);
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'latest');
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

        $houses = $query->active()
            ->paginate(12)
            ->appends($request->all());

        return view('frontend.houses.index', compact('houses'));
    }

    /**
     * Display the specified house
     */
    public function show($slug)
    {
        $house = House::with(['rooms'])->where('slug', $slug)->active()->firstOrFail();

        // Get similar houses (other houses with similar capacity)
        $similarHouses = House::where('id', '!=', $house->id)
            ->where('adults', '>=', $house->adults - 2)
            ->where('adults', '<=', $house->adults + 2)
            ->active()
            ->take(3)
            ->get();

        return view('frontend.houses.show', compact('house', 'similarHouses'));
    }
}
