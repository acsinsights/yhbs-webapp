<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class House extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'house_number',
        'is_active',
        'image',
        'description',
        'price_per_night',
        'price_per_2night',
        'price_per_3night',
        'additional_night_price',
        'adults',
        'children',
        'library',
    ];

    protected $casts = [
        'library' => AsCollection::class,
    ];

    /**
     * Get rooms for this house.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

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
     * Scope to get houses where ALL rooms are available for the given date range
     * (i.e., no rooms in the house have bookings for the selected dates)
     */
    public function scopeAvailable($query, $checkIn, $checkOut)
    {
        return $query->whereDoesntHave('rooms.bookings', function ($q) use ($checkIn, $checkOut) {
            $q->where(function ($query) use ($checkIn, $checkOut) {
                // Check if any booking overlaps with the date range
                // Two ranges overlap if: booking_check_in < new_check_out AND booking_check_out > new_check_in
                $query->where('check_in', '<', $checkOut)
                    ->where('check_out', '>', $checkIn);
            })
                ->whereIn('status', ['pending', 'booked', 'checked_in']); // Only consider active bookings
        })->whereHas('rooms'); // Ensure the house has at least one room
    }
}
