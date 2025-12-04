<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
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

    public function rooms(): BelongsToMany
    {
        return $this->belongsToMany(Room::class);
    }

    public function yachts(): BelongsToMany
    {
        return $this->belongsToMany(Yacht::class);
    }
}
