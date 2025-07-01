<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Medicine;
use App\Models\PaymentType;
use App\Models\PendingPayment;
use App\Models\Presentation;
use App\Models\Setting;
use App\Models\Treatment;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\VoucherSeries;
use App\Models\VoucherType;
use Barryvdh\DomPDF\Facade\Pdf;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VoucherController extends Controller
{
    //TODO: Check that a set-correlative cant be duplicated.
    //But a correlative with a differnt set can be duplicated.
    /**
     * Permite el registro de un voucher en base a carrito de compra y datos de la misma.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function createVoucher(Request $request)
    {
        $request->validate([
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.type' => ['required', 'string'],
            'cart.*.id' => ['required', 'numeric'],
            'cart.*.amount' => ['required', 'numeric'],
            'cart.*.price' => ['required', 'numeric'],
            'cart.*.subtotal' => ['required', 'numeric'], ///No requerido?
            'cart.*.igv' => ['required', 'numeric'],

            'subtotal' => ['required', 'numeric'],
            'igv' => ['required', 'numeric'],
            'total' => ['required', 'numeric'],
            'created_by' => ['required', 'exists:users,id'],
            'payment_type_id' => ['required', 'exists:payment_types,id'],
            'patient_id' => ['required', 'exists:patients,id'],
            'voucher_type' => ['required', 'string', 'min:6', 'max:15'],
            'change' => ['required', 'numeric'],
            'payment_hash' => ['nullable', 'string'],
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

        $paymentType = PaymentType::find($request->payment_type_id);

        if ($paymentType->name !== 'EFECTIVO') {
            $hash = $request->payment_hash;
            if (is_null($hash) || strlen($hash) < 5) {
                return response()->json([
                    'message' => 'Debe proveer un payment_hash válido (al menos 5 caracteres) para métodos que no sean EFECTIVO.'
                ], 422);
            }
            $duplicatedHash = Voucher::where('payment_serial', $hash)->first();
            if ($duplicatedHash) {
                return response()->json([
                    'message' => 'La referencia de pago ingresada ya existe. Operación cancelada.'
                ], 422);
            }
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

        try {
            DB::beginTransaction();
            $voucherSerie = VoucherSeries::where('voucher_type', $voucherType->id)
                ->where('is_active',true) //TODO: Algo falla? Aca!
                ->lockForUpdate()
                ->first();

            if (!$voucherSerie) {
                DB::rollBack();
                return response()->json([
                    'message' => "No se encontró la serie para el tipo de voucher ID: {$voucherType->id}"
                ], 422);
            }
            //Verificar disponibilidad de correlativo y generar hasta encontrar valor valido.
            $next = $voucherSerie->next_correlative;
            $correlative = str_pad($next, 8, '0', STR_PAD_LEFT);
            while (Voucher::where('correlative', $correlative)
            ->where('set',$voucherSerie->serie)
            ->exists()) {
                $next++;
                $correlative = str_pad($next, 8, '0', STR_PAD_LEFT);
            }
            //Crear voucher con correlativo valido.
            $voucher = Voucher::create([
                'subtotal' => $request->subtotal,
                'igv' => $request->igv,
                'total' => $request->total,
                'paid' => true,
                'set' => $voucherSerie->serie,
                'correlative' => $correlative,
                'voucher_type' => $voucherType->id,
                'patient_id' => $request->patient_id,
                'payment_type_id' => $request->payment_type_id,
                'payment_serial' => $request->payment_hash ?? '',
                'change' => $request->change ?? 0,
                'created_by' => $request->created_by,
            ]);
            //Actualizar el valor para el proximo correlativo.
            $voucherSerie->next_correlative = $next + 1;
            $voucherSerie->save();

            foreach ($productsIds as $p) {
                $productInfo = collect($request->cart)->first(function ($item) use ($p) {
                    return $item['type'] === 'PRODUCT' && $item['id'] == $p;
                });
                $voucherDetail = VoucherDetail::create([
                    'amount' => $productInfo['amount'],
                    'unit_price' => $productInfo['price'],
                    'subtotal' => $productInfo['subtotal'],
                    'voucher_id' => $voucher->id,
                    'medicine_id' => $p,
                    'treatment_id' => null,
                    'appointment_id' => null,
                ]);
                $medicine = Medicine::find($p);
                $newStock = $medicine->stock - $productInfo['amount'];
                $newStock <= 0 ? $newStock = 0 : $newStock;
                $medicine->update([
                    'stock' => $newStock,
                ]);
            }

            foreach ($treatmentsIds as $t) {
                $treatmentInfo = collect($request->cart)->first(function ($item) use ($t) {
                    return $item['type'] === 'TREATMENT' && $item['id'] == $t;
                });
                $voucherDetail = VoucherDetail::create([
                    'amount' => 1,
                    'unit_price' => $treatmentInfo['price'],
                    'subtotal' => $treatmentInfo['subtotal'],
                    'voucher_id' => $voucher->id,
                    'medicine_id' => null,
                    'treatment_id' => $t,
                    'appointment_id' => null,
                ]);
            }

            foreach ($pendingPaymentsIds as $p) {
                $paymentInfo = collect($request->cart)->first(function ($item) use ($p) {
                    return $item['type'] === 'PENDING_PAYMENT' && $item['id'] == $p;
                });
                $pendingPayment = PendingPayment::find($p);

                $voucherDetail = VoucherDetail::create([
                    'amount' => 1,
                    'unit_price' => $paymentInfo['price'],
                    'subtotal' => $paymentInfo['subtotal'],
                    'voucher_id' => $voucher->id,
                    'medicine_id' => null,
                    'treatment_id' => null,
                    'appointment_id' => $pendingPayment->appointment_id,
                ]);
                $pendingPayment->delete();
            }
            DB::commit();
            $finalVoucher = Voucher::with(['voucherDetails', 'paymentType', 'voucherType'])->find($voucher->id);
            return response()->json([
                'message' => "Venta registrada correctamente, voucher generado bajo el ID: $voucher->id",
                'voucher' => $finalVoucher
            ], 201);
        } catch (Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error interno del servidor, venta cancelada. Intente nuevamente o comuniquese con sistemas.',
                'ex' => $ex
            ], 500);
        }
    }

    /**
     * Retorna un listado de vouchers incluyendo detalle de los mismos, de sus medicinas, citas, tratamientos, tipo de pago y comprador.
     * Incluye paginacion, filtrado, orden y cantidad de elementos.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getVouchers(Request $request)
    {
        $query = Voucher::with(['voucherDetails', 'voucherDetails.medicine', 'voucherDetails.appointment', 'voucherDetails.treatment', 'paymentType', 'patient']);

        if ($request->has('id')) { //TODO: Something fails? HERE!
            $query->where('id', $request->input('id'));
        }
        // Filtrar por nombre si esta presente.
        if ($request->has('correlative')) {
            $query->where('correlative', 'ilike', '%' . $request->input('correlative') . '%');
        }

        if ($request->has('set')) {
            $query->where('set', 'ilike', '%' . $request->input('set') . '%');
        }

        if ($request->has('dni')) {
            $query->whereHas('patient', function ($q) use ($request) {
                $q->where('dni', 'ilike', '%' . $request->input('dni') . '%');
            });
        }

        // Ordenado
        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_dir', 'desc');

        if (in_array($sortField, ['id', 'created_at', 'updated_at', 'correlative'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        // Pagination
        $perPage = $request->input('per_page', 10);
        $vouchers = $query->paginate($perPage);

        return response()->json($vouchers, 200);
    }

    /**
     * Retorna un voucher por ID. Incluye detalles, info. de medicina, cita, tratamiento, tipo de pago, comprador y detalle de voucher.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getVoucherById($id)
    {
        $voucher = Voucher::with(['voucherDetails', 'voucherDetails.medicine', 'voucherDetails.appointment', 'voucherDetails.treatment','paymentType','patient', 'voucherDetails.medicine.presentation'])->find($id);
        if (!$voucher) {
            return response()->json([
                'message' => "Voucher de ID: $id no encontrado, intente nuevamente.",
            ], 404);
        }
        return response()->json($voucher);
    }

    /**
     * Retorna PDF de voucher por ID.
     * @param \Illuminate\Http\Request $request
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function getVoucherPdfById(Request $request, $id)
    {
        $voucher = Voucher::find($id);
        if (!$voucher) {
            return response()->json([
                'message' => "Voucher de ID: $id no encontrado, intente nuevamente.",
            ], 404);
        }
        $address = Setting::where('key', 'VALOR_SEDE')->first();
        $ruc = Setting::where('key', 'VALOR_RUC')->first();
        $presentations = Presentation::all();
        if (!$address) {
            $address = 'Av. primavera 517, piso 1 oficina 103, San Borja - Lima , Lima, Peru';
        }
        if (!$ruc) {
            $ruc = '20524871701';
        }

        $logoPath = public_path('images/logo_transparent.png');
        $image = "data:image/png;base64," . base64_encode(file_get_contents($logoPath));

        $pdf = Pdf::loadView('pdfs.bol_voucher', [
            'data' => $voucher,
            'address' => $address,
            'ruc' => $ruc,
            'presentations' => $presentations,
            'logo' => $image,
        ]);
        $fileName = $voucher->set . '-' . $voucher->correlative . '.pdf';
        if($request->has('download')){
            return $pdf->download($fileName);
        }
        return $pdf->stream($fileName);
    }
}
