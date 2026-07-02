<?php

namespace Zerp\Jitsi\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class JitsiSettingsController extends Controller
{
    public function update(Request $request)
    {
        if (Auth::user()->can('edit-jitsi-settings')) {

            // No credentials are required to use the public meet.jit.si
            // instance. jitsi_domain, jitsi_app_id, and jitsi_jwt_secret are
            // only needed for a self-hosted or JaaS (authenticated) instance.
            $rules = [
                'settings.jitsi_enabled' => 'nullable|string|in:on,off',
                'settings.jitsi_domain' => 'nullable|string|max:255',
                'settings.jitsi_app_id' => 'nullable|string|max:255',
                'settings.jitsi_jwt_secret' => 'nullable|string|max:255',
            ];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->with('error', __('Validation failed'));
            }

            $allowedSettings = [
                'jitsi_enabled',
                'jitsi_domain',
                'jitsi_app_id',
                'jitsi_jwt_secret',
            ];

            $settings = $request->input('settings', []);
            try {
                foreach ($settings as $key => $value) {
                    if (in_array($key, $allowedSettings)) {
                        setSetting($key, $value, creatorId(), false);
                    }
                }

                return redirect()->back()->with('success', __('Jitsi Meet settings saved successfully.'));
            } catch (\Exception $e) {
                return redirect()->back()->with('error', __('Failed to update Jitsi Meet settings: ') . $e->getMessage());
            }

        } else {
            return redirect()->back()->with('error', __('Permission denied'));
        }
    }
}
