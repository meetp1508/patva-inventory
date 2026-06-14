<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingsRequest;
use App\Services\SettingService;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function __construct(private readonly SettingService $settings)
    {
    }

    public function index()
    {
        $this->authorize('settings access');

        return view('settings.index', ['settings' => $this->settings]);
    }

    public function update(UpdateSettingsRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('company_logo')) {
            $old = $this->settings->get('company_logo');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            $data['company_logo'] = $request->file('company_logo')->store('logos', 'public');
        } else {
            unset($data['company_logo']);
        }

        $this->settings->set($data);

        return redirect()->route('settings.index')->with('success', 'Settings updated successfully.');
    }
}
