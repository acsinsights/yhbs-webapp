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
}
