<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Room extends Model
{
    protected $fillable = [
        'hotel_id',
        'name',
        'slug',
        'room_number',
        'image',
        'description',
        'price',
        'discount_price',
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
     * Get the hotel that owns the room.
     */
    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
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
            ->orWhereHas('hotel', function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            });
    }
}
