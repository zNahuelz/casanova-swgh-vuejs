<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\PaymentType;
use App\Models\PendingPayment;
use App\Models\Treatment;
use App\Models\VoucherDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Retorna listado general de pagos pendientes.
     * @return \Illuminate\Http\Response
     */
    public function getPendingPayments()
    {
        $payments = PendingPayment::query()->orderBy('created_at', 'asc')->paginate(20);
        return response($payments, 200);
    }

    /**
     * Retorna listado de pagos pendientes por DNI de paciente.
     * @param mixed $dni
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getPendingPaymentsByDni($dni)
    {
        $pendingPayments = PendingPayment::where(function ($query) use ($dni) {
            $query->whereHas('appointment.patient', function ($q) use ($dni) {
                $q->where('dni', $dni);
            });
        })->with([
            'appointment.patient',
        ])->get();

        if (sizeof($pendingPayments) <= 0) {
            return response()->json([
                'message' => 'El paciente no cuenta con pagos pendientes de ningún tipo.'
            ], 404);
        }
        return response()->json($pendingPayments);
    }

    /**
     * Retorna listado de reembolsos pendientes.
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getPendingRefunds()
    {
        $refunds = PendingPayment::where('notes', 'like', '%REEMBOLSO%')->with(['appointment', 'treatment'])->orderBy('created_at', 'desc')->get();
        return response()->json($refunds, sizeof($refunds) <= 0 ? 404 : 200);
    }

    /**
     * Elimina permanentemente un reembolso pendiente.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function deleteRefund($id)
    {
        $refund = PendingPayment::find($id);
        if (!$refund) {
            return response()->json([
                'message' => "Reembolso de ID: $id no encontrado. Intente nuevamente o comuniquese con administración."
            ], 404);
        }
        if (str_contains($refund->notes, 'REEMBOLSO')) {
            $refund->delete();
            return response()->json([
                'message' => "Recordatorio de reembolso de ID: $id eliminado correctamente."
            ], 200);
        } else {
            return response()->json([
                'message' => 'El elemento seleccionado no pertenece a un reembolso pendiente, comuniquese con administración'
            ], 500);
        }
    }

    /**
     * Retorna detalle de pago segun ID de cita.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getInfoByAppointmentId($id)
    {
        $pendingPayment = PendingPayment::where('appointment_id', $id)->first();
        $payment = VoucherDetail::where('appointment_id', $id)->first();
        if (!$pendingPayment) {
            if (!$payment) {
                return response()->json([
                    'message' => "No se encontro información sobre el pago de la cita de ID: $id Intente nuevamente o comuniquese con administración."
                ], 404);
            }
            return response()->json([
                'payment' => $payment,
                'type' => 'PAYMENT_OK'
            ], 200);
        }
        $pendingPaymentType = str_contains($pendingPayment->notes, 'REEMBOLSO') ? 'REFUND_PENDING' : 'PENDING_PAYMENT';
        return response()->json([
            'payment' => $pendingPayment,
            'type' => $pendingPaymentType //'PENDING_PAYMENT'
        ], 200);
    }

    /**
     * Retorna todos los tipos de pagos. (Efectivo, yape, plin, tarjeta)
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getPaymentTypes()
    {
        $paymentTypes = PaymentType::all();
        if (sizeof($paymentTypes) <= 0) {
            return response()->json([
                'message' => 'No se encontraron tipos de pago registrados.'
            ], 404);
        }
        return response()->json($paymentTypes, 200);
    }

    /**
     * Verifica el contenido del carrito de compra para la posterior generacion de voucher.
     * Retorna listado de elementos a remover del carrito.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function verifyShoppingCart(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products' => ['sometimes', 'array', 'min:1'],
            'products.*' => ['numeric'],

            'treatments' => ['sometimes', 'array', 'min:1'],
            'treatments.*' => ['numeric'],

            'pending_payments' => ['sometimes', 'array', 'min:1'],
            'pending_payments.*' => ['numeric'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $arrays = ['products', 'treatments', 'pending_payments'];

            $hasAtLeastOne = collect($arrays)->contains(function ($field) use ($request) {
                return is_array($request->input($field)) && count($request->input($field)) > 0;
            });

            if (!$hasAtLeastOne) {
                $validator->errors()->add('items', 'Al menos 1 elemento de tipo producto, tratamiento o pago pendiente debe estar presente.');
            }
        });
        $validator->validate();

        $itemsToRemove = [
            'products' => [],
            'treatments' => [],
            'pending_payments' => []
        ];

        if (count($request->products ?? []) >= 1) {
            foreach ($request->products as $p) {
                $product = Medicine::find($p);
                if (!$product) {
                    $itemsToRemove['products'][] = $p;
                }
            }
        }

        if (count($request->treatments ?? []) >= 1) {
            foreach ($request->treatments as $t) {
                $treatment = Treatment::find($t);
                if (!$treatment) {
                    $itemsToRemove['treatments'][] = $t;
                }
            }
        }

        if (count($request->pending_payments ?? []) >= 1) {
            foreach ($request->pending_payments as $pp) //pp joke lol
            {
                $pendingPayment = PendingPayment::find($pp);
                if (!$pendingPayment) {
                    $itemsToRemove['pending_payments'][] = $pp;
                }
            }
        }

        if (
            count($itemsToRemove['products'] ?? []) >= 1 ||
            count($itemsToRemove['treatments'] ?? []) >= 1 ||
            count($itemsToRemove['pending_payments'] ?? []) >= 1
        ) {
            return response()->json([
                'message' => 'Debe remover los siguientes elementos del carrito de compras debido a que no se encuentran disponibles.',
                'itemsToRemove' => $itemsToRemove
            ], 422);
        } else {
            return response()->json([
                'message' => 'Todos los elementos del carrito de compras son validos, proceda con el pago.'
            ], 200);
        }
    }
}
