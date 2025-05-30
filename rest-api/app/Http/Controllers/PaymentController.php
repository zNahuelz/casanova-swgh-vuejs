<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PendingPayment;
use App\Models\VoucherDetail;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function getPendingPayments()
    {
        $payments = PendingPayment::query()->orderBy('created_at','asc')->paginate(20);
        return response($payments,200);
    }
    public function getPendingPaymentsByDni(Request $request)
    {
        //TODO....!
    }

    public function getInfoByAppointmentId($id)
    {
        $pendingPayment = PendingPayment::where('appointment_id',$id)->first();
        $payment = VoucherDetail::where('appointment_id',$id)->first();
        if(!$pendingPayment)
        {
            if(!$payment)
            {
                return response()->json([
                    'message' => "No se encontro información sobre el pago de la cita de ID: $id Intente nuevamente o comuniquese con administración."
                ],404);
            }
            return response()->json([
                'payment' => $payment,
                'type' => 'PAYMENT_OK'
            ],200);
        }
        return response()->json([
            'payment' => $pendingPayment,
            'type' => 'PENDING_PAYMENT'
        ],200);
    }
}
