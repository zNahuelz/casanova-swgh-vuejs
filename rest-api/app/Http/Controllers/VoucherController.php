<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\PendingPayment;
use App\Models\Treatment;
use App\Models\VoucherSeries;
use App\Models\VoucherType;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    public function createVoucher(Request $request)
    {
        $request->validate([
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.type' => ['required', 'string'],
            'cart.*.id' => ['required', 'numeric'],
            'cart.*.amount' => ['required', 'numeric'],
            'cart.*.price' => ['required', 'numeric'],
            //'cart.*.subtotal' => ['required', 'numeric'], ///No requerido?
            'cart.*.igv' => ['required', 'numeric'],

            'subtotal' => ['required', 'numeric'],
            'igv' => ['required', 'numeric'],
            'total' => ['required', 'numeric'],
            'created_by' => ['required', 'exists:users,id'],
            'payment_type_id' => ['required', 'exists:payment_types,id'],
            'patient_id' => ['required', 'exists:patients,id'],
            'voucher_type' => ['required', 'string', 'min:6', 'max:15'],
            'change' => ['required', 'numeric'],
            'payment_hash' => ['required','string'],
        ]);

        $cartItems = $request->cart;
        $productsIds = [];
        $treatmentsIds = [];
        $pendingPaymentsIds = [];

        $voucherType = VoucherType::where('name', $request->voucher_type)->first();
        if (!$voucherType) {
            return response()->json([
                'message' => "No se encontro el tipo de voucher especificado: $request->voucher_type"
            ], 422);
        }

        $voucherSerie = VoucherSeries::where('voucher_type', $voucherType->id)->first();
        if (!$voucherSerie) {
            return response()->json([
                'message' => "No se encontro la configuración de serie para la emisión de comprobantes del tipo de voucher de ID: $request->voucher_type"
            ], 422);
        }

        foreach ($cartItems as $e) {
            if ($e['type'] == 'PRODUCT') {
                $med = Medicine::find($e['id']);
                if ($med) {
                    $productsIds[] = $med->id;
                }
            }
            if ($e['type'] == 'TREATMENT') {
                $trt = Treatment::find($e['id']);
                if ($trt) {
                    $treatmentsIds[] = $trt->id;
                }
            }
            if ($e['type'] == 'PENDING_PAYMENT') {
                $pP = PendingPayment::find($e['id']);
                if ($pP) {
                    $pendingPaymentsIds[] = $pP->id;
                }
            }
        }

        //Stop doing pp jokes and continue with this so it saves the voucher and generates the pdf.
        return response()->json([
            'products' => $productsIds,
            'treatments' => $treatmentsIds,
            'ppJoke' => $pendingPaymentsIds,
            'voucherType' => $voucherType,
            'voucherSerie' => $voucherSerie,
        ]);
    }
}
