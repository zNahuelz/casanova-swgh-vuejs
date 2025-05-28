<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SendAppointmentReminder;
use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Models\DoctorAvailability;
use App\Models\DoctorUnavailability;
use App\Models\Holiday;
use App\Models\Patient;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Enum;

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

        $all = Appointment::where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('date', [$startDate, $endDate])
                ->orWhereBetween('rescheduling_date', [$startDate, $endDate]);
        })->get();

        $doctorAppts = $all->where('doctor_id', $doctorId)
            ->map(function ($a) {
                $date = $a->rescheduling_date ?? $a->date;
                $time = $a->rescheduling_time ?? $a->time;
                return Carbon::parse("$date $time");
            });

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
        $workStart  = Carbon::parse($availability->start_time);
        $workEnd    = Carbon::parse($availability->end_time);
        $breakStart = Carbon::parse($availability->break_start);
        $breakEnd   = Carbon::parse($availability->break_end);

        $slots = [];

        foreach (
            [
                ['from' => $workStart, 'to' => $breakStart],
                ['from' => $breakEnd,  'to' => $workEnd],
            ] as $p
        ) {
            $cursor = $p['from']->copy();

            while ($cursor->lt($p['to'])) {
                $slotEnd = $cursor->copy()->addMinutes($data['slot_length']);
                if ($slotEnd->gt($p['to'])) {
                    break;
                }

                $dtStart = $day->copy()->setTime($cursor->hour, $cursor->minute);

                $bookedByDoc = $doctorAppts->contains(fn($c) => $c->equalTo($dtStart));
                $blocked     = $unavs->contains(
                    fn($u) =>
                    $dtStart->betweenIncluded(
                        Carbon::parse($u->start_datetime),
                        Carbon::parse($u->end_datetime)
                    )
                );

                $isRescheduling = $patientAppts->contains(fn($a) => !is_null($a->rescheduling_date) || !is_null($a->rescheduling_time));

                $isOriginalSlot = $patientAppts->contains(function ($a) use ($day, $cursor) {
                    return $a->date === $day->toDateString() &&
                        Carbon::parse($a->time)->equalTo($cursor);
                });

                // Hide this slot if it's the original one for the same patient
                if ($isRescheduling && $isOriginalSlot) {
                    $cursor->addMinutes($data['slot_length']);
                    continue;
                }

                $conflictPatient = $patientAppts->contains(function ($a) use ($day, $cursor) {
                    $apptDate = $a->rescheduling_date ?? $a->date;
                    $apptTime = $a->rescheduling_time ?? $a->time;

                    return $apptDate === $day->toDateString() &&
                        Carbon::parse($apptTime)->equalTo($cursor);
                });

                if (! $bookedByDoc && ! $blocked) {
                    $slots[] = [
                        'start_time' => $dtStart->toTimeString(),
                        'duration' => $data['is_treatment'] ? 0 : $data['slot_length'],
                        'is_conflicting_with_patient' => $conflictPatient,
                    ];
                }

                $cursor->addMinutes($data['slot_length']);
            }
        }

        if ($day->isToday()) {
            $now = Carbon::now('America/Lima');
            $nowMins = $now->hour * 60 + $now->minute;

            $slots = array_values(array_filter($slots, function ($slot) use ($nowMins) {
                [$h, $m]  = explode(':', $slot['start_time']);
                $slotMins = ((int)$h) * 60 + ((int)$m);
                return $slotMins >= $nowMins;
            }));
        }

        return $slots;
    }

    public function createAppointment(Request $request)
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i:s'],
            'status' => ['required', new Enum(AppointmentStatus::class)],
            'is_remote' => ['required', 'boolean'],
            'duration' => ['required', 'integer', 'min:15', 'max:60'],
            'patient_id' => ['required', 'exists:patients,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'created_by' => ['required', 'exists:users,id'],
        ]);

        $date = $request->date;
        $time = $request->time;
        $patientId = $request->patient_id;
        $doctorId = $request->doctor_id;

        $weekday = Carbon::parse($date)->dayOfWeekIso; // ISO dayOfWeek: Monday=1 ... Sunday=7

        $availability = DoctorAvailability::where('doctor_id', $doctorId)
            ->where('weekday', $weekday)
            ->first();

        if (!$availability || !$availability->is_active) {
            return response()->json([
                'message' => 'El doctor no está disponible para el día seleccionado, intente nuevamente.',
                'status' => 'UNAVAILABLE_DAY'
            ], 422);
        }

        $workStart = $availability->start_time->copy()->setDate(0, 1, 1);
        $workEnd = $availability->end_time->copy()->setDate(0, 1, 1);
        $breakStart = $availability->break_start->copy()->setDate(0, 1, 1);
        $breakEnd = $availability->break_end->copy()->setDate(0, 1, 1);

        $appointmentTimeCarbon = Carbon::createFromFormat('H:i:s', $time)->setDate(0, 1, 1);

        if ($appointmentTimeCarbon->lt($workStart) || $appointmentTimeCarbon->gte($workEnd)) {
            return response()->json([
                'message' => 'La hora de la cita está fuera del horario laboral del doctor.',
                'status' => 'TIME_OUTSIDE_WORK_HOURS'
            ], 422);
        }

        if ($appointmentTimeCarbon->gte($breakStart) && $appointmentTimeCarbon->lt($breakEnd)) {
            return response()->json([
                'message' => 'La hora de la cita coincide con el descanso del doctor.',
                'status' => 'TIME_WITHIN_BREAK'
            ], 422);
        }

        $conflictPatient = Appointment::where('patient_id', $patientId)
            ->where(function ($q) use ($date, $time) {
                $q->where(function ($q2) use ($date, $time) {
                    $q2->where('date', $date)
                        ->where('time', $time);
                })
                    ->orWhere(function ($q2) use ($date, $time) {
                        $q2->where('rescheduling_date', $date)
                            ->where('rescheduling_time', $time);
                    });
            })
            ->exists();

        if ($conflictPatient) {
            return response()->json([
                'message' => 'Ya existe una cita para este paciente en esa fecha y hora.',
                'status' => 'DUPLICATED_APPOINTMENT_CURRENT_PATIENT'
            ], 422);
        }

        $conflictDoctor = Appointment::where('doctor_id', $doctorId)
            ->where(function ($q) use ($date, $time) {
                $q->where(function ($q2) use ($date, $time) {
                    $q2->where('date', $date)
                        ->where('time', $time);
                })
                    ->orWhere(function ($q2) use ($date, $time) {
                        $q2->where('rescheduling_date', $date)
                            ->where('rescheduling_time', $time);
                    });
            })
            ->exists();

        if ($conflictDoctor) {
            return response()->json([
                'message' => 'El doctor ya tiene una cita programada en esa fecha y hora.',
                'status' => 'DUPLICATED_APPOINTMENT_DIFF_PATIENT'
            ], 422);
        }

        try {
            DB::beginTransaction();
            $appointment = Appointment::create([
                'date' => $date,
                'time' => $time,
                'notes' => '...',
                'status' => $request->status,
                'is_remote' => $request->is_remote,
                'duration' => $request->duration,
                'rescheduling_date' => null,
                'rescheduling_time' => null,
                'is_treatment' => null,
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'created_by' => $request->created_by
            ]);
            DB::commit();
            if ($appointment->patient->email != 'EMAIL@DOMINIO.COM') //TODO: Remove on deployment.
            {
                SendAppointmentReminder::dispatch($appointment)->delay(now()->addMinutes(5));
            }
            return response()->json([
                'message' => "Cita correctamente reservada. Asignado ID: {$appointment->id}",
                'appointment' => $appointment,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error en la reserva de cita. Comuniquese con administración o intente nuevamente.',
                'ex' => $e
            ], 500);
        }
        //TODO: ---> Then make appointment list per doctor, general, patient etc...
        //TODO: Then do appointment reschedule.... (With the same doctor ONLY...???)
    }

    public function getAppointments(Request $request)
    {
        //TODO: Make UI and modify.
        $query = Appointment::with(['doctor', 'patient']);

        if ($request->filled('patient_dni')) {
            $dni = $request->input('patient_dni');
            $query->whereHas(
                'patient',
                fn($q) =>
                $q->where('dni', 'like', "%{$dni}%")
            );
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->input('doctor_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('date')) {
            // exact date match YYYY-MM-DD
            $query->whereDate('date', $request->input('date'));
        }

        if ($request->filled('is_remote')) {
            $query->where(
                'is_remote',
                filter_var($request->input('is_remote'), FILTER_VALIDATE_BOOLEAN)
            );
        }

        $today = Carbon::today();
        $query->whereDate('date', '>=', $today);

        $query->orderBy('date')->orderBy('time');

        $appointments = $query->get();

        $grouped = $appointments
            ->groupBy('doctor_id')
            ->map(function ($aps) {
                $doctor = $aps->first()->doctor;  // already eager-loaded
                return [
                    'doctor'       => $doctor->toArray(),
                    'appointments' => $aps->map->toArray()->values(),
                ];
            })
            ->values(); // reset keys

        return response()->json($grouped, 200);
    }
}
