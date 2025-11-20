<?php

use App\Models\WebsiteSetting;
use Illuminate\Support\Facades\Cache;

if (!function_exists('website_setting')) {
    /**
     * Get a single website setting value by key.
     */
    function website_setting(string $key, mixed $default = null): mixed
    {
        if ($key === '') {
            return $default;
        }

        $cacheKey = "website_setting_{$key}";

        return Cache::remember($cacheKey, now()->addHour(), function () use ($key, $default) {
            return WebsiteSetting::query()
                ->where('key', $key)
                ->value('value') ?? $default;
        });
    }
}

if (!function_exists('website_settings')) {
    /**
     * Get multiple website settings at once.
     *
     * @return array<string, mixed>
     */
    function website_settings(array $keys): array
    {
        if (empty($keys)) {
            return [];
        }

        $settings = WebsiteSetting::query()
            ->whereIn('key', $keys)
            ->pluck('value', 'key');

        return collect($keys)
            ->mapWithKeys(fn($key) => [$key => $settings[$key] ?? null])
            ->toArray();
    }
}

if (!function_exists('currency_symbol')) {
    function currency_symbol(): string
    {
        return (string) website_setting('currency_symbol', '$');
    }
}

if (!function_exists('currency_name')) {
    function currency_name(): string
    {
        return (string) website_setting('currency_name', 'USD');
    }
}

if (!function_exists('currency_format')) {
    function currency_format(null|int|float|string $amount, int $decimals = 2, bool $withSymbol = true): string
    {
        $numeric = is_numeric($amount) ? (float) $amount : 0;
        $formatted = number_format($numeric, $decimals);

        if (! $withSymbol) {
            return $formatted;
        }

        $symbol = currency_symbol();
        $symbol = trim($symbol);
        $spacer = strlen($symbol) > 1 ? ' ' : '';

        return $symbol . $spacer . $formatted;
    }
}
