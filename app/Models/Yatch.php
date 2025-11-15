<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\AsCollection;

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
        'library',
    ];

    protected $casts = [
        'library' => AsCollection::class,
    ];
}
