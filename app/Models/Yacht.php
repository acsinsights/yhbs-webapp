<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Yacht extends Model
{
    protected $table = 'yachts';

    protected $fillable = [
        'name',
        'slug',
        'image',
        'description',
        'sku',
        'price',
        'discount_price',
        'length',
        'width',
        'max_guests',
        'max_crew',
        'max_fuel_capacity',
        'max_capacity',
        'library',
        'is_active',
    ];

    protected $casts = [
        'library' => AsCollection::class,
        'is_active' => 'boolean',
    ];

    /**
     * The categories that belong to the yacht.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_yacht', 'yacht_id', 'category_id')
            ->where('categories.type', 'yacht');
    }

    /**
     * The amenities that belong to the yacht.
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'amenity_yacht', 'yacht_id', 'amenity_id')
            ->where('amenities.type', 'yacht');
    }

    /**
     * Get bookings for this yacht.
     */
    public function bookings(): MorphMany
    {
        return $this->morphMany(Booking::class, 'bookingable');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include yachts that are available for the given date range.
     */
    public function scopeAvailable($query, $checkIn, $checkOut)
    {
        return $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
            $q->where(function ($query) use ($checkIn, $checkOut) {
                $query->where(function ($q) use ($checkIn, $checkOut) {
                    $q->whereBetween('check_in', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out', [$checkIn, $checkOut])
                        ->orWhere(function ($q) use ($checkIn, $checkOut) {
                            $q->where('check_in', '<=', $checkIn)
                                ->where('check_out', '>=', $checkOut);
                        });
                })
                    ->whereIn('status', ['pending', 'booked', 'checked_in']);
            });
        });
    }
}
