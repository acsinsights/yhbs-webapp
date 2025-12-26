<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Yacht;
use App\Models\Category;
use App\Models\Amenity;
use App\Models\Booking;
use Illuminate\Http\Request;

class YachtController extends Controller
{
    /**
     * Display a listing of yachts
     */
    public function index(Request $request)
    {
        // Optimize query with eager loading
        $query = Yacht::query()->with(['categories', 'amenities']);

        // Filter by category (by ID)
        if ($request->filled('category')) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }

        // Filter by capacity (total guests)
        if ($request->filled('capacity') && $request->capacity > 0) {
            $query->where('max_guests', '>=', $request->capacity);
        }

        // Fallback: Filter by adults capacity (for backward compatibility)
        if ($request->filled('adults') && $request->adults > 0) {
            $query->where('max_guests', '>=', $request->adults);
        }

        // Fallback: Filter by children (for backward compatibility)
        if ($request->filled('children') && $request->children > 0) {
            // Yachts max_guests includes both adults and children
            $totalGuests = ($request->adults ?? 0) + $request->children;
            $query->where('max_guests', '>=', $totalGuests);
        }

        // Filter by check-in and check-out dates (availability)
        if ($request->filled('check_in') && $request->filled('check_out')) {
            $checkIn = $request->check_in;
            $checkOut = $request->check_out;

            // Exclude yachts that have overlapping bookings
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
            $query->where('price_per_hour', '>=', $request->min_price);
        }
        if ($request->filled('max_price')) {
            $query->where('price_per_hour', '<=', $request->max_price);
        }

        // Search by name or description
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Sort options
        $sortBy = $request->get('sort_by', 'latest');
        switch ($sortBy) {
            case 'price_low':
                $query->orderBy('price_per_hour', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price_per_hour', 'desc');
                break;
            case 'name':
                $query->orderBy('name', 'asc');
                break;
            case 'capacity':
                $query->orderBy('max_guests', 'desc');
                break;
            default:
                $query->latest();
                break;
        }

        $yachts = $query->active()
            ->paginate(12)
            ->appends($request->all());

        // Only fetch needed columns
        $categories = Category::select('id', 'name', 'slug')->get();
        $amenities = Amenity::select('id', 'name')->get();

        return view('frontend.yachts.index', compact('yachts', 'categories', 'amenities'));
    }

    /**
     * Display the specified yacht
     */
    public function show($slug)
    {
        $yacht = Yacht::with(['categories', 'amenities'])
            ->where('slug', $slug)
            ->active()
            ->firstOrFail();

        // Get similar yachts with optimized query
        $categoryIds = $yacht->categories->pluck('id');
        $similarYachts = Yacht::select('id', 'name', 'slug', 'image', 'price_per_hour', 'max_guests')
            ->whereHas('categories', function ($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
            ->where('id', '!=', $yacht->id)
            ->active()
            ->limit(3)
            ->get();

        // Get booked dates for this yacht (optimized)
        $bookedDates = Booking::where('bookingable_type', Yacht::class)
            ->where('bookingable_id', $yacht->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->select('check_in', 'check_out')
            ->get()
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
