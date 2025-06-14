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
            'key' => ['required', 'string', 'min:1', 'max:100', 'regex:/^[A-Za-z_]{1,100}$/', Rule::unique('settings', 'key')],
            'value' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['nullable', 'min:1', 'max:255'],
        ]);

        $setting = Setting::create([
            'key' => trim(strtoupper($request->key)),
            'value' => trim($request->value),
            'description' => trim(strtoupper($request->description))
        ]);

        return response()->json([
            'message' => 'Variable de configuración creada correctamente. Asignado el ID: ' . $setting->id . ' con clave: ' . $setting->key,
            'setting' => $setting,
        ], 201);
    }

    public function updateSetting(Request $request, $id)
    {
        $oldSetting = Setting::find($id);
        if (!$oldSetting) {
            return response()->json([
                'message' => 'Variable de configuración de ID: ' . $id . ' no encontrada.',
            ], 404);
        }

        $request->validate([
            'key' => ['required', 'string', 'min:1', 'max:100', 'regex:/^[A-Za-z_]{1,100}$/', Rule::unique('settings', 'key')->ignore($id)],
            'value' => ['required', 'string', 'min:1', 'max:255'],
            'description' => ['nullable', 'min:1', 'max:255'],
        ]);

        $oldSetting->update([
            'key' => trim(strtoupper($request->key)),
            'value' => trim($request->value),
            'description' => trim(strtoupper($request->description))
        ]);

        return response()->json([
            'message' => 'Variable de configuración con ID: ' . $id . ' actualizada correctamente.',
        ], 200);
    }

    public function updateIgvConfig(Request $request)
    {
        //TODO: ******* UPDATE ALL PRODUCTS PRICES....!!! ******
        $request->validate([
            'key' => ['required', 'string', 'exists:settings,key'],
            'igvValue' => [
                'required',
                'regex:/^\d{1,3}(\.\d{1,2})?$/',
                'numeric',
                'min:0.1',
                'max:100',
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $setting = Setting::where('key', 'like', $request->key)->first();
        if (!$setting) {
            return response()->json([
                'message' => "No se encontro la configuración de clave: $request->key Operación cancelada."
            ], 404);
        }

        $setting->update([
            'value' => trim($request->igvValue),
            'description' => $request->description ?? '---',
        ]);
        $readableIgv = doubleval($request->igvValue) * 100;
        return response()->json([
            'message' => "Valor del IGV fijado en: $readableIgv% Todos los productos y servicios han sido actualizados correctamente."
        ]);
    }

    public function updateAppointmentPrice(Request $request)
    {
        $request->validate([
            'key' => ['required', 'string', 'exists:settings,key'],
            'appPrice' => [
                'required',
                'regex:/^\d{1,4}(\.\d{1,2})?$/',
                'numeric',
                'min:1',
                'max:5000',
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $setting = Setting::where('key', 'like', $request->key)->first();

        if (!$setting) {
            return response()->json([
                'message' => "No se encontro la configuración de clave: $request->key Operación cancelada."
            ], 404);
        }

        $setting->update([
            'value' => trim($request->appPrice),
            'description' => $request->description ?? '---',
        ]);
        $readablePrice = doubleval($request->appPrice);
        return response()->json([
            'message' => "Costo de consulta médica regular fijado en: S./$readablePrice"
        ]);
    }

    public function getSettingByKey($key)
    {
        $setting = Setting::where('key', $key)->first();
        if (!$setting) {
            return response()->json([
                'message' => 'Variable de configuración con clave: ' . $key . ' no encontrada.'
            ], 404);
        }
        return response()->json($setting, 200);
    }

    public function getSettings(Request $request)
    {
        $query = Setting::query();

        if ($request->has('id')) {
            $query->where('id', $request->input('id'));
        }
        if ($request->has('key')) {
            $query->where('key', 'ilike', '%' . $request->input('key') . '%');
        }
        if ($request->has('value')) {
            $query->where('value', 'ilike', '%' . $request->input('value') . '%');
        }
        if ($request->has('description')) {
            $query->where('description', 'ilike', '%' . $request->input('description') . '%');
        }

        $settings = $query->get();
        return response()->json($settings, 200);
    }
}
