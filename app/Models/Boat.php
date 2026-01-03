<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsToMany, MorphMany};
use Illuminate\Support\Str;

class Boat extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'service_type',
        'description',
        'image',
        'library',
        'min_passengers',
        'max_passengers',
        'price_per_hour',
        'price_1hour',
        'price_2hours',
        'price_3hours',
        'additional_hour_price',
        'ferry_private_weekday',
        'ferry_private_weekend',
        'ferry_public_weekday',
        'ferry_public_weekend',
        'price_15min',
        'price_30min',
        'price_full_boat',
        'buffer_time',
        'location',
        'features',
        'meta_description',
        'meta_keywords',
        'is_active',
        'is_featured',
        'sort_order',
        'booking_policy',
        'allows_same_day_booking',
        'requires_advance_booking',
        'is_monthly_schedule',
        'library',
    ];

    protected $casts = [
        'library' => AsCollection::class,
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price_per_hour' => 'decimal:2',
        'price_1hour' => 'decimal:2',
        'price_2hours' => 'decimal:2',
        'price_3hours' => 'decimal:2',
        'additional_hour_price' => 'decimal:2',
        'ferry_private_weekday' => 'decimal:2',
        'ferry_private_weekend' => 'decimal:2',
        'ferry_public_weekday' => 'decimal:2',
        'ferry_public_weekend' => 'decimal:2',
        'price_15min' => 'decimal:2',
        'price_30min' => 'decimal:2',
        'price_full_boat' => 'decimal:2',
        'allows_same_day_booking' => 'boolean',
        'requires_advance_booking' => 'boolean',
        'is_monthly_schedule' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($boat) {
            if (empty($boat->slug)) {
                $boat->slug = Str::slug($boat->name);
            }
        });

        static::updating(function ($boat) {
            if ($boat->isDirty('name') && empty($boat->slug)) {
                $boat->slug = Str::slug($boat->name);
            }
        });
    }

    public function bookings(): MorphMany
    {
        return $this->morphMany(Booking::class, 'bookingable');
    }

    /**
     * The amenities that belong to the boat.
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
        return $query->where('name', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->orWhere('location', 'like', "%{$search}%");
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('service_type', $type);
    }

    public function getServiceTypeLabelAttribute(): string
    {
        return match ($this->service_type) {
            'yacht' => 'Yacht',
            'taxi' => 'Private Taxi',
            'ferry' => 'Ferry Service',
            'limousine' => 'Limousine Service',
            default => $this->service_type,
        };
    }

    public function getDisplayPriceAttribute(): string
    {
        return match ($this->service_type) {
            'yacht', 'taxi' => $this->price_1hour ? "KD {$this->price_1hour}/hour" : "KD {$this->price_per_hour}/hour",
            'ferry' => $this->price_per_person_adult ? "KD {$this->price_per_person_adult}/person" : "From KD {$this->private_trip_price}",
            'limousine' => $this->price_15min ? "KD {$this->price_15min}/15min" : "KD {$this->price_full_boat}",
            default => 'Contact for price',
        };
    }
}
