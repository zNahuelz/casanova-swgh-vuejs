<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorAvailability;
use App\Models\DoctorUnavailability;
use App\Models\Holiday;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function prepareAppointment(Request $request)
    {
        $data = $this->validatePrepareAppointmentRequest($request);

        $weekly = $this->loadWeeklyAvailability($data['doctor_id']);
        if ($weekly->isEmpty()) {
            return response()->json(['message' => 'El doctor no tiene horarios activos.'], 404);
        }

        $unavs = $this->loadUnavailabilities($data['doctor_id']);
        [$doctorAppts, $patientAppts] = $this->loadAppointments($data, $data['doctor_id']);
        $holidays = $this->loadHolidays();

        // Si on_date es presente, restringir resultado a solo día especificado.
        if ($data['on_date']) {
            $schedule = $this->buildSingleDateSchedule(
                Carbon::parse($data['on_date']),
                $weekly,
                $unavs,
                $doctorAppts,
                $patientAppts,
                $holidays,
                $data
            );
        } else {
            $schedule = $this->buildSchedule(
                $weekly,
                $unavs,
                $doctorAppts,
                $patientAppts,
                $holidays,
                $data
            );
        }

        // Si show_unavailabilities = false mostrar solo días disponibles en el rango indicado.
        if (!$data['show_unavailabilities']) {
            $schedule = array_values(array_filter($schedule, fn($day) => $day['is_available']));
        }
        return response()->json($schedule);
    }

    private function validatePrepareAppointmentRequest(Request $request): array
    {
        //sometimes ==> Valida si esta presente. (no es requerido...)
        $request->validate([
            'doctor_id'             => ['required', 'integer', 'exists:doctors,id'],
            'days_ahead'            => ['required_without:on_date', 'integer', 'min:1', 'max:100'],
            'slot_length'           => ['required', 'integer', 'min:1', 'max:240'],
            'patient_dni'           => ['required', 'string', 'exists:patients,dni'],
            'is_treatment'          => ['sometimes', 'boolean'],
            'show_unavailabilities' => ['sometimes', 'boolean'],
            'on_date'               => ['sometimes', 'date_format:Y-m-d'],
        ]);

        $patient = Patient::where('dni', $request->patient_dni)->first();
        if (!$patient) {
            response()->json(['message' => 'Paciente no encontrado'], 404)->throwResponse();
        }
        //Si on_date esta presente days_ahead 0. Sino days_ahead == //
        return [
            'doctor_id'             => $request->doctor_id,
            'days_ahead'            => $request->filled('on_date') ? 0 : (int) $request->days_ahead,
            'slot_length'           => (int) $request->slot_length,
            'is_treatment'          => filter_var($request->query('is_treatment', false), FILTER_VALIDATE_BOOLEAN),
            'patient_id'            => $patient->id,
            'show_unavailabilities' => filter_var($request->query('show_unavailabilities', true), FILTER_VALIDATE_BOOLEAN),
            'on_date'               => $request->input('on_date'),
        ];
    }

    private function loadWeeklyAvailability(int $doctorId)
    {
        return DoctorAvailability::where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->get()
            ->keyBy('weekday'); // ==>> Asignar DÍA de la semana a CLAVE:valor.
    }

    private function loadUnavailabilities(int $doctorId)
    {
        return DoctorUnavailability::where('doctor_id', $doctorId)
            ->whereNull('deleted_at')
            ->get();
    }

    private function loadAppointments(array $data, int $doctorId): array
    {
        $startDate = $data['on_date'] ?? Carbon::today()->toDateString();
        $endDate   = $data['on_date'] ? $data['on_date'] : Carbon::today()->addDays($data['days_ahead'])->toDateString();

        $all = Appointment::whereBetween('date', [$startDate, $endDate])->get();

        $doctorAppts = $all->where('doctor_id', $doctorId)
            ->map(fn($a) => Carbon::parse("{$a->date} {$a->time}"));

        $patientAppts = $all->where('patient_id', $data['patient_id']);

        return [$doctorAppts, $patientAppts];
    }

    private function loadHolidays()
    {
        return Holiday::all();
    }

    private function buildSchedule($weekly, $unavs, $doctorAppts, $patientAppts, $holidays, array $data)
    {
        $response = [];
        $endDay   = Carbon::today()->copy()->addDays($data['days_ahead']);

        for ($day = Carbon::today(); $day->lte($endDay); $day->addDay()) {
            $response[] = $this->buildDayEntry($day, $weekly, $unavs, $doctorAppts, $patientAppts, $holidays, $data);
        }
        return $response;
    }

    private function buildSingleDateSchedule(Carbon $day, $weekly, $unavs, $doctorAppts, $patientAppts, $holidays, array $data)
    {
        return [
            $this->buildDayEntry($day, $weekly, $unavs, $doctorAppts, $patientAppts, $holidays, $data)
        ];
    }

    private function buildDayEntry(Carbon $day, $weekly, $unavs, $doctorAppts, $patientAppts, $holidays, array $data)
    {
        $dateKey     = $day->toDateString();
        $weekdayName = $day->locale('es')->isoFormat('dddd');
        // Feriados...
        $holiday = $holidays->first(
            fn($h) =>
            Carbon::parse($h->date)->isSameDay($day) ||
                ($h->is_recurring && Carbon::parse($h->date)->format('m-d') === $day->format('m-d'))
        );
        if ($holiday) {
            return compact('dateKey', 'weekdayName') + [
                'is_available' => false,
                'slots'        => [],
                'reason'       => 'FERIADO: ' . $holiday->name,
            ];
        }

        $dow = $day->isoWeekday();
        if (! isset($weekly[$dow])) {
            return compact('dateKey', 'weekdayName') + [
                'is_available' => false,
                'slots'        => [],
                'reason'       => 'No laborable',
            ];
        }

        $avail     = $weekly[$dow];
        $workStart = Carbon::parse($avail->start_time);
        $workEnd   = Carbon::parse($avail->end_time);

        // Full-day block
        $fullBlock = $unavs->first(
            fn($u) =>
            Carbon::parse($u->start_datetime)->lte($day->copy()->setTimeFrom($workStart)) &&
                Carbon::parse($u->end_datetime)->gte($day->copy()->setTimeFrom($workEnd))
        );
        if ($fullBlock) {
            return compact('dateKey', 'weekdayName') + [
                'is_available' => false,
                'slots'        => [],
                'reason'       => $fullBlock->reason,
            ];
        }

        $slots  = $this->generateSlotsForDay($day, $avail, $unavs, $doctorAppts, $patientAppts, $data);
        $reason = count($slots) ? null : ($avail->reason ?? 'No hay espacios disponibles');

        return compact('dateKey', 'weekdayName') + [
            'is_available' => (bool) count($slots),
            'slots'        => $slots,
            'reason'       => $reason,
        ];
    }

    private function generateSlotsForDay(Carbon $day, $availability, $unavs, $doctorAppts, $patientAppts, array $data)
    {
        $slots = [];
        $workStart  = Carbon::parse($availability->start_time);
        $workEnd    = Carbon::parse($availability->end_time);
        $breakStart = Carbon::parse($availability->break_start);
        $breakEnd   = Carbon::parse($availability->break_end);

        foreach ([['from' => $workStart, 'to' => $breakStart], ['from' => $breakEnd, 'to' => $workEnd]] as $p) {
            $cursor = $p['from']->copy();
            while ($cursor->lt($p['to'])) {
                $slotStart = $cursor->copy();
                $slotEnd   = $slotStart->copy()->addMinutes($data['slot_length']);
                if ($slotEnd->gt($p['to'])) break;

                $dtStart          = $day->copy()->setTime($slotStart->hour, $slotStart->minute);
                $bookedByDoctor   = $doctorAppts->contains(fn($c) => $c->equalTo($dtStart));
                $blocked          = $unavs->contains(fn($u) => $dtStart->betweenIncluded(
                    Carbon::parse($u->start_datetime),
                    Carbon::parse($u->end_datetime)
                ));
                $conflictPatient  = $patientAppts->contains(
                    fn($a) =>
                    $a->doctor_id === $availability->doctor_id &&
                        $a->date      === $day->toDateString() &&
                        Carbon::parse($a->time)->equalTo($slotStart)
                );

                if (! $bookedByDoctor && ! $blocked) {
                    $slots[] = [
                        'start_time'                   => $dtStart->toTimeString(),
                        'duration'                     => $data['is_treatment'] ? 0 : $data['slot_length'],
                        'is_conflicting_with_patient' => $conflictPatient,
                    ];
                }
                $cursor->addMinutes($data['slot_length']);
            }
        }
        return $slots;
    }
}
