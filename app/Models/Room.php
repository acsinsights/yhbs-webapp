<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Room extends Model
{
    protected $fillable = [
        'house_id',
        'name',
        'slug',
        'room_number',
        'image',
        'description',
        'price_per_night',
        'price_per_2night',
        'price_per_3night',
        'additional_night_price',
        'library',
        'meta_description',
        'meta_keywords',
        'is_active',
        'adults',
        'children',
    ];

    protected $casts = [
        'library' => AsCollection::class,
        'is_active' => 'boolean',
    ];

    /**
     * Get the house that owns the room.
     */
    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    /**
     * The categories that belong to the room.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * The amenities that belong to the room.
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where('room_number', 'like', "%{$search}%")
            ->orWhereHas('house', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            });
    }

    /**
     * Check if room is available for given date range
     */
    public function scopeAvailable($query, $checkIn, $checkOut)
    {
        return $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
            $q->where(function ($query) use ($checkIn, $checkOut) {
                // Check if new booking overlaps with existing bookings
                $query->where(function ($q) use ($checkIn, $checkOut) {
                    $q->whereBetween('check_in', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out', [$checkIn, $checkOut])
                        ->orWhere(function ($q) use ($checkIn, $checkOut) {
                            $q->where('check_in', '<=', $checkIn)
                                ->where('check_out', '>=', $checkOut);
                        });
                })
                    ->whereIn('status', ['pending', 'booked', 'checked_in']); // Only consider active bookings
            });
        });
    }

    /**
     * Get bookings for this room
     */
    public function bookings(): MorphMany
    {
        return $this->morphMany(Booking::class, 'bookingable');
    }
}
