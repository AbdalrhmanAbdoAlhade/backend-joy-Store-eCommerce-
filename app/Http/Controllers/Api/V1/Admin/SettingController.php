<?php
// app/Http/Controllers/Api/V1/Admin/SettingController.php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->groupBy('group');

        return response()->json($settings);
    }

    public function update(Request $request)
    {
        $request->validate([
            'settings' => ['required', 'array'],
            'settings.*.key' => ['required', 'string'],
            'settings.*.value' => ['nullable'],
            'settings.*.type' => ['nullable', 'in:string,number,boolean,json'],
        ]);

        foreach ($request->settings as $item) {
            Setting::set(
                $item['key'],
                $item['value'],
                $item['type'] ?? 'string',
                $item['group'] ?? 'general'
            );
        }

        return response()->json(['message' => 'تم تحديث الإعدادات']);
    }
}
