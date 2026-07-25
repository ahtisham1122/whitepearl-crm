<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingService
{
    public function update(array $data, ?UploadedFile $logo): Setting
    {
        $setting = Setting::current();

        if ($logo instanceof UploadedFile) {
            if ($setting->company_logo) {
                Storage::disk('public')->delete($setting->company_logo);
            }

            $data['company_logo'] = $logo->store('company', 'public');
        }

        $setting->update($data);

        // The setting() helper caches this row forever since it's read on
        // almost every page load -- this is the one place that cache gets
        // cleared, so the very next read picks up what was just saved.
        Cache::forget('app-settings');

        return $setting;
    }
}
