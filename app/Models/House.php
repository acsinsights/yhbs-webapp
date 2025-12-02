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
}
