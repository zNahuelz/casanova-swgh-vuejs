<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DoctorAvailability;
use App\Models\Medicine;
use App\Models\Setting;
use App\Models\Treatment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SettingController extends Controller
{
    /**
     * Registra un variable para la configuracion del sistema previa validacion.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
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

    /**
     * Actualiza una variable de configuracion del sistema existente por ID.
     * @param \Illuminate\Http\Request $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
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

    /**
     * Actualiza la configuracion del IGV de sistema previa validacion, posteriormente actualiza el valor de IGV de todos los productos y servicios automaticamente.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function updateIgvConfig(Request $request)
    {
        $request->validate([
            'key' => ['required', 'string', 'exists:settings,key'],
            'igvValue' => [
                'required',
                'regex:/^\d{1,3}(\.\d{1,2})?$/',
                'numeric',
                'min:0',
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
        //Updating system-wide prices.
        try {
            DB::beginTransaction();
            $medicines = Medicine::where('salable', true)->get();
            $treatments = Treatment::all();
            $IGV = doubleval($request->igvValue);

            foreach ($medicines as $med) {
                $newIgv = 0;
                $newProfit = 0;
                if ($med->igv > 0) {
                    $base = $med->sell_price / (1 + $IGV);
                    $newIgv = round(($med->sell_price - $base), 2);
                    $newProfit = round(($base - $med->buy_price), 2);
                    if ($newProfit < 0) {
                        $newProfit = 0;
                    }

                    $med->update([
                        'igv' => $newIgv,
                        'profit' => $newProfit,
                    ]);
                } else {
                    $med->update([
                        'igv' => 0,
                        'profit' => ($med->sell_price - $med->buy_price) < 0 ? 0 : ($med->sell_price - $med->buy_price),
                    ]);
                }
            }

            foreach ($treatments as $trt) {
                $newIgv = 0;
                $newProfit = 0;
                if ($trt->igv > 0) {
                    $newIgv = round(($trt->price * $IGV), 2);
                    $newProfit = round(($trt->price - $newIgv), 2);
                } else {
                    $newIgv = 0;
                    $newProfit = round($trt->price, 2);
                }
                if ($newProfit < 0) {
                    $newProfit = 0;
                }
                $trt->update([
                    'igv' => $newIgv,
                    'profit' => $newProfit,
                ]);
            }
            DB::commit();
            return response()->json([
                'message' => "Valor del IGV fijado en: $readableIgv% Todos los productos y servicios han sido actualizados correctamente."
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Valor del IGV fijado en: $readableIgv% No se pudo actualizar el precio de productos y/o servicios. Intente nuevamente o comuniquese con administración."
            ], 500);
        }
    }

    /**
     * Actualiza el precio de una cita, previa validacion.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
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
        ], 200);
    }

    /**
     * Gestiona la posibilidad o no de trabajar los fines de semana (afecta reserva de citas, horario de doctores, habilita disponibilidades en findes de existir).
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function manageJobOnWeekends(Request $request)
    {
        $request->validate([
            'key' => ['required', 'string', 'exists:settings,key'],
            'jobOnWeekends' => ['required', 'in:true,false'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $setting = Setting::where('key', 'like', $request->key)->first();

        if (!$setting) {
            return response()->json([
                'message' => "No se encontro la configuración de clave: $request->key Operación cancelada."
            ], 404);
        }

        $availabilities = DoctorAvailability::whereIn('weekday', [6, 7])->get();

        try {
            DB::beginTransaction();
            if ($request->jobOnWeekends === 'false') {
                foreach ($availabilities as $av) {
                    $av->update([
                        'is_active' => false,
                    ]);
                }
            } else {
                foreach ($availabilities as $av) {
                    $av->update([
                        'is_active' => true,
                    ]);
                }
            }
            $setting->update([
                'value' => trim($request->jobOnWeekends),
                'description' => $request->description ?? '---',
            ]);
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "No se pudo actualizar el valor de la clave: $request->key. Intente nuevamente o comuniquese con administración."
            ], 500);
        }
        return response()->json([
            'message' => $setting->value === 'true' ?
                "Valor de llave: $request->key configurado como VERDADERO. El personal ahora puede trabajar los fines de semana. Recuerde que debe configurar el horario de cada doctor para los fines de semana." :
                "Valor de llave: $request->key configurado como FALSO. El personal NO puede trabajar los fines de semana. Recuerde que debe reprogramar o cancelar todas las citas reservadas para los fines de semana.",
        ]);
    }

    /**
     * Actualiza la informacion de los vouchers emitidos por el sistema (DIRECCION Y RUC DE LA EMPRESA)
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function updateVoucherInfo(Request $request)
    {
        if ($request->type === 'ADDRESS_CHANGE') {
            $request->validate([
                'key' => ['required', 'string', 'exists:settings,key'],
                'address' => ['required', 'string', 'min:10', 'max:120'],
                'description' => ['nullable', 'string', 'max:255'],
            ]);

            $setting = Setting::where('key', $request->key)->first();
            $setting->update([
                'value' => trim($request->address),
                'description' => $request->description ?? '---'
            ]);
            return response()->json([
                'message' => "Valor de la llave: $request->key actualizado correctamente. La dirección de la empresa ha sido actualizada en todo el sistema."
            ], 200);
        } else if ($request->type === 'RUC_CHANGE') {
            $request->validate([
                'key' => ['required', 'string', 'exists:settings,key'],
                'ruc' => ['required', 'string', 'min:11', 'max:11'],
                'description' => ['nullable', 'string', 'max:255'],
            ]);
            $setting = Setting::where('key', $request->key)->first();
            $setting->update([
                'value' => trim($request->ruc),
                'description' => $request->description ?? '---'
            ]);
            return response()->json([
                'message' => "Valor de la llave: $request->key actualizado correctamente. El RUC de la empresa ha sido actualizada en todo el sistema."
            ], 200);
        }
        return response()->json([
            'message' => "No se pudo realizar la operación. Los parámetros ingresados son inválidos."
        ], 400);
    }

    /**
     * Retorna objeto Setting en base a su clave.
     * @param mixed $key
     * @return mixed|\Illuminate\Http\JsonResponse
     */
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

    /**
     * Retorna el listado de todas las variables de configuracion del sistema, incluye filtrado.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
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
