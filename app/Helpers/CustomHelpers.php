<?php

use App\Models\SessionEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

if (!function_exists('hasAuthRole')) {
    /**
     * Check if the authenticated user has a specific role
     *
     * @param string $role
     * @return bool
     */
    function hasAuthRole($role): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user ? $user->hasRole($role) : false;
    }
}

if (!function_exists('hasAuthAnyRole')) {
    /**
     * Check if the authenticated user has any of the specified roles
     *
     * @param string|array $roles
     * @return bool
     */
    function hasAuthAnyRole($roles): bool
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        return $user ? $user->hasAnyRole($roles) : false;
    }
}

if (!function_exists('getCurrentSession')) {
    function getCurrentSession()
    {
        $user = User::find(Auth::id());

        $currentSession = SessionEntry::find($user->session_entry_id);

        return $currentSession;
    }
}

if (!function_exists('customTransliterate')) {
    /**
     * Custom transliteration for better Devanagari conversion
     * Converts Latin script to proper Devanagari script for Hindi/Marathi names
     *
     * @param string $text The text to transliterate
     * @return string The transliterated text
     */
    function customTransliterate($text)
    {
        // Load mappings from configuration file and merge all sections
        $config = config('transliteration');
        $mappings = [];

        // Merge all sections into one flat array
        foreach ($config as $section => $sectionMappings) {
            if (is_array($sectionMappings)) {
                $mappings = array_merge($mappings, $sectionMappings);
            }
        }

        // Split the text into words for better handling of full names
        $words = explode(' ', trim($text));
        $transliteratedWords = [];

        foreach ($words as $word) {
            $result = strtolower($word); // Convert to lowercase for mapping

            // Apply mappings in order of length (longer first to avoid partial matches)
            $sortedMappings = $mappings;
            uksort($sortedMappings, function ($a, $b) {
                return strlen($b) - strlen($a);
            });

            foreach ($sortedMappings as $latin => $devanagari) {
                if (strpos($result, $latin) !== false) {
                    $result = str_replace($latin, $devanagari, $result);
                }
            }

            $transliteratedWords[] = $result;
        }

        return implode(' ', $transliteratedWords);
    }
}

if (!function_exists('getPageMeta')) {
    /**
     * Get page meta information based on route name
     *
     * @param string|null $routeName
     * @return object
     */
    function getPageMeta($routeName = null)
    {
        if (!$routeName) {
            $routeName = request()->route()->getName();
        }

        $pageMeta = \App\Models\PageMeta::where('route_name', $routeName)->first();

        if ($pageMeta) {
            return (object) [
                'title' => $pageMeta->meta_title,
                'description' => $pageMeta->meta_description,
                'keywords' => $pageMeta->meta_keywords,
            ];
        }

        // Return default meta
        return (object) [
            'title' => config('app.name') . ' - Yacht & House Booking System',
            'description' => 'Book luxury yachts and premium houses with our advanced booking system.',
            'keywords' => 'yacht booking, house rental, luxury rentals',
        ];
    }
}
