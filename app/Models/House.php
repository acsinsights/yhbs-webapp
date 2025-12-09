<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class House extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'house_number',
        'is_active',
        'image',
        'description',
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
        })->whereHas('rooms'); // Ensure the house has at least one room
    }
}
