<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\{BookingStatusEnum, PaymentMethodEnum, PaymentStatusEnum};

class Booking extends Model
{
    protected $fillable = [
        'bookingable_type',
        'bookingable_id',
        'user_id',
        'adults',
        'children',
        'guest_details',
        'check_in',
        'check_out',
        'arrival_time',
        'price',
        'discount_price',
        'status',
        'payment_status',
        'payment_method',
        'notes',
    ];

    protected $casts = [
        'guest_details' => 'array',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'status' => BookingStatusEnum::class,
        'payment_status' => PaymentStatusEnum::class,
        'payment_method' => PaymentMethodEnum::class,
    ];

    /**
     * Get the parent bookingable model (room or yacht).
     */
    public function bookingable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user that owns the booking.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the booking can be edited.
     */
    public function canBeEdited(): bool
    {
        return $this->status !== BookingStatusEnum::CHECKED_OUT
            && $this->status !== BookingStatusEnum::CANCELLED
            && $this->payment_status !== PaymentStatusEnum::FAILED;
    }

    /**
     * Check if the booking status is BOOKED.
     */
    public function isBooked(): bool
    {
        return $this->status === BookingStatusEnum::BOOKED;
    }

    /**
     * Check if the booking status is CHECKED_IN.
     */
    public function isCheckedIn(): bool
    {
        return $this->status === BookingStatusEnum::CHECKED_IN;
    }

    /**
     * Check if the booking status is CHECKED_OUT.
     */
    public function isCheckedOut(): bool
    {
        return $this->status === BookingStatusEnum::CHECKED_OUT;
    }

    /**
     * Check if the booking can be checked in.
     */
    public function canCheckIn(): bool
    {
        return $this->status === BookingStatusEnum::BOOKED;
    }

    /**
     * Check if the booking can be checked out.
     */
    public function canCheckOut(): bool
    {
        return $this->status === BookingStatusEnum::CHECKED_IN;
    }

    /**
     * Check if the booking can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return $this->status !== BookingStatusEnum::CHECKED_IN
            && $this->status !== BookingStatusEnum::CHECKED_OUT
            && $this->status !== BookingStatusEnum::CANCELLED;
    }

    /**
     * Check if the booking can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return $this->status !== BookingStatusEnum::CHECKED_IN;
    }
}
