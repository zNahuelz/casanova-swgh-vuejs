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
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = Carbon::parse($request->input('date'));
        $base = Appointment::withTrashed();

        if ($request->input('type') === 'by_month') {
            $base->whereMonth('date', $date->month)
                ->whereYear('date',  $date->year);
        } else {
            $base->whereYear('date',  $date->year);
        }

        $total            = (clone $base)->count();
        $totalCanceled    = (clone $base)
            ->where('status', AppointmentStatus::Canceled)
            ->count();
        $totalRescheduled = (clone $base)
            ->whereNotNull('rescheduling_date')
            ->count();
        $attended         = (clone $base)
            ->where('status', AppointmentStatus::Attended)
            ->count();
        $pending          = (clone $base)
            ->where('status', AppointmentStatus::Pending)
            ->count();
        $remote           = (clone $base)
            ->where('is_remote', true)
            ->count();

        if ($total === 0) {
            return response()->json([
                'message' => 'No se encontraron citas reservadas en el periodo ingresado. Intente nuevamente o reserve algunas citas.'
            ], 404);
        }

        $mostPopularDoctorId = (clone $base)
            ->select('doctor_id', DB::raw('COUNT(*) as cnt'))
            ->groupBy('doctor_id')
            ->orderByDesc('cnt')
            ->value('doctor_id');

        $mostPopularDoctor = $mostPopularDoctorId
            ? Doctor::find($mostPopularDoctorId)
            : null;

        $response = [
            'report_type'             => $request->input('type'),
            'total_reservations'      => $total,
            'total_rescheduled'       => $totalRescheduled,
            'total_canceled'          => $totalCanceled,
            'attended_appointments'   => $attended,
            'pending_appointments'    => $pending,
            'total_remote'            => $remote,
            'most_popular_doctor'     => $mostPopularDoctor,
            'rescheduling_percentage' => round($totalRescheduled * 100 / $total, 2),
            'canceled_percentage'     => round($totalCanceled   * 100 / $total, 2),
            'attending_percentage'    => round($attended        * 100 / $total, 2),
            'pending_percentage'      => round($pending         * 100 / $total, 2),
            'remote_percentage'       => round($remote          * 100 / $total, 2),
        ];

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
