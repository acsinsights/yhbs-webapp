<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Booking;
use App\Models\Room;
use App\Models\Yacht;
use App\Models\House;
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
        $price = 0;

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
                $price = $property->price_per_night ?? 0;
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
                $price = $property->price_per_hour ?? $property->price_per_night ?? 0;
            }
        } elseif ($type === 'house') {
            $property = House::find($id);
            if ($property) {
                if ($property->image) {
                    // Handle different image path formats
                    if (str_starts_with($property->image, 'http')) {
                        $propertyImage = $property->image;
                    } elseif (str_starts_with($property->image, '/default') || str_starts_with($property->image, '/frontend')) {
                        $propertyImage = asset($property->image);
                    } elseif (str_starts_with($property->image, 'storage/')) {
                        $propertyImage = asset($property->image);
                    } else {
                        $propertyImage = asset('storage/' . $property->image);
                    }
                }
                $propertyName = $property->name;
                $location = 'House #' . ($property->house_number ?? 'N/A');
                $price = $property->price_per_night ?? 0;
            }
        }

        if (!$property) {
            return redirect()->route('home')->with('error', 'Property not found.');
        }

        // If no image found, use default
        if (!$propertyImage) {
            $propertyImage = asset('frontend/assets/img/innerpages/hotel-img1.jpg');
        }

        // Get booking details from request
        $checkIn = $request->get('check_in');
        $checkOut = $request->get('check_out');
        $adults = $request->get('adults', 1);
        $children = $request->get('children', 0);
        $adultNames = $request->get('adult_names', []);
        $childrenNames = $request->get('children_names', []);

        if (!$checkIn || !$checkOut) {
            return redirect()->back()->with('error', 'Please select check-in and check-out dates.');
        }

        $checkInDate = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        // For yachts, calculate hours; for rooms/houses, calculate nights
        if ($type === 'yacht') {
            $hours = max(1, $checkInDate->diffInHours($checkOutDate));
            $subtotal = $price * $hours;
            $nights = $hours; // Store as hours for yachts
        } else {
            $totalHours = $checkInDate->diffInHours($checkOutDate);
            $nights = max(1, ceil($totalHours / 24));
            $subtotal = $price * $nights;
        }

        $serviceFee = 0; // No service fee for now
        $tax = 0; // No tax for now
        $total = $subtotal + $serviceFee + $tax;

        // Combine all guest names
        $allGuestNames = array_merge($adultNames, $childrenNames);

        $booking = (object) [
            'type' => $type,
            'property_id' => $id,
            'property_image' => $propertyImage,
            'property_name' => $propertyName,
            'location' => $location,
            'check_in' => $checkInDate->format('Y-m-d H:i'),
            'check_in_display' => $checkInDate->format('M d, Y h:i A'),
            'check_out' => $checkOutDate->format('Y-m-d H:i'),
            'check_out_display' => $checkOutDate->format('M d, Y h:i A'),
            'nights' => $nights,
            'guests' => $adults,
            'children' => $children,
            'guest_names' => $allGuestNames,
            'adult_names' => $adultNames,
            'children_names' => $childrenNames,
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
            'type' => 'required|in:room,yacht,house',
            'property_id' => 'required|integer',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'adult_names' => 'nullable|array',
            'children_names' => 'nullable|array',
            'payment_method' => 'required|in:cash,card,online,other',
            'total' => 'required|numeric|min:0',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'nullable|string',
            'special_requests' => 'nullable|string',
        ]);

        // Determine bookingable type and get the property
        if ($validated['type'] === 'room') {
            $bookingableType = Room::class;
            $property = Room::findOrFail($validated['property_id']);
        } elseif ($validated['type'] === 'yacht') {
            $bookingableType = Yacht::class;
            $property = Yacht::findOrFail($validated['property_id']);
        } else {
            $bookingableType = House::class;
            $property = House::findOrFail($validated['property_id']);
        }

        // Combine guest names
        $guestDetails = [
            'customer' => [
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'address' => $validated['address'] ?? null,
            ],
            'adult_names' => $validated['adult_names'] ?? [],
            'children_names' => $validated['children_names'] ?? [],
            'special_requests' => $validated['special_requests'] ?? null,
        ];

        // Create the booking
        $booking = Booking::create([
            'bookingable_type' => $bookingableType,
            'bookingable_id' => $validated['property_id'],
            'user_id' => Auth::id() ?? null,
            'adults' => $validated['adults'],
            'children' => $validated['children'] ?? 0,
            'guest_details' => $guestDetails,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
            'price' => $validated['total'],
            'status' => 'booked',
            'payment_status' => 'pending',
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['special_requests'] ?? null,
        ]);

        return redirect()->route('booking.confirmation', ['id' => $booking->id])
            ->with('success', 'Booking confirmed successfully!');
    }

    public function confirmation($id)
    {
        $booking = Booking::with(['bookingable', 'user'])->findOrFail($id);

        // Check if user is authorized to view this booking
        if (Auth::check() && $booking->user_id && $booking->user_id !== Auth::id()) {
            abort(403, 'Unauthorized access to booking.');
        }

        // Prepare booking data for view
        $propertyImage = null;
        $propertyName = 'Property';
        $location = 'Location';
        $propertyType = 'Property';

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
                $propertyType = 'Room';
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
                $propertyType = 'Yacht';
            }
        } elseif ($booking->bookingable_type === House::class) {
            $house = $booking->bookingable;
            if ($house) {
                // Log house image path for debugging
                \Log::info('House Image Debug', [
                    'house_id' => $house->id,
                    'house_image' => $house->image,
                    'house_name' => $house->name,
                ]);

                if ($house->image) {
                    // Handle different image path formats
                    if (str_starts_with($house->image, 'http')) {
                        $propertyImage = $house->image;
                    } elseif (str_starts_with($house->image, '/default') || str_starts_with($house->image, '/frontend')) {
                        // Image path starts with /default or /frontend, use asset directly
                        $propertyImage = asset($house->image);
                    } elseif (str_starts_with($house->image, 'default/') || str_starts_with($house->image, 'frontend/')) {
                        // Image path without leading slash
                        $propertyImage = asset($house->image);
                    } elseif (str_starts_with($house->image, 'storage/')) {
                        $propertyImage = asset($house->image);
                    } elseif (str_starts_with($house->image, 'houses/')) {
                        $propertyImage = asset('storage/' . $house->image);
                    } else {
                        // Assume it's already a relative path from storage
                        $propertyImage = asset('storage/' . $house->image);
                    }
                } else {
                    // Set default image if no image found
                    $propertyImage = asset('frontend/img/default-room.jpg');
                }
                
                \Log::info('House Image Final Path', ['property_image' => $propertyImage]);
                
                $propertyName = $house->name;
                $location = 'House #' . ($house->house_number ?? 'N/A');
                $propertyType = 'House';
            }
        }

        if (!$propertyImage) {
            $propertyImage = asset('frontend/assets/img/innerpages/hotel-img1.jpg');
        }

        $checkInDate = Carbon::parse($booking->check_in);
        $checkOutDate = Carbon::parse($booking->check_out);

        // Calculate nights or hours based on property type
        if ($booking->bookingable_type === Yacht::class) {
            $nights = max(1, $checkInDate->diffInHours($checkOutDate));
        } else {
            $totalHours = $checkInDate->diffInHours($checkOutDate);
            $nights = max(1, ceil($totalHours / 24));
        }

        // Get customer info from guest_details or user
        $guestDetails = $booking->guest_details ?? [];
        $customerInfo = $guestDetails['customer'] ?? [];

        $customerName = $customerInfo['first_name'] ?? $booking->user->name ?? 'Guest';
        if (isset($customerInfo['last_name'])) {
            $customerName .= ' ' . $customerInfo['last_name'];
        }

        // Calculate price per night
        $pricePerNight = $nights > 0 ? $booking->price / $nights : $booking->price;

        $bookingData = (object) [
            'id' => $booking->id,
            'reference' => 'YHBS' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
            'property_image' => $propertyImage,
            'property_name' => $propertyName,
            'property_type' => $propertyType,
            'location' => $location,
            'check_in' => $booking->check_in->format('M d, Y h:i A'),
            'check_out' => $booking->check_out->format('M d, Y h:i A'),
            'nights' => $nights,
            'guests' => $booking->adults,
            'children' => $booking->children,
            'guest_details' => $guestDetails,
            'adult_names' => $guestDetails['adult_names'] ?? [],
            'children_names' => $guestDetails['children_names'] ?? [],
            'customer_name' => $customerName,
            'customer_email' => $customerInfo['email'] ?? $booking->user->email ?? 'N/A',
            'customer_phone' => $customerInfo['phone'] ?? $booking->user->phone ?? 'N/A',
            'customer_address' => $customerInfo['address'] ?? null,
            'special_requests' => $guestDetails['special_requests'] ?? $booking->notes,
            'payment_method' => ucfirst($booking->payment_method->value),
            'payment_status' => ucfirst($booking->payment_status->value),
            'status' => ucfirst(str_replace('_', ' ', $booking->status->value)),
            'total' => $booking->price,
            'price_per_night' => $pricePerNight,
            'service_fee' => 0,
            'tax' => 0,
            'created_at' => $booking->created_at->format('M d, Y h:i A'),
        ];

        // Log for debugging
        \Log::info('Booking Confirmation Image Debug', [
            'booking_id' => $booking->id,
            'bookingable_type' => $booking->bookingable_type,
            'property_image' => $propertyImage,
            'bookingable' => $booking->bookingable ? get_class($booking->bookingable) : null,
        ]);

        return view('frontend.booking-confirmation', ['booking' => $bookingData]);
    }
}
