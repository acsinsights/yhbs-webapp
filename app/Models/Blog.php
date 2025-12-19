<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Blog extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'is_published',
        'date',
        'description',
        'image',
        'content',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($blog) {
            if (empty($blog->slug)) {
                $blog->slug = Str::slug($blog->title);
            }
        });

        static::updating(function ($blog) {
            if ($blog->isDirty('title')) {
                $blog->slug = Str::slug($blog->title);
            }
        });
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'blog_tag');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }
}
