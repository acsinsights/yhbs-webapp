<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'library' => 'array',
    ];
}
