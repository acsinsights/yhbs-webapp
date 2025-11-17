<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
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
}
