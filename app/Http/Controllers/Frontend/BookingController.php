<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Yacht;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function checkout(Request $request)
    {
        $type = $request->get('type', 'room');
        $id = $request->get('id');

        if (!$id) {
            return redirect()->route('home')->with('error', 'Invalid booking request.');
        }

        // Fetch property details based on type
        $property = null;
        $propertyImage = null;
        $propertyName = 'Property';
        $location = 'Location';
        $price = 150;

        if ($type === 'room') {
            $property = Room::with('house')->find($id);
            if ($property) {
                if ($property->image) {
                    if (str_starts_with($property->image, '/default')) {
                        $propertyImage = asset($property->image);
                    } else {
                        $propertyImage = asset('storage/' . $property->image);
                    }
                }
                $propertyName = $property->name;
                $location = $property->house->name ?? 'N/A';
                $price = $property->price;
            }
        } elseif ($type === 'yacht') {
            $property = Yacht::find($id);
            if ($property) {
                if ($property->image) {
                    if (str_starts_with($property->image, '/default')) {
                        $propertyImage = asset($property->image);
                    } else {
                        $propertyImage = asset('storage/' . $property->image);
                    }
                }
                $propertyName = $property->name;
                $location = $property->location ?? 'N/A';
                $price = $property->price;
            }
        }

        if (!$property) {
            return redirect()->route('home')->with('error', 'Property not found.');
        }

        // If no image found, use default
        if (!$propertyImage) {
            $propertyImage = asset('frontend/assets/img/innerpages/hotel-img1.jpg');
        }

        // Calculate nights and total
        $checkIn = $request->get('check_in', date('Y-m-d'));
        $checkOut = $request->get('check_out', date('Y-m-d', strtotime('+3 days')));

        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);
        $nights = max(1, $checkInDate->diffInDays($checkOutDate));

        $subtotal = $price * $nights;
        $serviceFee = round($subtotal * 0.05, 2); // 5% service fee
        $tax = round($subtotal * 0.10, 2); // 10% tax
        $total = $subtotal + $serviceFee + $tax;

        $booking = (object) [
            'type' => $type,
            'property_id' => $id,
            'property_image' => $propertyImage,
            'property_name' => $propertyName,
            'location' => $location,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'arrival_time' => $request->get('arrival_time'),
            'nights' => $nights,
            'guests' => $request->get('adults', 2),
            'children' => $request->get('children', 0),
            'guest_names' => $request->get('guest_names', []),
            'price_per_night' => $price,
            'subtotal' => $subtotal,
            'service_fee' => $serviceFee,
            'tax' => $tax,
            'total' => $total,
        ];

        return view('frontend.checkout', compact('booking'));
    }

    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:room,yacht',
            'property_id' => 'required|integer',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'arrival_time' => 'nullable|string',
            'guest_names' => 'nullable|array',
            'payment_method' => 'required|in:cash,card,online',
            'total' => 'required|numeric|min:0',
        ]);

        // Determine bookingable type and get the property
        if ($validated['type'] === 'room') {
            $bookingableType = Room::class;
            $property = Room::findOrFail($validated['property_id']);
        } else {
            $bookingableType = Yacht::class;
            $property = Yacht::findOrFail($validated['property_id']);
        }

        // Create the booking
        $booking = Booking::create([
            'bookingable_type' => $bookingableType,
            'bookingable_id' => $validated['property_id'],
            'user_id' => Auth::id() ?? null,
            'adults' => $validated['adults'],
            'children' => $validated['children'] ?? 0,
            'guest_details' => $validated['guest_names'] ?? [],
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'arrival_time' => $validated['arrival_time'] ?? null,
            'price' => $validated['total'],
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => $validated['payment_method'],
            'notes' => $request->get('notes'),
        ]);

        return redirect()->route('booking.confirmation', ['id' => $booking->id])
            ->with('success', 'Booking created successfully!');
    }

    public function confirmation($id)
    {
        $booking = Booking::with(['bookingable', 'user'])->findOrFail($id);

        // Check if user is authorized to view this booking
        if (Auth::check() && $booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking.');
        }

        // Prepare booking data for view
        $propertyImage = null;
        $propertyName = 'Property';
        $location = 'Location';

        if ($booking->bookingable_type === Room::class) {
            $room = $booking->bookingable;
            if ($room) {
                if ($room->image) {
                    if (str_starts_with($room->image, '/default')) {
                        $propertyImage = asset($room->image);
                    } else {
                        $propertyImage = asset('storage/' . $room->image);
                    }
                }
                $propertyName = $room->name;
                $location = $room->house->name ?? 'N/A';
            }
        } elseif ($booking->bookingable_type === Yacht::class) {
            $yacht = $booking->bookingable;
            if ($yacht) {
                if ($yacht->image) {
                    if (str_starts_with($yacht->image, '/default')) {
                        $propertyImage = asset($yacht->image);
                    } else {
                        $propertyImage = asset('storage/' . $yacht->image);
                    }
                }
                $propertyName = $yacht->name;
                $location = $yacht->location ?? 'N/A';
            }
        }

        if (!$propertyImage) {
            $propertyImage = asset('frontend/assets/img/innerpages/hotel-img1.jpg');
        }

        $checkInDate = Carbon::parse($booking->check_in);
        $checkOutDate = Carbon::parse($booking->check_out);
        $nights = max(1, $checkInDate->diffInDays($checkOutDate));

        $bookingData = (object) [
            'id' => $booking->id,
            'reference' => 'YHBS' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
            'property_image' => $propertyImage,
            'property_name' => $propertyName,
            'location' => $location,
            'check_in' => $booking->check_in->format('Y-m-d'),
            'check_out' => $booking->check_out->format('Y-m-d'),
            'arrival_time' => $booking->arrival_time,
            'nights' => $nights,
            'guests' => $booking->adults,
            'children' => $booking->children,
            'customer_name' => $booking->user->name ?? 'Guest',
            'customer_email' => $booking->user->email ?? 'N/A',
            'customer_phone' => $booking->user->phone ?? 'N/A',
            'payment_method' => ucfirst($booking->payment_method),
            'payment_status' => ucfirst($booking->payment_status),
            'status' => ucfirst(str_replace('_', ' ', $booking->status)),
            'total' => $booking->price,
            'created_at' => $booking->created_at->format('M d, Y h:i A'),
        ];

        return view('frontend.booking-confirmation', ['booking' => $bookingData]);
    }
}
