<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\{BookingStatusEnum, PaymentMethodEnum, PaymentStatusEnum};
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Booking extends Model
{
    use LogsActivity;
    protected $fillable = [
        'booking_id',
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
        'price_per_night',
        'nights',
        'service_fee',
        'tax',
        'price_per_hour',
        'discount_price',
        'coupon_id',
        'discount_amount',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'trip_type',
        'notes',
        'cancellation_requested_at',
        'cancellation_status',
        'cancellation_reason',
        'cancelled_at',
        'refund_amount',
        'refund_status',
        'cancelled_by',
        'reschedule_requested_at',
        'reschedule_status',
        'reschedule_reason',
        'new_check_in',
        'new_check_out',
        'requested_time_slot',
        'reschedule_fee',
        'rescheduled_by',
        'extra_fee',
        'extra_fee_remark',
    ];

    protected $casts = [
        'guest_details' => 'array',
        'check_in' => 'datetime',
        'check_out' => 'datetime',
        'status' => BookingStatusEnum::class,
        'payment_status' => PaymentStatusEnum::class,
        'payment_method' => PaymentMethodEnum::class,
        'cancellation_requested_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'reschedule_requested_at' => 'datetime',
        'new_check_in' => 'datetime',
        'new_check_out' => 'datetime',
    ];

    /**
     * Boot method to auto-generate booking_id.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->booking_id)) {
                $booking->booking_id = self::generateUniqueBookingId();
            }
        });
    }

    /**
     * Generate a unique 5-character alphanumeric booking ID.
     */
    private static function generateUniqueBookingId(): string
    {
        do {
            // Generate a random 5-character alphanumeric string
            $bookingId = strtoupper(substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 5));
        } while (self::where('booking_id', $bookingId)->exists());

        return $bookingId;
    }

    /**
     * Get the activity log options.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'payment_status', 'payment_method', 'check_in', 'check_out', 'price'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

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
     * Get the coupon used for this booking.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the user who cancelled this booking.
     */
    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    /**
     * Get the user who rescheduled this booking.
     */
    public function rescheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rescheduled_by');
    }

    /**
     * Check if cancellation has been requested.
     */
    public function hasCancellationRequest(): bool
    {
        return $this->cancellation_requested_at !== null && $this->cancellation_status === 'pending';
    }

    /**
     * Check if reschedule has been requested.
     */
    public function hasRescheduleRequest(): bool
    {
        return $this->reschedule_requested_at !== null && $this->reschedule_status === 'pending';
    }

    /**
     * Check if booking is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === BookingStatusEnum::CANCELLED || $this->cancelled_at !== null;
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
            && $this->status !== BookingStatusEnum::CANCELLED
            && $this->cancelled_at === null
            && $this->cancellation_requested_at === null;
    }

    /**
     * Check if the booking can be rescheduled.
     */
    public function canBeRescheduled(): bool
    {
        // Cannot reschedule if check-in date has passed
        if ($this->check_in && $this->check_in->isPast()) {
            return false;
        }

        return $this->status !== BookingStatusEnum::CHECKED_IN
            && $this->status !== BookingStatusEnum::CHECKED_OUT
            && $this->status !== BookingStatusEnum::CANCELLED
            && $this->cancelled_at === null
            && $this->cancellation_requested_at === null
            && $this->reschedule_requested_at === null;
    }

    /**
     * Calculate reschedule fee based on booking type.
     */
    public function calculateRescheduleFee(): float
    {
        $bookingableType = class_basename($this->bookingable_type);

        if ($bookingableType === 'House') {
            return 50.00; // 50 KWD for houses
        } elseif ($bookingableType === 'Room') {
            return 20.00; // 20 KWD for hotel rooms
        } elseif ($bookingableType === 'Boat') {
            // 2 KWD per person for boats
            $totalPersons = ($this->adults ?? 0) + ($this->children ?? 0);
            return $totalPersons * 2.00;
        }

        return 0.00;
    }

    /**
     * Check if the booking can be deleted.
     */
    public function canBeDeleted(): bool
    {
        return $this->status !== BookingStatusEnum::CHECKED_IN;
    }

    /**
     * Scope a query to search bookings by user and bookingable properties.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $search
     * @param  array  $bookingableFields  Fields to search in the bookingable model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search, array $bookingableFields = [])
    {
        if (empty($search)) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $bookingableFields) {
            // Search in user name and email
            $q->whereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });

            // Search in bookingable fields if provided
            if (!empty($bookingableFields)) {
                $q->orWhereHas('bookingable', function ($bookingableQuery) use ($search, $bookingableFields) {
                    $bookingableQuery->where(function ($fieldQuery) use ($search, $bookingableFields) {
                        foreach ($bookingableFields as $index => $field) {
                            $method = $index === 0 ? 'where' : 'orWhere';

                            // Handle nested relationships (e.g., 'house.name')
                            if (str_contains($field, '.')) {
                                $relationName = substr($field, 0, strpos($field, '.'));
                                $nestedField = substr($field, strpos($field, '.') + 1);

                                $fieldQuery->orWhereHas($relationName, function ($nestedQuery) use ($search, $nestedField) {
                                    $nestedQuery->where($nestedField, 'like', "%{$search}%");
                                });
                            } else {
                                $fieldQuery->{$method}($field, 'like', "%{$search}%");
                            }
                        }
                    });
                });
            }
        });
    }
}
