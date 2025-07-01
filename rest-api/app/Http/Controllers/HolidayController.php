<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HolidayController extends Controller
{
    /**
     * Retorna listado de feriados, incluye filtrado.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getHolidays(Request $request)
    {
        $query = Holiday::query();

        if ($request->has('id')) {
            $query->where('id', $request->input('id'));
        }
        if ($request->has('name')) {
            $query->where('name', 'ilike', '%' . $request->input('name') . '%');
        }
        if ($request->has('date')) {
            $query->whereDate('date', $request->input('date'));
        }

        $holidays = $query->get();
        return response()->json($holidays, 200);
    }

    /**
     * Permite registrar un feriado posterior a validaciones.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function createHoliday(Request $request)
    {
        $payloadDate = Carbon::parse($request->input('date'));

        $request->validate([
            'name' => [
                'required',
                'string',
                'min:5',
                'max:100',
                Rule::unique('holidays', 'name'),
            ],
            'date' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('holidays', 'date'),
                function ($attribute, $value, $fail) use ($payloadDate) {
                    $month = $payloadDate->month;
                    $day = $payloadDate->day;

                    $conflict = Holiday::where('is_recurring', true)
                        ->whereMonth('date', $month)
                        ->whereDay('date', $day)
                        ->exists();

                    if ($conflict) {
                        $fail('La fecha seleccionada ya se encuentra registrada como un feriado recurrente.');
                    }
                }
            ],
            'is_recurring' => ['required', 'boolean'],
        ]);

        try {
            DB::beginTransaction();
            $holiday = Holiday::create([
                'name' => trim(strtoupper($request->name)),
                'date' => $request->date,
                'is_recurring' => $request->is_recurring,
            ]);
            DB::commit();
            $auxText = $holiday->is_recurring ? 'El feriado se repetirá en los próximos años automáticamente.' : 'El feriado solo fue configurado para el año seleccionado.';
            return response()->json([
                'message' => "Feriado creado correctamente. Asignado ID: $holiday->id y nombre: $holiday->name. $auxText"
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error, no se pudo guardar el feriado ingresado. Intente nuevamente.'
            ], 500);
        }
    }

    /**
     * Permite actualizar un feriado existente posterior a validaciones.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function updateHoliday(Request $request)
    {
        $request->validate([
            'id' => ['required', 'numeric', 'exists:holidays,id'],
            'name' => [
                'required',
                'string',
                'min:5',
                'max:100',
                Rule::unique('holidays', 'name')->ignore($request->id)
            ],
            'date' => [
                'required',
                'date_format:Y-m-d',
                Rule::unique('holidays', 'date')->ignore($request->id),
                function ($attribute, $value, $fail) use ($request) {
                    $payloadDate = Carbon::parse($value);
                    $month = $payloadDate->month;
                    $day = $payloadDate->day;
                    $conflict = Holiday::where('is_recurring', true)
                        ->whereMonth('date', $month)
                        ->whereDay('date', $day)
                        ->where('id', '!=', $request->id)
                        ->exists();

                    if ($conflict) {
                        $fail('La fecha seleccionada ya pertenece a un feriado recurrente.');
                    }
                }
            ],
            'is_recurring' => ['required', 'boolean']
        ]);
        $holiday = Holiday::findOrFail($request->id);
        try {
            DB::beginTransaction();
            $holiday->update([
                'name' => trim(strtoupper($request->name)),
                'date' => $request->date,
                'is_recurring' => $request->is_recurring,
            ]);
            DB::commit();
            return response()->json([
                'message' => "Feriado de ID: $request->id actualizado correctamente: $request->name"
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error interno del servidor, actualización de feriado cancelada. Intente nuevamente.'
            ], 500);
        }
    }

    /**
     * Permite eliminar un feriado (permanentemente) segun ID.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function deleteHoliday($id)
    {
        $holiday = Holiday::find($id);
        if (!$holiday) {
            return response()->json([
                'message' => "No se encontró el feriado de ID: $id. Vuelva a intentarlo o comuniquese con administración."
            ], 404);
        }

        $holiday->forceDelete();
        return response()->json([
            'message' => "Feriado de ID: $id eliminado correctamente.",
        ], 200);
    }
}
