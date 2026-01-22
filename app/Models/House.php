<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class House extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'house_number',
        'is_active',
        'is_under_maintenance',
        'maintenance_note',
        'image',
        'description',
        'meta_description',
        'meta_keywords',
        'price_per_night',
        'price_per_2night',
        'price_per_3night',
        'additional_night_price',
        'adults',
        'children',
        'number_of_rooms',
        'library',
        'unavailable_days',
    ];

    protected $casts = [
        'library' => AsCollection::class,
        'unavailable_days' => 'array',
        'is_active' => 'boolean',
        'is_under_maintenance' => 'boolean',
    ];

    /**
     * Get bookings for this house.
     */
    public function bookings(): MorphMany
    {
        return $this->morphMany(Booking::class, 'bookingable');
    }

    /**
     * Scope to get active houses
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get bookable houses (active and not under maintenance)
     */
    public function scopeBookable($query)
    {
        return $query->where('is_active', true)->where('is_under_maintenance', false);
    }

    /**
     * Scope to get available houses for the given date range
     */
    public function scopeAvailable($query, $checkIn, $checkOut)
    {
        return $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
            $q->where(function ($query) use ($checkIn, $checkOut) {
                // Check if any booking overlaps with the date range
                $query->where('check_in', '<', $checkOut)
                    ->where('check_out', '>', $checkIn);
            })
                ->whereIn('status', ['pending', 'booked', 'checked_in']); // Only consider active bookings
        });
    }
}
