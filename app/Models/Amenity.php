<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Amenity extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'type',
    ];

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}
