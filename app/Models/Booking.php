<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Booking extends Model
{
    protected $fillable = [
        'bookingable_id',  
        'user_id',
        'check_in',
        'check_out',
        'price',
        'discount_price',
        'status',
        'payment_status',
        'payment_method',
        'notes',
    ];
    /**
     * Get the parent bookingable model (room or yatch).
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
}
