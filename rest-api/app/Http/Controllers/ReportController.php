<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Voucher;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Retorna un reporte de citas por mes o año.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function generateAppointmentsReport(Request $request)
    {
        $request->validate([
            'type' => ['required', 'string', 'in:by_month,by_year'],
            'date' => ['required', 'date_format:Y-m-d']
        ]);
        $date = Carbon::parse($request->date);

        if ($request->type === 'by_month') {
            $appointments = Appointment::withTrashed()->whereMonth('date', $date->month)
                ->whereYear('date', $date->year)
                ->get();
        } else {
            $appointments = Appointment::whereYear('date', $date->year)->get();
        }

        $total = $appointments->count();
        $totalRescheduled = $appointments->where('rescheduling_date','!==',null)->count();
        $totalCanceled = $appointments->where('status',AppointmentStatus::Canceled)->count();
        $attended = $appointments->where('status', AppointmentStatus::Attended)->count();
        $pending = $appointments->where('status', AppointmentStatus::Pending)->count();
        $remote = $appointments->where('is_remote', true)->count();
        $mostPopularDoctorId = $appointments->groupBy('doctor_id')
            ->sortByDesc(fn($group) => count($group))
            ->keys()
            ->first();
        $mostPopularDoctor = null;
        if ($mostPopularDoctorId) {
            $mostPopularDoctor = Doctor::find($mostPopularDoctorId);
        }

        $response = [
            'report_type' => $request->type,
            'total_reservations' => $total,
            'total_rescheduled' => $totalRescheduled,
            'total_canceled' => $totalCanceled,
            'attended_appointments' => $attended,
            'pending_appointments' => $pending,
            'total_remote' => $remote,
            'most_popular_doctor' => $mostPopularDoctor,
            'rescheduling_percentage' => $total > 0 ? round($totalRescheduled * 100 / $total, 2) : 0,
            'canceled_percentage' => $totalCanceled > 0 ? round($totalCanceled * 100 / $total, 2) : 0,
            'attending_percentage' => $attended > 0 ? round($attended * 100 / $total, 2) : 0,
            'pending_percentage' => $pending > 0 ? round($pending * 100 / $total, 2) : 0,
            'remote_percentage' => $remote > 0 ? round($remote * 100 / $total, 2) : 0,
        ];

        if ($total <= 0) {
            return response()->json([
                'message' => 'No se encontraron citas registradas durante el período ingresado. Vuelva a intentarlo seleccionando un período distinto o reserve algunas citas.'
            ], 404);
        }
        return response()->json($response, 200);
    }

    /**
     * Retorna un reporte de ventas por mes o año.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function generateSalesReport(Request $request)
    {
        $request->validate([
            'type' => ['required', 'string', 'in:by_month,by_year'],
            'date' => ['required', 'date_format:Y-m-d']
        ]);
        $date = Carbon::parse($request->date);

        if ($request->type === 'by_month') {
            $vouchers = Voucher::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->get();
        } else {
            $vouchers = Voucher::whereYear('created_at', $date->year)->get();
        }

        $total = $vouchers->count();
        $highest = $vouchers->sortByDesc('total')->first();
        $lowest = $vouchers->sortBy('total')->first();
        $paidCash = $vouchers->where('paymentType.name', 'EFECTIVO')->count();
        $paidCard = $vouchers->where('paymentType.name', 'TARJETA BANCARIA')->count();
        $paidYape = $vouchers->where('paymentType.name', 'YAPE')->count();
        $paidPlin = $vouchers->where('paymentType.name', 'PLIN')->count();
        $vtBoleta = $vouchers->where('voucherType.name', 'BOLETA')->count();
        $vtFactura = $vouchers->where('voucherType.name', 'FACTURA')->count();
        $averageSale = round($vouchers->average('total'), 2);
        $averageIgv = round($vouchers->average('igv'), 2);
        $averageSubtotal = round($vouchers->average('subtotal'), 2);
        $averageChange = round($vouchers->average('change'), 2);

        $response = [
            'report_type' => $request->type,
            'total_sales' => $total,
            'highest_sale' => $highest,
            'lowest_sale' => $lowest,
            'paid_cash' => $paidCash,
            'paid_card' => $paidCard,
            'paid_yape' => $paidYape,
            'paid_plin' => $paidPlin,
            'boleta' => $vtBoleta,
            'factura' => $vtFactura,
            'average_sale' => $averageSale,
            'average_igv' => $averageIgv,
            'average_subtotal' => $averageSubtotal,
            'average_change' => $averageChange,
        ];
        if ($total <= 0) {
            return response()->json([
                'message' => 'No se encontraron ventas registradas durante el período ingresado. Vuelva a intentarlo seleccionando un período distinto o registre algunas ventas.'
            ], 404);
        }
        return response()->json($response);
    }
}
