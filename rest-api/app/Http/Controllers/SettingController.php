<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    public function createSetting(Request $request)
    {
        $request->validate([
            'key' => ['required','string','min:1','max:100','regex:/^[A-Za-z_]{1,100}$/',Rule::unique('settings','key')],
            'value' => ['required','string','min:1','max:255'],
            'description' => ['nullable','min:1','max:255'],
        ]);

        $setting = Setting::create([
            'key' => trim(strtoupper($request->key)),
            'value' => trim($request->value),
            'description' => trim(strtoupper($request->description))
        ]);

        return response()->json([
            'message' => 'Variable de configuración creada correctamente. Asignado el ID: '.$setting->id.' con clave: '.$setting->key,
            'setting' => $setting,
        ],201);
    }

    public function updateSetting(Request $request, $id)
    {
        $oldSetting = Setting::find($id);
        if(!$oldSetting)
        {
            return response()->json([
                'message' => 'Variable de configuración de ID: '.$id.' no encontrada.',
            ],404);
        }

        $request->validate([
            'key' => ['required','string','min:1','max:100','regex:/^[A-Za-z_]{1,100}$/',Rule::unique('settings','key')->ignore($id)],
            'value' => ['required','string','min:1','max:255'],
            'description' => ['nullable','min:1','max:255'],
        ]);

        $oldSetting->update([
            'key' => trim(strtoupper($request->key)),
            'value' => trim($request->value),
            'description' => trim(strtoupper($request->description))
        ]);

        return response()->json([
            'message' => 'Variable de configuración con ID: '.$id.' actualizada correctamente.',
        ],200);
    }

    public function getSettingByKey($key)
    {
        $setting = Setting::where('key',$key)->first();
        if(!$setting)
        {
            return response()->json([
                'message' => 'Variable de configuración con clave: '.$key.' no encontrada.'
            ],404);
        }
        return response()->json($setting,200);
    }

    public function getSettings()
    {
        $settings = Setting::all();
        return response()->json($settings,200);
    }
}
