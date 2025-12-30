<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Booking;
use App\Models\Coupon;
use App\Models\Room;
use App\Models\House;
use App\Services\CouponService;
use App\Services\WalletService;
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

        // Clear coupon session if property/type has changed
        $currentProperty = session('checkout_property_id');
        $currentType = session('checkout_property_type');

        if ($currentProperty != $id || $currentType != $type) {
            session()->forget('applied_coupon');
            session(['checkout_property_id' => $id, 'checkout_property_type' => $type]);
        }

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
            $property = Room::find($id);
            if ($property) {
                // CRITICAL DEBUG: Check property right after fetch
                \Log::info('Property Fetched', [
                    'type' => 'room',
                    'id' => $id,
                    'property_id' => $property->id,
                    'price_per_night_RAW' => $property->getAttributes()['price_per_night'] ?? 'NOT SET',
                    'price_per_night_ACCESSOR' => $property->price_per_night,
                ]);

                if ($property->image) {
                    if (str_starts_with($property->image, '/default')) {
                        $propertyImage = asset($property->image);
                    } else {
                        $propertyImage = asset('storage/' . $property->image);
                    }
                }
                $propertyName = $property->name;
                $location = 'Room #' . ($property->room_number ?? 'N/A');
                $price = $property->price_per_night ?? 0;
            }
        } elseif ($type === 'house') {
            $property = House::find($id);
            if ($property) {
                if ($property->image) {
                    // Handle different image path formats
                    if (str_starts_with($property->image, 'http')) {
                        $propertyImage = $property->image;
                    } elseif (str_starts_with($property->image, 'default/') || str_starts_with($property->image, '/default') || str_starts_with($property->image, 'frontend/') || str_starts_with($property->image, '/frontend')) {
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

        $checkInDate = Carbon::parse($checkIn)->startOfDay();
        $checkOutDate = Carbon::parse($checkOut)->startOfDay();

        // Calculate nights
        $nights = $checkInDate->diffInDays($checkOutDate);

        // Allow same-day bookings (0 nights = 1 day booking)
        if ($nights === 0) {
            $nights = 1;
        }

        // Calculate price based on property type and nights
        if ($type === 'house') {
            // Houses: use tiered pricing structure like rooms
            if ($nights == 1) {
                $subtotal = $property->price_per_night ?? 0;
            } elseif ($nights == 2) {
                if ($property->price_per_2night) {
                    $subtotal = $property->price_per_2night;
                } else {
                    $subtotal = ($property->price_per_night ?? 0) * 2;
                }
            } elseif ($nights == 3) {
                if ($property->price_per_3night) {
                    $subtotal = $property->price_per_3night;
                } else {
                    $subtotal = ($property->price_per_night ?? 0) * 3;
                }
            } else {
                // 4+ nights
                if ($property->price_per_3night) {
                    $basePrice = $property->price_per_3night;
                } else {
                    $basePrice = ($property->price_per_night ?? 0) * 3;
                }
                $additionalNights = $nights - 3;
                $additionalPrice = $additionalNights * ($property->additional_night_price ?? $property->price_per_night ?? 0);
                $subtotal = $basePrice + $additionalPrice;
            }

            \Log::info('House Booking Calculation', [
                'property_id' => $id,
                'nights' => $nights,
                'price_per_night' => $property->price_per_night,
                'price_per_2night' => $property->price_per_2night ?? 'not set',
                'price_per_3night' => $property->price_per_3night ?? 'not set',
                'calculated_subtotal' => $subtotal,
            ]);
        } else {
            // For rooms: use night-specific pricing
            if ($nights == 1) { // Use loose comparison to handle both int and float
                // CRITICAL DEBUG
                \Log::info('BEFORE Assignment', [
                    'type' => $type,
                    'property_id' => $id,
                    'property_price_per_night_RAW' => $property->price_per_night,
                    'property_price_per_night_TYPE' => gettype($property->price_per_night),
                ]);

                $subtotal = $property->price_per_night ?? 0;

                \Log::info('AFTER Assignment', [
                    'subtotal_VALUE' => $subtotal,
                    'subtotal_TYPE' => gettype($subtotal),
                    'property_STILL' => $property->price_per_night,
                ]);

                // Debug logging
                \Log::info('1 Night Booking Debug', [
                    'type' => $type,
                    'property_id' => $id,
                    'nights' => $nights,
                    'property_price_per_night' => $property->price_per_night,
                    'calculated_subtotal' => $subtotal,
                ]);
            } elseif ($nights == 2) { // Use loose comparison
                if ($property->price_per_2night) {
                    $subtotal = $property->price_per_2night;
                } else {
                    $subtotal = ($property->price_per_night ?? 0) * 2;
                }
            } elseif ($nights == 3) { // Use loose comparison
                if ($property->price_per_3night) {
                    $subtotal = $property->price_per_3night;
                } else {
                    $subtotal = ($property->price_per_night ?? 0) * 3;
                }
            } else {
                // 4+ nights
                if ($property->price_per_3night) {
                    $basePrice = $property->price_per_3night;
                } else {
                    $basePrice = ($property->price_per_night ?? 0) * 3;
                }
                $additionalNights = $nights - 3;
                $additionalPrice = $additionalNights * ($property->additional_night_price ?? $property->price_per_night ?? 0);
                $subtotal = $basePrice + $additionalPrice;
            }
        }

        $serviceFee = 0; // No service fee for now
        $tax = 0; // No tax for now
        $total = $subtotal + $serviceFee + $tax;

        // TEMPORARY DEBUG - Remove after fixing
        if ($type === 'room' && $id == 12) {
            \Log::info('Room 12 Checkout Debug', [
                'nights' => $nights,
                'property_price_per_night' => $property->price_per_night,
                'calculated_subtotal' => $subtotal,
                'total' => $total,
            ]);
        }

        // Combine all guest names
        $allGuestNames = array_merge($adultNames, $childrenNames);

        // Ensure price_per_night is a valid number
        $pricePerNight = floatval($property->price_per_night ?? 0);

        // Add debug logging for houses
        if ($type === 'house') {
            \Log::info('House Booking Object Creation', [
                'property_id' => $id,
                'property_price_per_night' => $property->price_per_night,
                'pricePerNight_converted' => $pricePerNight,
                'nights' => $nights,
                'subtotal' => $subtotal,
            ]);
        }

        $booking = (object) [
            'type' => $type,
            'property_id' => $id,
            'property_image' => $propertyImage,
            'property_name' => $propertyName,
            'location' => $location,
            'check_in' => $checkInDate->format('Y-m-d'),
            'check_in_display' => $checkInDate->format('M d, Y'),
            'check_out' => $checkOutDate->format('Y-m-d'),
            'check_out_display' => $checkOutDate->format('M d, Y'),
            'nights' => $nights,
            'guests' => $adults,
            'children' => $children,
            'guest_names' => $allGuestNames,
            'adult_names' => $adultNames,
            'children_names' => $childrenNames,
            'price_per_night' => $pricePerNight,
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
            'coupon_code' => 'nullable|string',
            'discount_amount' => 'nullable|numeric|min:0',
            'use_wallet_balance' => 'nullable|boolean',
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

        // Handle coupon if provided
        $couponId = null;
        $discountAmount = 0;

        if (!empty($validated['coupon_code'])) {
            $coupon = Coupon::where('code', strtoupper($validated['coupon_code']))->first();

            if ($coupon) {
                // Final validation: check usage limits one more time before confirming
                if ($coupon->usage_limit && $coupon->usage_count >= $coupon->usage_limit) {
                    return redirect()->back()
                        ->withInput()
                        ->with('coupon_error', 'This coupon has reached its usage limit.');
                }

                // Check per-user usage limit
                $userId = Auth::id();
                if ($userId && $coupon->usage_limit_per_user) {
                    $userUsageCount = \App\Models\Booking::where('user_id', $userId)
                        ->where('coupon_id', $coupon->id)
                        ->count();

                    if ($userUsageCount >= $coupon->usage_limit_per_user) {
                        return redirect()->back()
                            ->withInput()
                            ->with('coupon_error', 'You have already used this coupon the maximum number of times.');
                    }
                }

                // Check if coupon is still valid
                if (!$coupon->isValid()) {
                    return redirect()->back()
                        ->withInput()
                        ->with('coupon_error', 'This coupon is no longer valid.');
                }

                // Get discount amount from form if provided, otherwise from session or calculate
                if (isset($validated['discount_amount'])) {
                    $discountAmount = (float) $validated['discount_amount'];
                } else {
                    $appliedCoupon = session('applied_coupon');
                    if ($appliedCoupon && $appliedCoupon['code'] === $coupon->code && isset($appliedCoupon['discount_amount'])) {
                        $discountAmount = $appliedCoupon['discount_amount'];
                    } else {
                        // Calculate discount as last resort
                        // Note: validated['total'] is already discounted, so calculate from original
                        $originalTotal = (float) $validated['total'] + $discountAmount;
                        $discountAmount = $coupon->calculateDiscount($originalTotal);
                    }
                }

                $couponId = $coupon->id;
            }
        }

        // Handle wallet balance usage
        $walletAmountUsed = 0;
        if (!empty($validated['use_wallet_balance']) && Auth::check()) {
            $user = Auth::user();
            $walletBalance = $user->wallet_balance;
            $totalAmount = (float) $validated['total'];

            // Calculate how much wallet balance to use
            $walletAmountUsed = min($walletBalance, $totalAmount);

            if ($walletAmountUsed > 0) {
                // Deduct from wallet
                $walletService = app(WalletService::class);
                // Note: We'll create the booking first, then deduct wallet
            }
        }

        // Calculate final amount to pay
        $finalTotal = (float) $validated['total'] - $walletAmountUsed;

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

        // Calculate nights and pricing breakdown
        $checkInDate = Carbon::parse($validated['check_in'])->startOfDay();
        $checkOutDate = Carbon::parse($validated['check_out'])->startOfDay();
        $nights = max(1, $checkInDate->diffInDays($checkOutDate) ?: 1);

        // Get price per night from property
        $pricePerNight = 0;
        if ($validated['type'] === 'yacht') {
            $pricePerNight = $property->price_per_hour ?? $property->price_per_night ?? 0;
        } else {
            $pricePerNight = $property->price_per_night ?? 0;
        }

        // Service fee and tax (currently 0, but structured for future use)
        $serviceFee = 0;
        $tax = 0;

        // Create the booking
        $booking = Booking::create([
            'bookingable_type' => $bookingableType,
            'bookingable_id' => $validated['property_id'],
            'user_id' => Auth::id() ?? null,
            'adults' => $validated['adults'],
            'children' => $validated['children'] ?? 0,
            'guest_details' => $guestDetails,
            'check_in' => Carbon::parse($validated['check_in'])->format('Y-m-d'),
            'check_out' => Carbon::parse($validated['check_out'])->format('Y-m-d'),
            'price' => $validated['total'],
            'price_per_night' => $pricePerNight,
            'nights' => $nights,
            'service_fee' => $serviceFee,
            'tax' => $tax,
            'coupon_id' => $couponId,
            'discount_amount' => $discountAmount,
            'total_amount' => $finalTotal,
            'status' => 'booked',
            'payment_status' => $walletAmountUsed >= (float) $validated['total'] ? 'paid' : 'pending',
            'payment_method' => $validated['payment_method'],
            'notes' => $validated['special_requests'] ?? null,
        ]);

        // Deduct wallet balance if used
        if ($walletAmountUsed > 0) {
            $walletService = app(WalletService::class);
            $walletService->deductAmount(
                Auth::user(),
                $walletAmountUsed,
                $booking,
                "Wallet payment for booking #{$booking->id}"
            );
        }

        // Increment coupon usage count if coupon was used
        if ($couponId) {
            $coupon->incrementUsage();

            // Clear coupon from session after successful booking
            session()->forget('applied_coupon');
        }

        return redirect()->route('booking.confirmation', ['id' => $booking->id])
            ->with('success', 'Booking confirmed successfully!');
    }

    public function confirmation($id)
    {
        $booking = Booking::with(['bookingable', 'user', 'coupon', 'cancelledBy'])->findOrFail($id);

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
                $location = 'Room #' . ($room->room_number ?? 'N/A');
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

        $checkInDate = Carbon::parse($booking->check_in)->startOfDay();
        $checkOutDate = Carbon::parse($booking->check_out)->startOfDay();

        // Get nights from booking record (or calculate as fallback)
        $nights = $booking->nights ?? max(1, $checkInDate->diffInDays($checkOutDate) ?: 1);

        // Get customer info from guest_details or user
        $guestDetails = $booking->guest_details ?? [];
        $customerInfo = $guestDetails['customer'] ?? [];

        $customerName = $customerInfo['first_name'] ?? $booking->user->name ?? 'Guest';
        if (isset($customerInfo['last_name'])) {
            $customerName .= ' ' . $customerInfo['last_name'];
        }

        // Get price per night from booking record (or from property as fallback)
        $pricePerNight = $booking->price_per_night ?? 0;
        if (!$pricePerNight && $booking->bookingable) {
            if ($booking->bookingable_type === Yacht::class) {
                $pricePerNight = $booking->bookingable->price_per_hour ?? $booking->bookingable->price ?? 0;
            } else {
                $pricePerNight = $booking->bookingable->price_per_night ?? 0;
            }
        }

        // Get coupon and discount info
        $couponCode = null;
        $discountAmount = $booking->discount_amount ?? 0;
        if ($booking->coupon) {
            $couponCode = $booking->coupon->code;
        }

        // Check for wallet transaction
        $walletAmountUsed = 0;
        $walletTransaction = \App\Models\WalletTransaction::where('booking_id', $booking->id)
            ->where('type', 'debit')
            ->first();
        if ($walletTransaction) {
            $walletAmountUsed = abs($walletTransaction->amount);
        }

        $bookingData = (object) [
            'id' => $booking->id,
            'reference' => 'YHBS' . str_pad($booking->id, 6, '0', STR_PAD_LEFT),
            'property_image' => $propertyImage,
            'property_name' => $propertyName,
            'property_type' => $propertyType,
            'location' => $location,
            'check_in' => $checkInDate->format('M d, Y'),
            'check_out' => $checkOutDate->format('M d, Y'),
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
            'total' => $booking->total_amount ?? $booking->price,
            'price' => $booking->price,
            'price_per_night' => $pricePerNight,
            'service_fee' => $booking->service_fee ?? 0,
            'tax' => $booking->tax ?? 0,
            'discount_amount' => $discountAmount,
            'coupon_code' => $couponCode,
            'wallet_amount_used' => $walletAmountUsed,
            'created_at' => $booking->created_at->format('M d, Y'),
            // Cancellation info
            'cancellation_requested_at' => $booking->cancellation_requested_at,
            'cancellation_status' => $booking->cancellation_status,
            'cancellation_reason' => $booking->cancellation_reason,
            'cancelled_at' => $booking->cancelled_at,
            'refund_amount' => $booking->refund_amount,
            'refund_status' => $booking->refund_status,
        ];

        return view('frontend.booking-confirmation', ['booking' => $bookingData]);
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'booking_amount' => 'required|numeric',
            'price_per_night' => 'nullable|numeric',
            'nights' => 'nullable|integer',
            'property_type' => 'nullable|string',
            'property_id' => 'nullable|integer',
        ]);

        $couponService = new CouponService();
        $result = $couponService->validateCoupon(
            $request->coupon_code,
            $request->booking_amount,
            $request->price_per_night,
            $request->nights,
            null,
            $request->property_type,
            $request->property_id
        );

        // Debug logging
        \Log::info('Coupon Application', [
            'booking_amount' => $request->booking_amount,
            'price_per_night' => $request->price_per_night,
            'nights' => $request->nights,
            'valid' => $result['valid'] ?? false,
            'discount_amount' => $result['discount_amount'] ?? 0,
            'new_total' => $result['new_total'] ?? 0,
        ]);

        if ($result['valid']) {
            // Store only coupon info in session, calculate discount fresh on page
            session([
                'applied_coupon' => [
                    'code' => $result['coupon']->code,
                    'discount_type' => $result['coupon']->discount_type->value,
                    'discount_value' => $result['coupon']->discount_value,
                    'max_discount_amount' => $result['coupon']->max_discount_amount,
                ]
            ]);

            return back()->with('coupon_success', $result['message'] ?? 'Coupon applied successfully!');
        }

        return back()->withErrors(['coupon_code' => $result['error']])->withInput();
    }

    public function removeCoupon()
    {
        session()->forget('applied_coupon');
        return back()->with('coupon_success', 'Coupon removed successfully');
    }
}
