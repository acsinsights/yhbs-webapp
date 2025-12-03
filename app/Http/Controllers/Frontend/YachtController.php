<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Yatch;
use App\Models\Category;
use App\Models\Amenity;
use Illuminate\Http\Request;

class YachtController extends Controller
{
    /**
     * Display a listing of yachts
     */
    public function index(Request $request)
    {
        $query = Yatch::with(['categories', 'amenities']);

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

        // Filter by capacity
        if ($request->filled('capacity')) {
            $query->where('max_guests', '>=', $request->capacity);
        }

        // Search by name or description
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $yachts = $query->paginate(12);

        $categories = Category::all();
        $amenities = Amenity::all();

        return view('frontend.yachts.index', compact('yachts', 'categories', 'amenities'));
    }

    /**
     * Display the specified yacht
     */
    public function show($id)
    {
        $yacht = Yatch::with(['categories', 'amenities'])->findOrFail($id);

        // Get similar yachts (yachts with similar category)
        $categoryIds = $yacht->categories->pluck('id');
        $similarYachts = Yatch::whereHas('categories', function ($q) use ($categoryIds) {
            $q->whereIn('categories.id', $categoryIds);
        })
            ->where('id', '!=', $yacht->id)
            ->take(3)
            ->get();

        // Get booked dates for this yacht
        $bookedDates = \App\Models\Booking::where('bookingable_type', Yatch::class)
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

        return view('frontend.yachts.show', compact('yacht', 'similarYachts', 'bookedDates'));
    }
}
