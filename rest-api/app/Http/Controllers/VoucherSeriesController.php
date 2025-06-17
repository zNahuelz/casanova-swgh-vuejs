<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\VoucherSeries;
use App\Models\VoucherType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VoucherSeriesController extends Controller
{
    public function getVoucherSeries()
    {
        $types = VoucherType::all();
        $allSeries = VoucherSeries::all()->groupBy('voucher_type');
        foreach ($types as $type) {
            $seriesForThisType = $allSeries->get($type->id, collect());
            $type->setRelation('voucher_series', $seriesForThisType);
        }
        return response()->json($types);
    }

    public function createVoucherSerie(Request $request)
    {
        $request->validate([
            'serie' => ['required', 'string', 'regex:/^[BF](00[1-9]|0[1-9][0-9]|[1-9][0-9]{2})$/', Rule::unique('voucher_series', 'serie'), Rule::unique('vouchers', 'set')],
            'next_correlative' => ['required', 'numeric', 'min:1'],
            'type' => ['required', 'string', 'in:BOL,FACT'],
        ]);
        if ($request->type === 'BOL') {
            $parent = VoucherType::where('name', 'BOLETA')->first();
        } else {
            $parent = VoucherType::where('name', 'FACTURA')->first();
        }

        if (!$parent) {
            return response()->json([
                'message' => 'Error interno del servidor. No se encontro el tipo de voucher indicado para la creación de la serie. Comuniquese con administración.'
            ], 500);
        }

        try {
            DB::beginTransaction();
            $newSerie = VoucherSeries::create([
                'voucher_type' => $parent->id,
                'serie' => trim(strtoupper($request->serie)),
                'next_correlative' => $request->next_correlative,
                'is_active' => false,
            ]);
            DB::commit();
            return response()->json([
                'message' => "Serie creada correctamente. Asignado formato SERIE-PROX. CORRELATIVO: $newSerie->serie - $newSerie->next_correlative Recuerde habilitar la serie para su uso."
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error interno del servidor, operación cancelada. Vuelva a intentarlo nuevamente o comuniquese con administración.'
            ], 500);
        }
    }

    public function updateVoucherSerie(Request $request)
    {
        $request->validate([
            'serie_id' => ['required','numeric','exists:voucher_series,id'],
            'next_correlative' => ['required', 'numeric', 'min:1','max:999999'],
        ]);

        $voucherSerie = VoucherSeries::find($request->serie_id);
        if(!$voucherSerie)
        {
            return response()->json([
                'message' => "No se encontro la serie de ID: $request->serie_id Intente nuevamente o comuniquese con administración."
            ],404);
        }

        $voucherSerie->update([
            'next_correlative' => $request->next_correlative,
        ]);
        return response()->json([
            'message' => "Serie actualizada correctamente. Próximo correlativo asignado: $voucherSerie->next_correlative Recuerde habilitar la serie de ser necesario.",
        ],200);
    }

    public function enableVoucherSerie(Request $request)
    {
        $request->validate([
            'serie_id' => ['required', 'numeric', 'exists:voucher_series,id']
        ]);

        $voucherSerie = VoucherSeries::find($request->serie_id);
        if (!$voucherSerie) {
            return response()->json([
                'message' => "Serie de voucher de ID: $request->serie_id no encontrada. Intente nuevamente o comuniquese con administración",
            ], 404);
        }

        try {
            DB::beginTransaction();
            $series = VoucherSeries::where('voucher_type', $voucherSerie->voucher_type)->get();
            foreach ($series as $s) {
                $s->update(['is_active' => false]);
            }
            $voucherSerie->update([
                'is_active' => true
            ]);
            DB::commit();
            return response()->json([
                'message' => "Serie de voucher de ID: $voucherSerie->id y SERIE-CORRRELATIVO: $voucherSerie->serie - $voucherSerie->next_correlative activada correctamente. Los próximos comprobantes de pago se emitiran bajo la misma."
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'No se pudo habilitar la serie de voucher seleccionada. Intente nuevamente o comuniquese con administración.'
            ], 500);
        }
    }
}
