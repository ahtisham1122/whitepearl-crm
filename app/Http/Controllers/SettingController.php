<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settingService)
    {
    }

    /**
     * Show the settings form. There is only ever one settings row, so
     * this is a single "edit" page -- no index/create/show/destroy.
     */
    public function edit(): View
    {
        return view('settings.edit', ['setting' => Setting::current()]);
    }

    public function update(UpdateSettingRequest $request): RedirectResponse
    {
        $this->settingService->update($request->validated(), $request->file('company_logo'));

        return redirect()
            ->route('settings.edit')
            ->with('success', 'Settings updated successfully.');
    }
}
