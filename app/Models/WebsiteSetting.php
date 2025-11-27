<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WebsiteSetting extends Model
{
    protected $fillable = ['name', 'key', 'value', 'type'];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Clear cache when a setting is updated
        static::updated(function (WebsiteSetting $setting) {
            Cache::forget("website_setting_{$setting->key}");
        });

        // Clear cache when a setting is deleted
        static::deleted(function (WebsiteSetting $setting) {
            Cache::forget("website_setting_{$setting->key}");
        });
    }
}
