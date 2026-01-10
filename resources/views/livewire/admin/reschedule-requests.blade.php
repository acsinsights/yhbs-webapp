<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Booking;
use App\Enums\BookingStatusEnum;
use Illuminate\Support\Facades\Mail;
use App\Mail\BookingRescheduleApprovedMail;
use App\Mail\BookingRescheduleRejectedMail;
use Mary\Traits\Toast;

new class extends Component {
    use WithPagination, Toast;

    public bool $showApproveModal = false;
    public bool $showRejectModal = false;
    public bool $showReasonModal = false;
    public bool $showDetailsModal = false;
    public ?Booking $selectedBooking = null;
    public ?int $lastViewedBookingId = null;
    public float $rescheduleFee = 0;
    public string $rejectionReason = '';
    public string $selectedReason = '';
    public string $paymentMethod = 'wallet'; // 'wallet' or 'manual'
    public float $extraFee = 0;
    public string $extraFeeRemark = '';

    public function openReasonModal($reason): void
    {
        $this->selectedReason = $reason;
        $this->showReasonModal = true;
    }

    public function closeReasonModal(): void
    {
        $this->showReasonModal = false;
        $this->selectedReason = '';
    }

    public function openDetailsModal($bookingId): void
    {
        $this->selectedBooking = Booking::with(['user', 'bookingable'])->findOrFail($bookingId);
        $this->lastViewedBookingId = $bookingId;
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->selectedBooking = null;
    }

    public function openApproveModal($bookingId): void
    {
        $this->selectedBooking = Booking::with(['user', 'bookingable'])->findOrFail($bookingId);
        $this->rescheduleFee = $this->selectedBooking->reschedule_fee ?? $this->selectedBooking->calculateRescheduleFee();
        $this->paymentMethod = 'wallet';
        $this->showApproveModal = true;
    }
    public function isRequestedDateAvailable(): bool
    {
        if (!$this->selectedBooking || !$this->selectedBooking->new_check_in) {
            return true; // Can't determine, assume available
        }

        $newCheckIn = $this->selectedBooking->new_check_in;
        $bookingableType = $this->selectedBooking->bookingable_type;
        $bookingableId = $this->selectedBooking->bookingable_id;

        // Check for boats with buffer time and time slot overlap
        if ($bookingableType === 'App\\Models\\Boat') {
            $tripType = $this->selectedBooking->trip_type;

            // For boats, use requested_time_slot to calculate duration
            $requestedTimeSlot = $this->selectedBooking->requested_time_slot;
            if (empty($requestedTimeSlot)) {
                return true; // Can't determine without time slot
            }

            // Parse the requested time slot (e.g., "09:00 AM - 10:00 AM")
            if (preg_match('/(\d+):(\d+)\s*(AM|PM)\s*-\s*(\d+):(\d+)\s*(AM|PM)/', $requestedTimeSlot, $matches)) {
                $startHour = (int) $matches[1];
                $startMinute = (int) $matches[2];
                $startPeriod = $matches[3];
                $endHour = (int) $matches[4];
                $endMinute = (int) $matches[5];
                $endPeriod = $matches[6];

                // Convert to 24-hour format
                if ($startPeriod === 'PM' && $startHour !== 12) {
                    $startHour += 12;
                } elseif ($startPeriod === 'AM' && $startHour === 12) {
                    $startHour = 0;
                }

                if ($endPeriod === 'PM' && $endHour !== 12) {
                    $endHour += 12;
                } elseif ($endPeriod === 'AM' && $endHour === 12) {
                    $endHour = 0;
                }

                // Create Carbon instances for the exact time slot
                $newCheckInTime = $newCheckIn->copy()->setTime($startHour, $startMinute);
                $newCheckOutTime = $newCheckIn->copy()->setTime($endHour, $endMinute);
            } else {
                return true; // Can't parse time slot
            }

            $bufferMinutes = $this->selectedBooking->bookingable->buffer_time ?? 0;
            $endTimeWithBuffer = $newCheckOutTime->copy()->addMinutes($bufferMinutes);

            // For PRIVATE trips: Check if slot is completely free
            if ($tripType === 'private') {
                return !Booking::where('bookingable_type', 'App\\Models\\Boat')
                    ->where('bookingable_id', $bookingableId)
                    ->where('id', '!=', $this->selectedBooking->id)
                    ->whereIn('status', ['pending', 'booked', 'checked_in'])
                    ->whereDate('check_in', $newCheckIn->format('Y-m-d'))
                    ->where(function ($query) use ($newCheckInTime, $endTimeWithBuffer, $newCheckOutTime) {
                        $query->whereBetween('check_in', [$newCheckInTime, $endTimeWithBuffer])->orWhere(function ($q) use ($newCheckInTime, $newCheckOutTime) {
                            $q->where('check_in', '<=', $newCheckInTime)->where('check_in', '>=', $newCheckInTime->copy()->subHours(24));
                        });
                    })
                    ->exists();
            }

            // For PUBLIC trips: Check if enough seats are available
            if ($tripType === 'public') {
                $requestedSeats = ($this->selectedBooking->adults ?? 0) + ($this->selectedBooking->children ?? 0);
                $maxPassengers = $this->selectedBooking->bookingable->max_passengers ?? 0;

                // Get all bookings on the same date for this boat (public trips) - including those without time slot
                $allBookings = Booking::where('bookingable_type', 'App\\Models\\Boat')
                    ->where('bookingable_id', $bookingableId)
                    ->where('id', '!=', $this->selectedBooking->id)
                    ->where('trip_type', 'public')
                    ->whereIn('status', ['pending', 'booked', 'checked_in'])
                    ->whereDate('check_in', $newCheckIn->format('Y-m-d'))
                    ->get();

                // Calculate booked seats for overlapping time slots
                $bookedSeats = 0;
                foreach ($allBookings as $existingBooking) {
                    $existingSlot = $existingBooking->requested_time_slot;

                    // Try to get time slot from guest_details if requested_time_slot is empty
                    if (empty($existingSlot) && isset($existingBooking->guest_details['boat_details']['start_time'])) {
                        $startTime = $existingBooking->guest_details['boat_details']['start_time'];
                        $duration = $existingBooking->guest_details['boat_details']['duration'] ?? 1;

                        // Calculate end time
                        if ($startTime) {
                            try {
                                $start = \Carbon\Carbon::createFromFormat('H:i', $startTime);
                                $end = $start->copy()->addHours((int) $duration);
                                $existingSlot = $start->format('h:i A') . ' - ' . $end->format('h:i A');
                            } catch (\Exception $e) {
                                // If parsing fails, skip this booking with warning
                                continue;
                            }
                        }
                    }

                    // Skip if still no time slot info available
                    if (empty($existingSlot)) {
                        continue;
                    }

                    // Parse existing booking time slot
                    if (preg_match('/(\d+):(\d+)\s*(AM|PM)\s*-\s*(\d+):(\d+)\s*(AM|PM)/', $existingSlot, $existingMatches)) {
                        $existingStartHour = (int) $existingMatches[1];
                        $existingStartMinute = (int) $existingMatches[2];
                        $existingStartPeriod = $existingMatches[3];
                        $existingEndHour = (int) $existingMatches[4];
                        $existingEndMinute = (int) $existingMatches[5];
                        $existingEndPeriod = $existingMatches[6];

                        // Convert to 24-hour format
                        if ($existingStartPeriod === 'PM' && $existingStartHour !== 12) {
                            $existingStartHour += 12;
                        } elseif ($existingStartPeriod === 'AM' && $existingStartHour === 12) {
                            $existingStartHour = 0;
                        }

                        if ($existingEndPeriod === 'PM' && $existingEndHour !== 12) {
                            $existingEndHour += 12;
                        } elseif ($existingEndPeriod === 'AM' && $existingEndHour === 12) {
                            $existingEndHour = 0;
                        }

                        // Create time values for comparison (minutes from midnight)
                        $newStartMinutes = $startHour * 60 + $startMinute;
                        $newEndMinutes = $endHour * 60 + $endMinute;
                        $existingStartMinutes = $existingStartHour * 60 + $existingStartMinute;
                        $existingEndMinutes = $existingEndHour * 60 + $existingEndMinute;

                        // Check if time slots overlap
                        // Two slots overlap if: (StartA < EndB) AND (EndA > StartB)
                        $overlaps = $newStartMinutes < $existingEndMinutes && $newEndMinutes > $existingStartMinutes;

                        if ($overlaps) {
                            $bookedSeats += ($existingBooking->adults ?? 0) + ($existingBooking->children ?? 0);
                        }
                    }
                }

                $availableSeats = $maxPassengers - $bookedSeats;
                return $availableSeats >= $requestedSeats;
            }
        }

        // Check for rooms
        if ($bookingableType === 'App\\Models\\Room') {
            $newCheckOut = $this->selectedBooking->new_check_out;
            if (!$newCheckOut) {
                return true; // Can't determine without check_out
            }

            return !Booking::where('bookingable_type', 'App\\Models\\Room')
                ->where('bookingable_id', $bookingableId)
                ->where('id', '!=', $this->selectedBooking->id)
                ->whereIn('status', ['pending', 'booked', 'checked_in'])
                ->where(function ($query) use ($newCheckIn, $newCheckOut) {
                    $query
                        ->whereBetween('check_in', [$newCheckIn, $newCheckOut])
                        ->orWhereBetween('check_out', [$newCheckIn, $newCheckOut])
                        ->orWhere(function ($q) use ($newCheckIn, $newCheckOut) {
                            $q->where('check_in', '<=', $newCheckIn)->where('check_out', '>=', $newCheckOut);
                        });
                })
                ->exists();
        }

        // Check for houses
        if ($bookingableType === 'App\\Models\\House') {
            $newCheckOut = $this->selectedBooking->new_check_out;
            if (!$newCheckOut) {
                return true; // Can't determine without check_out
            }

            return !Booking::where('bookingable_type', 'App\\Models\\House')
                ->where('bookingable_id', $bookingableId)
                ->where('id', '!=', $this->selectedBooking->id)
                ->whereIn('status', ['pending', 'booked', 'checked_in'])
                ->where(function ($query) use ($newCheckIn, $newCheckOut) {
                    $query
                        ->whereBetween('check_in', [$newCheckIn, $newCheckOut])
                        ->orWhereBetween('check_out', [$newCheckIn, $newCheckOut])
                        ->orWhere(function ($q) use ($newCheckIn, $newCheckOut) {
                            $q->where('check_in', '<=', $newCheckIn)->where('check_out', '>=', $newCheckOut);
                        });
                })
                ->exists();
        }

        return true; // Unknown type, assume available
    }

    public function getAvailabilityMessage(): ?string
    {
        if (!$this->selectedBooking || !$this->selectedBooking->new_check_in) {
            return null;
        }

        $bookingableType = $this->selectedBooking->bookingable_type;

        if ($bookingableType === 'App\\Models\\Boat') {
            $tripType = $this->selectedBooking->trip_type;
            $newCheckIn = $this->selectedBooking->new_check_in;
            $bookingableId = $this->selectedBooking->bookingable_id;
            $requestedTimeSlot = $this->selectedBooking->requested_time_slot;

            if (empty($requestedTimeSlot)) {
                return null;
            }

            if ($tripType === 'private') {
                $hasConflict = Booking::where('bookingable_type', 'App\\Models\\Boat')
                    ->where('bookingable_id', $bookingableId)
                    ->where('id', '!=', $this->selectedBooking->id)
                    ->whereIn('status', ['pending', 'booked', 'checked_in'])
                    ->whereDate('check_in', $newCheckIn->format('Y-m-d'))
                    ->where('requested_time_slot', $requestedTimeSlot)
                    ->exists();

                if ($hasConflict) {
                    return "This time slot ({$requestedTimeSlot}) has already been booked by another customer for a private trip. Please reject this request and ask the customer to choose a different time slot.";
                }
            } elseif ($tripType === 'public') {
                $requestedSeats = ($this->selectedBooking->adults ?? 0) + ($this->selectedBooking->children ?? 0);
                $maxPassengers = $this->selectedBooking->bookingable->max_passengers ?? 0;

                // Parse requested time slot
                if (!preg_match('/(\d+):(\d+)\s*(AM|PM)\s*-\s*(\d+):(\d+)\s*(AM|PM)/', $requestedTimeSlot, $matches)) {
                    return null;
                }

                $startHour = (int) $matches[1];
                $startMinute = (int) $matches[2];
                $startPeriod = $matches[3];
                $endHour = (int) $matches[4];
                $endMinute = (int) $matches[5];
                $endPeriod = $matches[6];

                // Convert to 24-hour format
                if ($startPeriod === 'PM' && $startHour !== 12) {
                    $startHour += 12;
                } elseif ($startPeriod === 'AM' && $startHour === 12) {
                    $startHour = 0;
                }

                if ($endPeriod === 'PM' && $endHour !== 12) {
                    $endHour += 12;
                } elseif ($endPeriod === 'AM' && $endHour === 12) {
                    $endHour = 0;
                }

                $newStartMinutes = $startHour * 60 + $startMinute;
                $newEndMinutes = $endHour * 60 + $endMinute;

                // Get all bookings and check for overlaps (including those without time slot for debugging)
                $allBookings = Booking::where('bookingable_type', 'App\\Models\\Boat')
                    ->where('bookingable_id', $bookingableId)
                    ->where('id', '!=', $this->selectedBooking->id)
                    ->where('trip_type', 'public')
                    ->whereIn('status', ['pending', 'booked', 'checked_in'])
                    ->whereDate('check_in', $newCheckIn->format('Y-m-d'))
                    ->get();

                $bookedSeats = 0;
                $overlappingBookings = [];
                $skippedBookings = []; // For debugging

                foreach ($allBookings as $existingBooking) {
                    $existingSlot = $existingBooking->requested_time_slot;

                    // Try to get time slot from guest_details if requested_time_slot is empty
                    if (empty($existingSlot) && isset($existingBooking->guest_details['boat_details']['start_time'])) {
                        $startTime = $existingBooking->guest_details['boat_details']['start_time'];
                        $duration = $existingBooking->guest_details['boat_details']['duration'] ?? 1;

                        // Calculate end time
                        if ($startTime) {
                            try {
                                $start = \Carbon\Carbon::createFromFormat('H:i', $startTime);
                                $end = $start->copy()->addHours((int) $duration);
                                $existingSlot = $start->format('h:i A') . ' - ' . $end->format('h:i A');
                            } catch (\Exception $e) {
                                // If parsing fails, add to skipped
                                $skippedBookings[] = [
                                    'booking_id' => $existingBooking->booking_id,
                                    'seats' => ($existingBooking->adults ?? 0) + ($existingBooking->children ?? 0),
                                    'reason' => 'Failed to parse time from guest_details',
                                ];
                                continue;
                            }
                        }
                    }

                    // Track bookings without time slot
                    if (empty($existingSlot)) {
                        $skippedBookings[] = [
                            'booking_id' => $existingBooking->booking_id,
                            'seats' => ($existingBooking->adults ?? 0) + ($existingBooking->children ?? 0),
                            'reason' => 'No time slot',
                        ];
                        continue;
                    }

                    if (preg_match('/(\d+):(\d+)\s*(AM|PM)\s*-\s*(\d+):(\d+)\s*(AM|PM)/', $existingSlot, $existingMatches)) {
                        $existingStartHour = (int) $existingMatches[1];
                        $existingStartMinute = (int) $existingMatches[2];
                        $existingStartPeriod = $existingMatches[3];
                        $existingEndHour = (int) $existingMatches[4];
                        $existingEndMinute = (int) $existingMatches[5];
                        $existingEndPeriod = $existingMatches[6];

                        if ($existingStartPeriod === 'PM' && $existingStartHour !== 12) {
                            $existingStartHour += 12;
                        } elseif ($existingStartPeriod === 'AM' && $existingStartHour === 12) {
                            $existingStartHour = 0;
                        }

                        if ($existingEndPeriod === 'PM' && $existingEndHour !== 12) {
                            $existingEndHour += 12;
                        } elseif ($existingEndPeriod === 'AM' && $existingEndHour === 12) {
                            $existingEndHour = 0;
                        }

                        $existingStartMinutes = $existingStartHour * 60 + $existingStartMinute;
                        $existingEndMinutes = $existingEndHour * 60 + $existingEndMinute;

                        // Check overlap
                        if ($newStartMinutes < $existingEndMinutes && $newEndMinutes > $existingStartMinutes) {
                            $seats = ($existingBooking->adults ?? 0) + ($existingBooking->children ?? 0);
                            $bookedSeats += $seats;
                            $overlappingBookings[] = [
                                'slot' => $existingSlot,
                                'seats' => $seats,
                                'booking_id' => $existingBooking->booking_id,
                            ];
                        }
                    }
                }

                $availableSeats = $maxPassengers - $bookedSeats;

                if ($availableSeats < $requestedSeats) {
                    $overlappingInfo = '';
                    if (!empty($overlappingBookings)) {
                        $overlappingInfo = "\n\nOverlapping bookings:\n";
                        foreach ($overlappingBookings as $overlap) {
                            $overlappingInfo .= "• Booking #{$overlap['booking_id']}: {$overlap['slot']} ({$overlap['seats']} seats)\n";
                        }
                    }
                    if (!empty($skippedBookings)) {
                        $overlappingInfo .= "\n⚠️ WARNING: Some bookings skipped (no time slot data):\n";
                        foreach ($skippedBookings as $skipped) {
                            $overlappingInfo .= "• Booking #{$skipped['booking_id']}: {$skipped['seats']} seats - {$skipped['reason']}\n";
                        }
                    }
                    return "⚠️ Time Slot: {$requestedTimeSlot} - Only {$availableSeats} seat(s) available out of {$requestedSeats} requested. The requested time slot does not have enough seats available. Please reject this request.{$overlappingInfo}";
                }

                $overlappingInfo = '';
                if (!empty($overlappingBookings)) {
                    $overlappingInfo = "\n\nOverlapping bookings found:\n";
                    foreach ($overlappingBookings as $overlap) {
                        $overlappingInfo .= "• Booking #{$overlap['booking_id']}: {$overlap['slot']} ({$overlap['seats']} seats)\n";
                    }
                }

                // Add debug info for skipped bookings
                if (!empty($skippedBookings)) {
                    $overlappingInfo .= "\n⚠️ WARNING: Some bookings on this date have no time slot data:\n";
                    foreach ($skippedBookings as $skipped) {
                        $overlappingInfo .= "• Booking #{$skipped['booking_id']}: {$skipped['seats']} seats\n";
                    }
                    $overlappingInfo .= "These bookings were NOT counted in availability calculation. Please verify manually.\n";
                }

                return "✓ Time Slot: {$requestedTimeSlot} - Available seats: {$availableSeats} out of {$maxPassengers} total seats (Customer requested: {$requestedSeats} seats). This reschedule request can be approved.{$overlappingInfo}";
            }
        }

        return null;
    }

    public function closeApproveModal(): void
    {
        $this->showApproveModal = false;
        $this->selectedBooking = null;
        $this->rescheduleFee = 0;
        $this->paymentMethod = 'wallet';
        $this->extraFee = 0;
        $this->extraFeeRemark = '';
        $this->resetValidation();
    }

    public function openRejectModal($bookingId): void
    {
        $this->selectedBooking = Booking::with(['user', 'bookingable'])->findOrFail($bookingId);
        $this->showRejectModal = true;
    }

    public function getBookingDuration($booking): string
    {
        if (!$booking->check_in || !$booking->check_out) {
            return 'N/A';
        }

        // Calculate duration in hours
        $duration = $booking->check_in->diffInHours($booking->check_out);

        if ($duration === 0) {
            $minutes = $booking->check_in->diffInMinutes($booking->check_out);
            if ($minutes === 15) {
                return '15 Minutes';
            } elseif ($minutes === 30) {
                return '30 Minutes';
            } elseif ($minutes < 60) {
                return $minutes . ' Minutes';
            }
            $duration = 1;
        }

        return $duration === 1 ? '1 Hour' : $duration . ' Hours';
    }

    public function getRequestedDuration($booking): string
    {
        $timeSlot = $booking->requested_time_slot ?? '';
        if (empty($timeSlot)) {
            return 'N/A';
        }

        // Parse time slot to get duration
        if (preg_match('/(\d+):(\d+)\s*(AM|PM)\s*-\s*(\d+):(\d+)\s*(AM|PM)/', $timeSlot, $matches)) {
            $startHour = (int) $matches[1];
            $startPeriod = $matches[3];
            $endHour = (int) $matches[4];
            $endPeriod = $matches[6];

            if ($startPeriod === 'PM' && $startHour !== 12) {
                $startHour += 12;
            } elseif ($startPeriod === 'AM' && $startHour === 12) {
                $startHour = 0;
            }

            if ($endPeriod === 'PM' && $endHour !== 12) {
                $endHour += 12;
            } elseif ($endPeriod === 'AM' && $endHour === 12) {
                $endHour = 0;
            }

            $duration = $endHour - $startHour;

            if ($duration === 0) {
                $startMin = (int) $matches[2];
                $endMin = (int) $matches[5];
                $minutes = $endMin - $startMin;
                if ($minutes === 15) {
                    return '15 Minutes';
                } elseif ($minutes === 30) {
                    return '30 Minutes';
                }
            }

            return $duration === 1 ? '1 Hour' : $duration . ' Hours';
        }

        return 'N/A';
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->selectedBooking = null;
        $this->rejectionReason = '';
        $this->resetValidation();
    }

    public function approveReschedule(): void
    {
        $this->validate(
            [
                'rescheduleFee' => ['required', 'numeric', 'min:0'],
                'paymentMethod' => ['required', 'in:wallet,manual'],
                'extraFee' => ['nullable', 'numeric', 'min:0'],
                'extraFeeRemark' => ['nullable', 'string', 'max:500'],
            ],
            [
                'rescheduleFee.required' => 'Reschedule fee is required.',
                'rescheduleFee.min' => 'Reschedule fee cannot be negative.',
                'paymentMethod.required' => 'Please select a payment method.',
                'extraFee.min' => 'Extra fee cannot be negative.',
            ],
        );

        if (!$this->selectedBooking) {
            $this->error('Booking not found.');
            return;
        }

        // Validate that the requested time slot is still available for all booking types
        $newCheckIn = $this->selectedBooking->new_check_in;
        $newCheckOut = $this->selectedBooking->new_check_out;
        $bookingableType = $this->selectedBooking->bookingable_type;
        $bookingableId = $this->selectedBooking->bookingable_id;

        if ($newCheckIn && $newCheckOut) {
            $conflictingBooking = null;

            // Check for boats with buffer time
            if ($bookingableType === 'App\\Models\\Boat') {
                $bufferMinutes = $this->selectedBooking->bookingable->buffer_time ?? 0;
                $endTimeWithBuffer = $newCheckOut->copy()->addMinutes($bufferMinutes);

                $conflictingBooking = Booking::where('bookingable_type', 'App\\Models\\Boat')
                    ->where('bookingable_id', $bookingableId)
                    ->where('id', '!=', $this->selectedBooking->id)
                    ->where('booking_status', '!=', 'cancelled')
                    ->where(function ($query) use ($newCheckIn, $endTimeWithBuffer) {
                        $query
                            ->whereBetween('check_in', [$newCheckIn, $endTimeWithBuffer])
                            ->orWhereBetween('check_out', [$newCheckIn, $endTimeWithBuffer])
                            ->orWhere(function ($q) use ($newCheckIn, $endTimeWithBuffer) {
                                $q->where('check_in', '<=', $newCheckIn)->where('check_out', '>=', $endTimeWithBuffer);
                            });
                    })
                    ->exists();

                if ($conflictingBooking) {
                    $bufferText = $bufferMinutes > 0 ? " (including {$bufferMinutes} min buffer time)" : '';
                    $this->error("The requested time slot is no longer available{$bufferText}. Please reject this request and ask customer to submit a new one.");
                    return;
                }
            }

            // Check for rooms
            if ($bookingableType === 'App\\Models\\Room') {
                $conflictingBooking = Booking::where('bookingable_type', 'App\\Models\\Room')
                    ->where('bookingable_id', $bookingableId)
                    ->where('id', '!=', $this->selectedBooking->id)
                    ->whereIn('status', ['pending', 'booked', 'checked_in'])
                    ->where(function ($query) use ($newCheckIn, $newCheckOut) {
                        $query
                            ->whereBetween('check_in', [$newCheckIn, $newCheckOut])
                            ->orWhereBetween('check_out', [$newCheckIn, $newCheckOut])
                            ->orWhere(function ($q) use ($newCheckIn, $newCheckOut) {
                                $q->where('check_in', '<=', $newCheckIn)->where('check_out', '>=', $newCheckOut);
                            });
                    })
                    ->exists();

                if ($conflictingBooking) {
                    $this->error('The requested dates are no longer available. Another booking exists for this room during the requested period. Please reject this request and ask customer to submit a new one.');
                    return;
                }
            }

            // Check for houses
            if ($bookingableType === 'App\\Models\\House') {
                $conflictingBooking = Booking::where('bookingable_type', 'App\\Models\\House')
                    ->where('bookingable_id', $bookingableId)
                    ->where('id', '!=', $this->selectedBooking->id)
                    ->whereIn('status', ['pending', 'booked', 'checked_in'])
                    ->where(function ($query) use ($newCheckIn, $newCheckOut) {
                        $query
                            ->whereBetween('check_in', [$newCheckIn, $newCheckOut])
                            ->orWhereBetween('check_out', [$newCheckIn, $newCheckOut])
                            ->orWhere(function ($q) use ($newCheckIn, $newCheckOut) {
                                $q->where('check_in', '<=', $newCheckIn)->where('check_out', '>=', $newCheckOut);
                            });
                    })
                    ->exists();

                if ($conflictingBooking) {
                    $this->error('The requested dates are no longer available. Another booking exists for this house during the requested period. Please reject this request and ask customer to submit a new one.');
                    return;
                }
            }
        }

        $user = $this->selectedBooking->user;
        $walletBalance = $user->wallet_balance ?? 0;

        // Calculate total fees to be deducted
        $totalFees = $this->rescheduleFee + ($this->extraFee ?? 0);

        // Validate wallet balance if wallet payment is selected
        if ($this->paymentMethod === 'wallet' && $totalFees > 0) {
            if ($walletBalance < $totalFees) {
                $this->error('Customer does not have sufficient wallet balance (' . currency_format($walletBalance) . '). Total fees: ' . currency_format($totalFees) . '. Please select "Collect Manually" or reduce the fees.');
                return;
            }
        }

        // Use database transaction
        \DB::transaction(function () use ($user, $totalFees) {
            // Deduct fees from wallet if wallet payment selected
            if ($this->paymentMethod === 'wallet' && $totalFees > 0) {
                $userLocked = \App\Models\User::lockForUpdate()->find($user->id);
                $balanceBefore = $userLocked->wallet_balance ?? 0;

                $userLocked->wallet_balance = $balanceBefore - $totalFees;
                $userLocked->save();

                $balanceAfter = $userLocked->wallet_balance;

                // Create transaction for reschedule fee
                if ($this->rescheduleFee > 0) {
                    \App\Models\WalletTransaction::create([
                        'user_id' => $user->id,
                        'amount' => $this->rescheduleFee,
                        'type' => 'debit',
                        'balance_before' => $balanceBefore,
                        'balance_after' => $balanceBefore - $this->rescheduleFee,
                        'description' => 'Reschedule fee for booking #' . $this->selectedBooking->id,
                        'booking_id' => $this->selectedBooking->id,
                    ]);
                }

                // Create transaction for extra fee
                if ($this->extraFee > 0) {
                    \App\Models\WalletTransaction::create([
                        'user_id' => $user->id,
                        'amount' => $this->extraFee,
                        'type' => 'debit',
                        'balance_before' => $balanceBefore - $this->rescheduleFee,
                        'balance_after' => $balanceAfter,
                        'description' => 'Extra fee for booking #' . $this->selectedBooking->id . ($this->extraFeeRemark ? ' - ' . $this->extraFeeRemark : ''),
                        'booking_id' => $this->selectedBooking->id,
                    ]);
                }
            }

            // Update booking dates and status
            $updateData = [
                'reschedule_status' => 'approved',
                'check_in' => $this->selectedBooking->new_check_in,
                'reschedule_fee' => $this->rescheduleFee,
                'rescheduled_by' => auth()->id(),
                'extra_fee' => $this->extraFee > 0 ? $this->extraFee : null,
                'extra_fee_remark' => $this->extraFee > 0 ? $this->extraFeeRemark : null,
            ];

            // For boats, calculate check_out from requested_time_slot
            if ($this->selectedBooking->bookingable_type === 'App\\Models\\Boat' && $this->selectedBooking->requested_time_slot) {
                $requestedTimeSlot = $this->selectedBooking->requested_time_slot;

                // Parse time slot to get end time
                if (preg_match('/(\d+):(\d+)\s*(AM|PM)\s*-\s*(\d+):(\d+)\s*(AM|PM)/', $requestedTimeSlot, $matches)) {
                    $endHour = (int) $matches[4];
                    $endMinute = (int) $matches[5];
                    $endPeriod = $matches[6];

                    // Convert to 24-hour format
                    if ($endPeriod === 'PM' && $endHour !== 12) {
                        $endHour += 12;
                    } elseif ($endPeriod === 'AM' && $endHour === 12) {
                        $endHour = 0;
                    }

                    // Set check_out time based on requested_time_slot
                    $updateData['check_out'] = $this->selectedBooking->new_check_in->copy()->setTime($endHour, $endMinute);
                } else {
                    // Fallback: set check_out same as new_check_out (might be null)
                    $updateData['check_out'] = $this->selectedBooking->new_check_out;
                }
            } else {
                // For rooms/houses, use new_check_out
                $updateData['check_out'] = $this->selectedBooking->new_check_out;
            }

            $this->selectedBooking->update($updateData);

            // Create notification for the customer
            \App\Models\UserNotification::create([
                'user_id' => $this->selectedBooking->user_id,
                'title' => 'Booking Reschedule Approved',
                'message' => 'Your reschedule request for booking #' . $this->selectedBooking->booking_id . ' has been approved.' . ($this->extraFee > 0 ? ' Extra fee: ' . currency_format($this->extraFee) . ($this->extraFeeRemark ? ' (' . $this->extraFeeRemark . ')' : '') : ''),
                'type' => 'success',
                'link' => route('customer.booking.details', $this->selectedBooking->id),
            ]);
        });

        try {
            Mail::to($this->selectedBooking->user->email)->send(new BookingRescheduleApprovedMail($this->selectedBooking));
        } catch (\Exception $e) {
            \Log::error('Failed to send reschedule approval email: ' . $e->getMessage());
        }

        $totalFees = $this->rescheduleFee + ($this->extraFee ?? 0);
        $successMessage = 'Reschedule approved successfully. Booking dates have been updated.';
        if ($this->paymentMethod === 'wallet' && $totalFees > 0) {
            $successMessage .= ' Total fees of ' . currency_format($totalFees) . ' have been deducted from customer wallet.';
        } elseif ($this->paymentMethod === 'manual' && $totalFees > 0) {
            $successMessage .= ' Total fees of ' . currency_format($totalFees) . ' will be collected manually from customer.';
        }

        $this->success($successMessage);
        $this->closeApproveModal();
        $this->resetPage();
    }

    public function rejectReschedule(): void
    {
        $this->validate(['rejectionReason' => 'required|string|min:10|max:500']);

        if (!$this->selectedBooking) {
            $this->error('Booking not found.');
            return;
        }

        $this->selectedBooking->update([
            'reschedule_status' => 'rejected',
            'reschedule_reason' => $this->selectedBooking->reschedule_reason . "\n\nRejection Reason: " . $this->rejectionReason,
        ]);

        try {
            Mail::to($this->selectedBooking->user->email)->send(new BookingRescheduleRejectedMail($this->selectedBooking, $this->rejectionReason));
        } catch (\Exception $e) {
            \Log::error('Failed to send reschedule rejection email: ' . $e->getMessage());
        }

        $this->success('Reschedule request rejected.');
        $this->closeRejectModal();
        $this->resetPage();
    }

    public function with(): array
    {
        return [
            'rescheduleRequests' => Booking::whereNotNull('reschedule_requested_at')
                ->where('reschedule_status', 'pending')
                ->with(['user', 'bookingable'])
                ->orderBy('reschedule_requested_at', 'desc')
                ->paginate(15),
        ];
    }
}; ?>

<div>
    <x-header title="Reschedule Requests" separator>
        <x-slot:middle class="!justify-end">
            <x-badge value="{{ $rescheduleRequests->total() }} Pending" class="badge-warning" />
        </x-slot:middle>
    </x-header>

    @if ($rescheduleRequests->count() > 0)
        <x-card>
            <x-table :headers="[
                ['key' => 'customer', 'label' => 'Customer'],
                ['key' => 'property', 'label' => 'Property'],
                ['key' => 'fee', 'label' => 'Fee'],
                ['key' => 'requested', 'label' => 'Requested On'],
                ['key' => 'view', 'label' => ''],
                ['key' => 'actions', 'label' => 'Actions'],
            ]" :rows="$rescheduleRequests" with-pagination>

                @scope('cell_customer', $booking)
                    @php
                        $bookingType = match (true) {
                            $booking->bookingable instanceof \App\Models\House => 'house',
                            $booking->bookingable instanceof \App\Models\Room => 'room',
                            $booking->bookingable instanceof \App\Models\Boat => 'boat',
                            default => 'house',
                        };
                        $detailsRoute = route('admin.bookings.' . $bookingType . '.show', $booking->id);
                    @endphp
                    <div>
                        <a href="{{ $detailsRoute }}" class="font-semibold text-primary hover:underline">
                            {{ $booking->user->name ?? 'N/A' }}
                        </a>
                        <div class="text-xs text-gray-500">{{ $booking->user->email ?? 'N/A' }}</div>
                        <div class="text-xs text-info mt-1">
                            <x-icon name="o-wallet" class="w-3 h-3 inline" />
                            Wallet: {{ currency_format($booking->user->wallet_balance ?? 0) }}
                        </div>
                    </div>
                @endscope

                @scope('cell_property', $booking)
                    <div>
                        <div class="font-semibold">{{ $booking->bookingable->name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-500">{{ class_basename($booking->bookingable_type) }}</div>
                    </div>
                @endscope

                @scope('cell_fee', $booking)
                    <strong class="text-warning">{{ currency_format($booking->reschedule_fee ?? 0) }}</strong>
                @endscope

                @scope('cell_requested', $booking)
                    <div>
                        <div class="text-sm">{{ $booking->reschedule_requested_at->format('d M Y') }}</div>
                        <div class="text-xs text-gray-500">{{ $booking->reschedule_requested_at->format('H:i A') }}</div>
                    </div>
                @endscope

                @scope('cell_view', $booking)
                    <x-button icon="o-eye"
                        class="btn-sm btn-ghost btn-circle {{ $this->lastViewedBookingId === $booking->id ? 'ring-2 ring-success' : '' }}"
                        wire:click="openDetailsModal({{ $booking->id }})" tooltip="View Details" />
                @endscope

                @scope('cell_actions', $booking)
                    <div class="flex gap-2">
                        <x-button icon="o-check-circle" class="btn-sm btn-success"
                            wire:click="openApproveModal({{ $booking->id }})" tooltip="Approve" />
                        <x-button icon="o-x-circle" class="btn-sm btn-error"
                            wire:click="openRejectModal({{ $booking->id }})" tooltip="Reject" />
                    </div>
                @endscope
            </x-table>
        </x-card>
    @else
        <x-card>
            <div class="text-center py-12">
                <x-icon name="o-inbox" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                <h3 class="text-lg font-semibold mb-2">No Pending Requests</h3>
                <p class="text-gray-500">All reschedule requests have been processed.</p>
            </div>
        </x-card>
    @endif

    <!-- Approve Modal -->
    <x-modal wire:model="showApproveModal" title="Approve Reschedule Request" separator>
        @if ($selectedBooking)
            <div class="space-y-4">
                <x-alert icon="o-information-circle" class="alert-info mb-4">
                    Booking #{{ $selectedBooking->id }} - {{ $selectedBooking->bookingable->name ?? 'N/A' }}
                    @if ($selectedBooking->trip_type)
                        <span class="badge badge-sm ml-2">
                            {{ ucfirst($selectedBooking->trip_type) }} Trip
                        </span>
                    @endif
                </x-alert>

                @if (!$this->isRequestedDateAvailable())
                    <x-alert icon="o-exclamation-triangle" class="alert-error mb-4">
                        <div>
                            <p class="font-bold">⚠️ Requested slot is NOT available!</p>
                            <p class="text-sm mt-1">
                                @if ($this->getAvailabilityMessage())
                                    {{ $this->getAvailabilityMessage() }}
                                @else
                                    Another booking already exists for this
                                    {{ $selectedBooking->bookingable_type === 'App\\Models\\Boat' ? 'boat' : ($selectedBooking->bookingable_type === 'App\\Models\\Room' ? 'room' : 'house') }}
                                    during the requested time period. Please reject this request and ask the customer
                                    to
                                    choose different dates.
                                @endif
                            </p>
                        </div>
                    </x-alert>
                @elseif (
                    $this->getAvailabilityMessage() &&
                        $selectedBooking->bookingable_type === 'App\\Models\\Boat' &&
                        $selectedBooking->trip_type === 'public')
                    <x-alert icon="o-information-circle" class="alert-success mb-4">
                        <div>
                            <p class="font-bold">✓ Availability Status</p>
                            <p class="text-sm mt-1">
                                {{ $this->getAvailabilityMessage() }}
                            </p>
                        </div>
                    </x-alert>
                @endif

                <div class="grid grid-cols-2 gap-4 mb-4">
                    @if ($selectedBooking->bookingable instanceof \App\Models\Boat)
                        <div>
                            <h6 class="font-semibold mb-2">Current Details:</h6>
                            <div class="text-sm">Date: {{ $selectedBooking->check_in->format('d M Y') }}</div>
                            <div class="text-sm">Duration: {{ $this->getBookingDuration($selectedBooking) }}</div>
                        </div>
                        <div>
                            <h6 class="font-semibold mb-2">New Details:</h6>
                            <div class="text-sm font-semibold text-success">Date:
                                {{ $selectedBooking->new_check_in?->format('d M Y') ?? 'N/A' }}</div>
                            <div class="text-sm font-semibold text-primary">Duration:
                                {{ $this->getRequestedDuration($selectedBooking) }}</div>
                        </div>
                    @else
                        <div>
                            <h6 class="font-semibold mb-2">Current Dates:</h6>
                            <div class="text-sm">Check-in: {{ $selectedBooking->check_in->format('d M Y') }}</div>
                            <div class="text-sm">Check-out: {{ $selectedBooking->check_out->format('d M Y') }}</div>
                        </div>
                        <div>
                            <h6 class="font-semibold mb-2">New Dates:</h6>
                            <div class="text-sm font-semibold text-success">Check-in:
                                {{ $selectedBooking->new_check_in?->format('d M Y') ?? 'N/A' }}</div>
                            <div class="text-sm font-semibold text-error">Check-out:
                                {{ $selectedBooking->new_check_out?->format('d M Y') ?? 'N/A' }}</div>
                        </div>
                    @endif
                </div>

                <x-input label="Reschedule Fee" wire:model="rescheduleFee" type="number" step="0.01"
                    prefix="{{ currency_symbol() }}" inline />

                <x-input label="Extra Fee (Optional)" wire:model="extraFee" type="number" step="0.01" min="0"
                    prefix="{{ currency_symbol() }}" hint="Any additional fees for the reschedule" inline />

                <x-textarea label="Extra Fee Remark (Optional)" wire:model="extraFeeRemark" rows="2"
                    placeholder="Reason for extra fee..." hint="This will be shown to the customer" />

                <x-alert icon="o-information-circle" class="alert-info">
                    Customer Wallet Balance:
                    <strong>{{ currency_format($selectedBooking->user->wallet_balance ?? 0) }}</strong>
                </x-alert>

                <div class="mb-4">
                    <label class="block font-semibold mb-2">Payment Method:</label>
                    <div class="space-y-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="paymentMethod" value="wallet"
                                class="radio radio-primary mr-2">
                            <div>
                                <span class="font-medium">Deduct from Wallet</span>
                                <p class="text-xs text-gray-500">Fee will be automatically deducted from customer's
                                    wallet balance</p>
                            </div>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="paymentMethod" value="manual"
                                class="radio radio-primary mr-2">
                            <div>
                                <span class="font-medium">Collect Manually / Waive Fee</span>
                                <p class="text-xs text-gray-500">Collect payment manually from customer or waive the
                                    fee
                                </p>
                            </div>
                        </label>
                    </div>
                </div>

                @if ($paymentMethod === 'wallet')
                    <x-alert icon="o-exclamation-triangle" class="alert-warning">
                        The reschedule fee will be deducted from the customer's wallet and booking dates will be updated
                        immediately.
                    </x-alert>
                @else
                    <x-alert icon="o-information-circle" class="alert-info">
                        Booking dates will be updated. Fee collection is your responsibility - coordinate with customer
                        directly.
                    </x-alert>
                @endif
            </div>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.closeApproveModal()" />
                <x-button label="Approve & Update" class="btn-success" wire:click="approveReschedule" spinner
                    :disabled="!$this->isRequestedDateAvailable()" />
            </x-slot:actions>
        @endif
    </x-modal>

    <!-- Reject Modal -->
    <x-modal wire:model="showRejectModal" title="Reject Reschedule Request" separator>
        @if ($selectedBooking)
            <div class="space-y-4">
                <x-alert icon="o-information-circle" class="alert-info mb-4">
                    Booking #{{ $selectedBooking->id }} - {{ $selectedBooking->bookingable->name ?? 'N/A' }}
                </x-alert>

                <x-textarea label="Rejection Reason" wire:model="rejectionReason" rows="4"
                    placeholder="Please provide a clear reason for rejecting this reschedule request..."
                    hint="Customer will receive this message via email" />

                <x-alert icon="o-exclamation-triangle" class="alert-error">
                    This action will notify the customer that their reschedule request has been declined.
                </x-alert>
            </div>

            <x-slot:actions>
                <x-button label="Cancel" @click="$wire.closeRejectModal()" />
                <x-button label="Reject Request" class="btn-error" wire:click="rejectReschedule" spinner />
            </x-slot:actions>
        @endif
    </x-modal>

    <!-- Reason View Modal -->
    <x-modal wire:model="showReasonModal" title="Reschedule Reason" separator>
        <div class="prose max-w-none">
            <p class="whitespace-pre-line">{{ $selectedReason }}</p>
        </div>

        <x-slot:actions>
            <x-button label="Close" @click="$wire.closeReasonModal()" />
        </x-slot:actions>
    </x-modal>

    <!-- Details Modal -->
    <x-modal wire:model="showDetailsModal" title="Reschedule Request Details" class="backdrop-blur"
        box-class="max-w-7xl w-full mx-4">
        @if ($selectedBooking)
            <div class="space-y-3 max-h-[80vh] overflow-y-auto px-1">
                <!-- Booking Information -->
                <div>
                    <h3 class="font-semibold text-base mb-2 flex items-center gap-1.5">
                        <x-icon name="o-document-text" class="w-4 h-4" />
                        Booking Information
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3 p-3 bg-base-200 rounded-lg">
                        <div>
                            <span class="text-sm text-gray-500">Booking ID</span>
                            <p class="font-semibold">#{{ $selectedBooking->id }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Property</span>
                            <p class="font-semibold">{{ $selectedBooking->bookingable->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ class_basename($selectedBooking->bookingable_type) }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Customer</span>
                            <p class="font-semibold">{{ $selectedBooking->user->name ?? 'N/A' }}</p>
                            <p class="text-xs text-gray-500">{{ $selectedBooking->user->email ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Guest Name</span>
                            <p class="font-semibold">{{ $selectedBooking->guest_name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Wallet Balance</span>
                            <p class="font-semibold text-info">
                                {{ currency_format($selectedBooking->user->wallet_balance ?? 0) }}</p>
                        </div>
                    </div>
                </div>

                <!-- Current Booking Details -->
                <div>
                    <h3 class="font-semibold text-base mb-2 flex items-center gap-1.5">
                        <x-icon name="o-calendar" class="w-4 h-4 text-primary" />
                        Current Booking
                    </h3>
                    <div class="p-3 bg-base-200 rounded-lg">
                        @if ($selectedBooking->bookingable instanceof \App\Models\Boat)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <span class="text-sm text-gray-500">Date</span>
                                    <p class="font-semibold">{{ $selectedBooking->check_in->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Duration</span>
                                    <p class="font-semibold">{{ $this->getBookingDuration($selectedBooking) }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Time</span>
                                    <p class="font-semibold">{{ $selectedBooking->check_in->format('h:i A') }} -
                                        {{ $selectedBooking->check_out->format('h:i A') }}</p>
                                </div>
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <span class="text-sm text-gray-500">Check-in</span>
                                    <p class="font-semibold">{{ $selectedBooking->check_in->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Check-out</span>
                                    <p class="font-semibold">{{ $selectedBooking->check_out->format('d M Y') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Requested Changes -->
                <div>
                    <h3 class="font-semibold text-base mb-2 flex items-center gap-1.5">
                        <x-icon name="o-calendar" class="w-4 h-4 text-success" />
                        Requested Changes
                    </h3>
                    <div class="p-3 bg-success/10 rounded-lg border border-success/20">
                        @if ($selectedBooking->bookingable instanceof \App\Models\Boat)
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                <div>
                                    <span class="text-sm text-gray-500">New Date</span>
                                    <p class="font-semibold text-success">
                                        {{ $selectedBooking->new_check_in?->format('d M Y') ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Duration</span>
                                    <p class="font-semibold text-success">
                                        {{ $this->getRequestedDuration($selectedBooking) }}</p>
                                </div>
                                @if ($selectedBooking->requested_time_slot)
                                    <div>
                                        <span class="text-sm text-gray-500">Time Slot</span>
                                        <p class="font-semibold text-success">
                                            {{ $selectedBooking->requested_time_slot }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                <div>
                                    <span class="text-sm text-gray-500">New Check-in</span>
                                    <p class="font-semibold text-success">
                                        {{ $selectedBooking->new_check_in?->format('d M Y') ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">New Check-out</span>
                                    <p class="font-semibold text-success">
                                        {{ $selectedBooking->new_check_out?->format('d M Y') ?? 'N/A' }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Reschedule Details -->
                <div>
                    <h3 class="font-semibold text-base mb-2 flex items-center gap-1.5">
                        <x-icon name="o-information-circle" class="w-4 h-4" />
                        Request Details
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="p-3 bg-base-200 rounded-lg">
                            <span class="text-sm text-gray-500">Reschedule Fee</span>
                            <p class="font-semibold text-warning text-lg">
                                {{ currency_format($selectedBooking->reschedule_fee ?? 0) }}</p>
                        </div>
                        <div class="p-3 bg-base-200 rounded-lg">
                            <span class="text-sm text-gray-500">Requested On</span>
                            <p class="font-semibold">
                                {{ $selectedBooking->reschedule_requested_at?->format('d M Y, h:i A') ?? 'N/A' }}</p>
                        </div>
                        @if ($selectedBooking->reschedule_reason)
                            <div class="p-3 bg-base-200 rounded-lg md:col-span-2">
                                <span class="text-sm text-gray-500 block mb-1">Reason for Rescheduling</span>
                                <p class="whitespace-pre-line text-sm">{{ $selectedBooking->reschedule_reason }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <x-slot:actions>
                <x-button label="Close" @click="$wire.closeDetailsModal()" />
            </x-slot:actions>
        @endif
    </x-modal>
</div>
