<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PolicyPage extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($policyPage) {
            if (empty($policyPage->slug)) {
                $policyPage->slug = Str::slug($policyPage->title);
            }
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
