<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Yatch extends Model
{
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
    ];

    protected $casts = [
        'library' => AsCollection::class,
    ];

    /**
     * The categories that belong to the yacht.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_yatch', 'yatch_id', 'category_id')
            ->where('categories.type', 'yatch');
    }

    /**
     * The amenities that belong to the yacht.
     */
    public function amenities(): BelongsToMany
    {
        return $this->belongsToMany(Amenity::class, 'amenity_yatch', 'yatch_id', 'amenity_id')
            ->where('amenities.type', 'yatch');
    }
}
