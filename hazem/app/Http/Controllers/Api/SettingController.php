<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return response()->json(Setting::all());
    }

    public function show($key)
    {
        $setting = Setting::where('key', $key)->first();
        return response()->json($setting);
    }

    public function update(Request $request, $key)
    {
        $validated = $request->validate([
            'value' => 'required',
            'type' => 'sometimes|string',
        ]);

        $setting = Setting::set($key, $validated['value'], $validated['type'] ?? 'text');
        return response()->json($setting);
    }

    public function bulkUpdate(Request $request)
    {
        $settings = $request->all();
        
        foreach ($settings as $key => $value) {
            Setting::set($key, $value);
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }
}
