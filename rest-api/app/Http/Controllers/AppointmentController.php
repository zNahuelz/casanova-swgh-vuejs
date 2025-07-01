<?php

namespace App\Http\Controllers;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Jobs\SendAppointmentCanceledReminder;
use App\Jobs\SendAppointmentReminder;
use App\Jobs\SendAppointmentRescheduledReminder;
use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Models\DoctorAvailability;
use App\Models\DoctorUnavailability;
use App\Models\Holiday;
use App\Models\Patient;
use App\Models\PendingPayment;
use App\Models\Setting;
use App\Models\VoucherDetail;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Enum;

class AppointmentController extends Controller
{
    /**
     * Valida la solicitud, prepara un listado de espacios disponibles para reserva de citas para un doctor y paciente.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
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

    /**
     * Valida el payload para la preparación de una cita.
     * @param \Illuminate\Http\Request $request
     * @return array{days_ahead: int, doctor_id: mixed, is_treatment: mixed, on_date: mixed, patient_id: mixed, show_unavailabilities: mixed, slot_length: int}
     */
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

    /**
     * Cargas las disponibilidades de un doctor segun ID.
     * @param int $doctorId
     * @return \Illuminate\Database\Eloquent\Collection<int|string, DoctorAvailability>
     */
    private function loadWeeklyAvailability(int $doctorId)
    {
        return DoctorAvailability::where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->get()
            ->keyBy('weekday'); // ==>> Asignar DÍA de la semana a CLAVE:valor.
    }

    /**
     * Cargas las indisponibilidades de un doctor segun ID.
     * @param int $doctorId
     * @return \Illuminate\Database\Eloquent\Collection<int, DoctorUnavailability>
     */
    private function loadUnavailabilities(int $doctorId)
    {
        return DoctorUnavailability::where('doctor_id', $doctorId)
            ->whereNull('deleted_at')
            ->get();
    }

    /**
     * Carga las citas reservadas de un doctor y paciente segun un rango temporal.
     * @param array $data
     * @param int $doctorId
     * @return array<\Illuminate\Database\Eloquent\Collection<int, Appointment>|\Illuminate\Database\Eloquent\Collection<int, Carbon>|\Illuminate\Support\Collection<int, Carbon>>
     */
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

    /**
     * Carga todos los feriados.
     * @return \Illuminate\Database\Eloquent\Collection<int, Holiday>
     */
    private function loadHolidays()
    {
        return Holiday::all();
    }

    /**
     * Genera un listado de espacios disponibles para reserva de citas (multiples días).
     * @param mixed $weekly
     * @param mixed $unavs
     * @param mixed $doctorAppts
     * @param mixed $patientAppts
     * @param mixed $holidays
     * @param array $data
     * @return array<array|array{is_available: bool, reason: mixed, slots: array|array{is_available: bool, reason: string, slots: array}>}
     */
    private function buildSchedule($weekly, $unavs, $doctorAppts, $patientAppts, $holidays, array $data)
    {
        $response = [];
        $endDay   = Carbon::today()->copy()->addDays($data['days_ahead']);

        for ($day = Carbon::today(); $day->lte($endDay); $day->addDay()) {
            $response[] = $this->buildDayEntry($day, $weekly, $unavs, $doctorAppts, $patientAppts, $holidays, $data);
        }
        return $response;
    }

    /**
     * Genera los espacios disponibles para reserva de citas (día especifico)
     * @param \Carbon\Carbon $day
     * @param mixed $weekly
     * @param mixed $unavs
     * @param mixed $doctorAppts
     * @param mixed $patientAppts
     * @param mixed $holidays
     * @param array $data
     * @return array<array|array{is_available: bool, reason: mixed, slots: array|array{is_available: bool, reason: string, slots: array}>}
     */
    private function buildSingleDateSchedule(Carbon $day, $weekly, $unavs, $doctorAppts, $patientAppts, $holidays, array $data)
    {
        return [
            $this->buildDayEntry($day, $weekly, $unavs, $doctorAppts, $patientAppts, $holidays, $data)
        ];
    }

    /**
     * Hijo de buildSingleDateSchedule.
     * @param \Carbon\Carbon $day
     * @param mixed $weekly
     * @param mixed $unavs
     * @param mixed $doctorAppts
     * @param mixed $patientAppts
     * @param mixed $holidays
     * @param array $data
     * @return array|array{is_available: bool, reason: mixed, slots: array|array{is_available: bool, reason: string, slots: array}}
     */
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

    /**
     * Genera espacios de tiempo para un dia especifico basado en disponibilidad, descansos y citas existentes.
     * @param \Carbon\Carbon $day
     * @param mixed $availability
     * @param mixed $unavs
     * @param mixed $doctorAppts
     * @param mixed $patientAppts
     * @param array $data
     * @return array
     */
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

    /**
     * Guarda la reserva de cita posterior a la validación de las reglas del negocio, envia email de notificación.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
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
        $costValue = Setting::where('key', 'COSTO_CITA_REGULAR')->first();
        try {
            DB::beginTransaction();
            $appointmentCost = 120;
            if ($costValue) {
                $appointmentCost = doubleval($costValue->value);
            }
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
            //TODO: Remove if doesnt work.
            $pendingPayment = PendingPayment::create([
                'appointment_id' => $appointment->id,
                'value' => $appointmentCost,
                'notes' => "PAGO PENDIENTE DE CITA: $appointment->id -- FECHA RESERVA: $appointment->date / HORA RESERVA: $appointment->time"
            ]);
            DB::commit();
            if ($appointment->patient->email != 'EMAIL@DOMINIO.COM') //TODO: Remove on deployment.
            {
                SendAppointmentReminder::dispatch($appointment)->delay(now()->addSeconds(15));
            }
            return response()->json([
                'message' => "Cita correctamente reservada. Asignado ID: $appointment->id - Solicitud de pago generada bajo ID: $pendingPayment->id",
                'appointment' => $appointment,
                'pending_payment' => $pendingPayment,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error en la reserva de cita. Comuniquese con administración o intente nuevamente.',
                'ex' => $e,
                'exm' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reprograma una cita existente posterior a validacion y envia notificación via email.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function rescheduleAppointment(Request $request)
    {
        $request->validate([
            'appointment_id'   => ['required', 'exists:appointments,id'],
            'new_date'         => ['required', 'date_format:Y-m-d'],
            'new_time'         => ['required', 'date_format:H:i:s'],
            'doctor_id'        => ['required', 'exists:doctors,id'],
            'status'           => ['required', new Enum(AppointmentStatus::class)],
            'is_remote' => ['required', 'boolean'],
            'updated_by'       => ['required', 'exists:users,id'],
        ]);

        $appointment = Appointment::with(['patient', 'doctor'])->findOrFail($request->appointment_id);
        $patientId   = $appointment->patient_id;
        $newDoctorId = $request->doctor_id;
        $newDate     = $request->new_date;
        $newTime     = $request->new_time;

        $weekday = Carbon::parse($newDate)->dayOfWeekIso;
        $avail   = DoctorAvailability::where('doctor_id', $newDoctorId)
            ->where('weekday', $weekday)
            ->first();

        if (!$avail || !$avail->is_active) {
            return response()->json([
                'message' => 'El doctor no está disponible para el día seleccionado.',
                'status'  => 'UNAVAILABLE_DAY'
            ], 422);
        }

        $workStart  = $avail->start_time->copy()->setDate(0, 1, 1);
        $workEnd    = $avail->end_time->copy()->setDate(0, 1, 1);
        $breakStart = $avail->break_start->copy()->setDate(0, 1, 1);
        $breakEnd   = $avail->break_end->copy()->setDate(0, 1, 1);
        $newTimeC   = Carbon::createFromFormat('H:i:s', $newTime)->setDate(0, 1, 1);

        if ($newTimeC->lt($workStart) || $newTimeC->gte($workEnd)) {
            return response()->json([
                'message' => 'La nueva hora está fuera del horario laboral del doctor.',
                'status'  => 'TIME_OUTSIDE_WORK_HOURS'
            ], 422);
        }
        if ($newTimeC->gte($breakStart) && $newTimeC->lt($breakEnd)) {
            return response()->json([
                'message' => 'La nueva hora coincide con el descanso del doctor.',
                'status'  => 'TIME_WITHIN_BREAK'
            ], 422);
        }

        $conflictQuery = function ($query, $field, $value) use ($newDate, $newTime, $appointment) {
            $query->where($field, $value)
                ->where(function ($q) use ($newDate, $newTime) {
                    $q->where(fn($q2) => $q2->where('date', $newDate)->where('time', $newTime))
                        ->orWhere(fn($q2) => $q2->where('rescheduling_date', $newDate)
                            ->where('rescheduling_time', $newTime));
                })
                ->where('id', '!=', $appointment->id);
        };

        $patientConflict = Appointment::where('patient_id', $patientId)
            ->where(fn($q) => $conflictQuery($q, 'patient_id', $patientId))
            ->exists();
        if ($patientConflict) {
            return response()->json([
                'message' => 'El paciente ya tiene una cita en esa fecha y hora.',
                'status'  => 'DUPLICATED_APPOINTMENT_CURRENT_PATIENT'
            ], 422);
        }

        $doctorConflict = Appointment::where('doctor_id', $newDoctorId)
            ->where(fn($q) => $conflictQuery($q, 'doctor_id', $newDoctorId))
            ->exists();
        if ($doctorConflict) {
            return response()->json([
                'message' => 'El doctor ya tiene una cita en esa fecha y hora.',
                'status'  => 'DUPLICATED_APPOINTMENT_DIFF_PATIENT'
            ], 422);
        }

        try {
            DB::beginTransaction();
            $appointment->update([
                'doctor_id'           => $newDoctorId,
                'rescheduling_date'   => $newDate,
                'rescheduling_time'   => $newTime,
                'status'              => $request->status,
                'is_remote'           => $request->is_remote,
                'updated_by'          => $request->updated_by,
            ]);
            DB::commit();

            if ($appointment->patient->email !== 'EMAIL@DOMINIO.COM') {
                SendAppointmentRescheduledReminder::dispatch($appointment)
                    ->delay(now()->addSeconds(15));
            }

            return response()->json([
                'message'     => "Cita reprogramada correctamente. ID: {$appointment->id}",
                'appointment' => $appointment,
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al reprogramar la cita. Intente nuevamente.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancela una cita, gestiona reembolsos y envia notificación via email.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function cancelAppointment($id)
    {
        $appointment = Appointment::find($id);
        if (!$appointment) {
            return response()->json([
                'message' => "Cita de ID: $id no encontrada, intente nuevamente o comuniquese con administración."
            ]);
        }
        $pendingPayment = PendingPayment::where('appointment_id', $appointment->id)->first();
        $voucherDetail = VoucherDetail::where('appointment_id', $appointment->id)->first();
        try {
            DB::beginTransaction();
            if ($pendingPayment) {
                $pendingPayment->delete();
            }
            if ($voucherDetail) {
                $refund = PendingPayment::create([
                    'appointment_id' => $appointment->id,
                    'treatment_id' => null,
                    'notes' => "REEMBOLSO PENDIENTE DE CITA CANCELADA: $appointment->id -- FECHA RESERVA: $appointment->date / HORA RESERVA: $appointment->time",
                    'value' => $voucherDetail->subtotal,
                ]);
            }
            $appointment->update([
                'status' => AppointmentStatus::Canceled,
            ]);
            $appointment->delete();
            DB::commit();

            if ($appointment->patient->email !== 'EMAIL@DOMINIO.COM') {
                SendAppointmentCanceledReminder::dispatch($appointment, $voucherDetail ? true : false)
                    ->delay(now()->addSeconds(15));
            }

            $response = !$pendingPayment ? "Cita de ID: $id cancelada correctamente. Recordatorio de reembolso generado bajo ID: $refund->id" : "Cita de ID: $id cancelada correctamente, no requiere reembolso debido a que el paciente no realizó el pago.";
            return response()->json([
                'message' => $response
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error interno del servidor, operación cancelada. Comuniquese con administración.',
            ], 500);
        }
    }

    /**
     * Retorna lista de citas con filtros, paginacion y citas eliminadas.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getAppointments(Request $request)
    {
        $query = Appointment::withTrashed()->with(['doctor', 'patient']);

        if ($request->has('id')) {
            $query->where('id', $request->input('id'));
        }

        if ($request->filled('doctor_dni')) {
            $dni = $request->input('doctor_dni');
            $query->whereHas(
                'doctor',
                fn($q) =>
                $q->where('dni', 'like', "%{$dni}%")
            );
        }

        if ($request->filled('patient_dni')) {
            $dni = $request->input('patient_dni');
            $query->whereHas(
                'patient',
                fn($q) =>
                $q->where('dni', 'like', "%{$dni}%")
            );
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('is_remote')) {
            $isRemote = filter_var($request->input('is_remote'), FILTER_VALIDATE_BOOLEAN);
            $query->where('is_remote', $isRemote);
        }

        if ($request->filled('date')) {
            $exact = Carbon::createFromFormat('Y-m-d', $request->input('date'));
            $query->whereDate('date', $exact);
        }

        $usingDateFrom = false;
        if ($request->filled('date_from')) {
            $usingDateFrom = true;
            $from = Carbon::createFromFormat('Y-m-d', $request->input('date_from'))->startOfDay();
            $query->whereDate('date', '>=', $from);
        }

        if ($usingDateFrom) {
            $query->orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->orderBy('doctor_id', 'asc');
        } else {
            $query->orderBy('doctor_id', 'asc')
                ->orderByDesc('date')
                ->orderByDesc('time');
        }

        $perPage = (int) $request->input('per_page', 15);
        $paginated = $query->paginate($perPage);
        return response()->json($paginated);
    }

    /**
     * Retorna cita especifica por ID. Incluye citas eliminadas.
     * @param mixed $id
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getAppointmentById($id)
    {
        $appointment = Appointment::withTrashed()
            ->where('id', $id)
            ->with(['doctor', 'patient'])
            ->first();
        if (!$appointment) {
            return response()->json([
                'message' => "Cita de ID: {$id} no encontrada."
            ], 404);
        }

        return response()->json($appointment, 200);
    }

    /**
     * Actualizar notas y estado de cita.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function fillAppointmentNotes(Request $request)
    {
        $request->validate([
            'appointment_id' => ['required', 'exists:appointments,id'],
            'updated_by' => ['required', 'exists:users,id'],
            'notes' => ['required', 'string', 'min:5', 'max:255'],
            'status' => ['required', new Enum(AppointmentStatus::class)],
        ]);
        $appointment = Appointment::find($request->appointment_id);
        if (!$appointment) {
            return response()->json([
                'message' => "Cita de ID: $request->appointment_id no encontrada, vuelva a intentarlo."
            ], 404);
        }
        try {
            DB::beginTransaction();
            $appointment->update([
                'notes' => trim(strtoupper($request->notes)),
                'updated_by' => $request->updated_by,
                'status' => $request->status,
            ]);
            DB::commit();
            return response()->json([
                'message' => "Notas y estado de cita de ID: $request->appointment_id actualizados correctamente."
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Actualización de notas de la cita de ID: $request->appointment_id fallida. Vuelva a intentarlo.",
                'ex' => $e,
            ], 500);
        }
    }

    /**
     * Editar notas de cita.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function editAppointmentNotes(Request $request)
    {
        $request->validate([
            'appointment_id' => ['required', 'exists:appointments,id'],
            'updated_by' => ['required', 'exists:users,id'],
            'notes' => ['required', 'string', 'min:5', 'max:255'],
        ]);
        $appointment = Appointment::find($request->appointment_id);
        if (!$appointment) {
            return response()->json([
                'message' => "Cita de ID: $request->appointment_id no encontrada, vuelva a intentarlo."
            ], 404);
        }

        try{
            DB::beginTransaction();
            $appointment->update([
                'notes' => trim(strtoupper($request->notes)),
                'updated_by' => $request->updated_by,
            ]);
            DB::commit();
                        return response()->json([
                'message' => "Notas de la cita de ID: $request->appointment_id actualizadas correctamente."
            ], 200);
        }
        catch(Exception $e)
        {
            DB::rollBack();
                        return response()->json([
                'message' => "Actualización de notas de la cita de ID: $request->appointment_id fallida. Vuelva a intentarlo.",
                'ex' => $e,
            ], 500);
        }
    }

    /**
     * Retorna listado de citas por DNI de paciente.
     * @param \Illuminate\Http\Request $request
     * @return mixed|\Illuminate\Http\JsonResponse
     */
    public function getAppointmentNotesByDni(Request $request)
    {
        $request->validate([
            'patient_dni' => ['required', 'string', 'exists:patients,dni']
        ]);

        $notes = Appointment::with(['patient', 'doctor'])
            ->whereHas('patient', function ($query) use ($request) {
                $query->where('dni', 'like', $request->patient_dni);
            })
            ->get();
        return response()->json($notes);
    }
}
