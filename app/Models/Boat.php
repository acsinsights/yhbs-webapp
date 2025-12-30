<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

class Boat extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'service_type',
        'description',
        'image',
        'images',
        'min_passengers',
        'max_passengers',
        'price_per_hour',
        'price_1hour',
        'price_2hours',
        'price_3hours',
        'additional_hour_price',
        'price_per_person_adult',
        'price_per_person_child',
        'private_trip_price',
        'private_trip_return_price',
        'price_15min',
        'price_30min',
        'price_full_boat',
        'location',
        'features',
        'meta_description',
        'meta_keywords',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'images' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price_per_hour' => 'decimal:2',
        'price_1hour' => 'decimal:2',
        'price_2hours' => 'decimal:2',
        'price_3hours' => 'decimal:2',
        'additional_hour_price' => 'decimal:2',
        'price_per_person_adult' => 'decimal:2',
        'price_per_person_child' => 'decimal:2',
        'private_trip_price' => 'decimal:2',
        'private_trip_return_price' => 'decimal:2',
        'price_15min' => 'decimal:2',
        'price_30min' => 'decimal:2',
        'price_full_boat' => 'decimal:2',
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
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
        return match($this->service_type) {
            'marina_trip' => 'Marina Trip',
            'taxi' => 'Private Taxi',
            'ferry' => 'Ferry Service',
            'limousine' => 'Limousine Service',
            default => $this->service_type,
        };
    }

    public function getDisplayPriceAttribute(): string
    {
        return match($this->service_type) {
            'marina_trip', 'taxi' => $this->price_1hour ? "KD {$this->price_1hour}/hour" : "KD {$this->price_per_hour}/hour",
            'ferry' => $this->price_per_person_adult ? "KD {$this->price_per_person_adult}/person" : "From KD {$this->private_trip_price}",
            'limousine' => $this->price_15min ? "KD {$this->price_15min}/15min" : "KD {$this->price_full_boat}",
            default => 'Contact for price',
        };
    }
}
