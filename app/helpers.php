<?php

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

if (! function_exists('setting')) {
    /**
     * Read the app's company settings from anywhere -- a Blade view, a
     * controller, a PDF template, a mail. Works just like Laravel's own
     * config() helper: call it with no arguments to get the whole
     * settings record, or with a key to get just that one value.
     *
     * The settings row is read constantly (every receipt, every report,
     * every page's logo) but changes rarely, so it's cached indefinitely
     * and only re-fetched when SettingService clears the cache after a save.
     */
    function setting(?string $key = null, mixed $default = null): mixed
    {
        $settings = Cache::rememberForever('app-settings', fn () => Setting::current());

        if ($key === null) {
            return $settings;
        }

        return $settings->{$key} ?? $default;
    }
}
