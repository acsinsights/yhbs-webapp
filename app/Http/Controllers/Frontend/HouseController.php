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
        // Optimize query
        $query = House::query();

        // Filter by capacity (total guests)
        if ($request->filled('capacity') && $request->capacity > 0) {
            $capacity = $request->capacity;
            // Find houses where adults + children >= requested capacity
            $query->whereRaw('(adults + COALESCE(children, 0)) >= ?', [$capacity]);
        }

        // Fallback: Filter by adults capacity (for backward compatibility)
        if ($request->filled('adults') && $request->adults > 0) {
            $query->where('adults', '>=', $request->adults);
        }

        // Fallback: Filter by children capacity (for backward compatibility)
        if ($request->filled('children') && $request->children > 0) {
            $query->where('children', '>=', $request->children);
        }

        // Filter by check-in and check-out dates (availability)
        if ($request->filled('check_in') && $request->filled('check_out')) {
            $checkIn = $request->check_in;
            $checkOut = $request->check_out;

            // Exclude houses that have bookings overlapping with the requested dates
            $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->where(function ($query) use ($checkIn, $checkOut) {
                    // Check for any date overlap
                    $query->where('check_in', '<', $checkOut)
                        ->where('check_out', '>', $checkIn);
                })->whereIn('status', ['confirmed', 'pending']);
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
        $house = House::where('slug', $slug)
            ->active()
            ->firstOrFail();

        // Get similar houses with optimized query
        $similarHouses = House::select('id', 'name', 'slug', 'image', 'price_per_night', 'adults', 'children')
            ->where('id', '!=', $house->id)
            ->where('adults', '>=', max(1, $house->adults - 2))
            ->where('adults', '<=', $house->adults + 2)
            ->active()
            ->limit(3)
            ->get();

        return view('frontend.houses.show', compact('house', 'similarHouses'));
    }
}
