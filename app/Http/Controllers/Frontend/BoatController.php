<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Boat;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BoatController extends Controller
{
    /**
     * Display a listing of boats
     */
    public function index(Request $request): View
    {
        $query = Boat::active()->orderBy('sort_order')->orderBy('name');

        // Filter by service type
        if ($request->has('service_type') && $request->service_type) {
            $query->where('service_type', $request->service_type);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $boats = $query->paginate(12);

        return view('frontend.boats.index', compact('boats'));
    }

    /**
     * Display the specified boat
     */
    public function show(string $slug): View
    {
        $boat = Boat::where('slug', $slug)->where('is_active', true)->firstOrFail();

        return view('frontend.boats.show', compact('boat'));
    }

    /**
     * Get available time slots for a boat
     */
    public function getAvailableTimeSlots(Request $request)
    {
        $request->validate([
            'boat_id' => 'required|exists:boats,id',
            'date' => 'required|date|after_or_equal:today',
            'duration' => 'required|numeric|min:0.25',
        ]);

        $boat = Boat::findOrFail($request->boat_id);
        $date = $request->date;
        $durationHours = floatval($request->duration);

        $timeSlots = collect();
        $startHour = 9; // 9 AM
        $endHour = 18; // 6 PM

        // Generate slots based on duration
        $currentHour = $startHour;
        while ($currentHour + $durationHours <= $endHour) {
            $startTime = Carbon::parse($date)->setTime(floor($currentHour), ($currentHour - floor($currentHour)) * 60);
            $endTime = $startTime->copy()->addMinutes($durationHours * 60);

            // Check if slot is in the past (for today's date)
            $now = Carbon::now();
            $isPast = false;
            if (Carbon::parse($date)->isToday()) {
                $isPast = $startTime->lessThanOrEqualTo($now);
            }

            // Check if this slot is already booked
            $isBooked = Booking::where('bookingable_type', Boat::class)
                ->where('bookingable_id', $boat->id)
                ->where('status', '!=', 'cancelled')
                ->whereDate('check_in', $date)
                ->where('check_in', '<', $endTime)
                ->where('check_out', '>', $startTime)
                ->exists();

            // Mark as unavailable if booked OR if the time has passed
            $isAvailable = !$isBooked && !$isPast;

            $timeSlots->push([
                'start_time' => $startTime->format('H:i'),
                'end_time' => $endTime->format('H:i'),
                'display' => $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A'),
                'is_available' => $isAvailable,
                'value' => $startTime->format('H:i'),
            ]);

            // Move to next slot
            $currentHour += $durationHours;
        }

        return response()->json([
            'success' => true,
            'slots' => $timeSlots,
        ]);
    }
}

