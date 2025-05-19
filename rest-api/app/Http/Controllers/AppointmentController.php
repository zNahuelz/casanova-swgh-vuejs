<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorAvailability;
use App\Models\DoctorUnavailability;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    //TODO: REFACTOR....!
    public function prepareAppointment(Request $request)
    {

        $request->validate([
            'doctor_id'    => ['required', 'integer', 'exists:doctors,id'],
            'days_ahead'   => ['required', 'integer', 'min:1', 'max:30'],
            'slot_length'  => ['required', 'integer', 'min:1', 'max:240'],
            'is_treatment' => ['sometimes', 'boolean'],
        ]);

        $doctorId    = $request->doctor_id;
        $daysAhead   = (int)$request->days_ahead;
        $slotLength  = (int)$request->slot_length;
        $isTreatment = filter_var($request->query('is_treatment', false), FILTER_VALIDATE_BOOLEAN);

        // 2) Load the doctor’s weekly availabilities
        $weekly = DoctorAvailability::where('doctor_id', $doctorId)
            ->where('is_active', true)
            ->get()
            ->keyBy('weekday');

        // 3) Load unavailabilities
        $unavs = DoctorUnavailability::where('doctor_id', $doctorId)
            ->whereNull('deleted_at')
            ->get()
            ->map(fn($u) => [
                'from' => Carbon::parse($u->start_datetime),
                'to'   => Carbon::parse($u->end_datetime),
            ]);

        // 4) Load existing appointments
        $appts = Appointment::where('doctor_id', $doctorId)
            ->whereBetween('date', [
                Carbon::today()->toDateString(),
                Carbon::today()->addDays($daysAhead)->toDateString(),
            ])
            ->get()
            ->map(fn($a) => Carbon::parse("{$a->date} {$a->time}"));

        $response = [];
        $endDay = Carbon::today()->copy()->addDays($daysAhead);

        // 5) Loop through each day
        for ($day = Carbon::today(); $day->lte($endDay); $day->addDay()) {
            $dateKey = $day->toDateString();
            $slots = [];
            $dow = $day->isoWeekday();

            if (! isset($weekly[$dow])) {
                // No schedule for this weekday
                $response[] = [
                    'date'         => $dateKey,
                    'is_available' => false,
                    'slots'        => [],
                    'reason'       => 'No working schedule on this day',
                ];
                continue;
            }

            $w = $weekly[$dow];
            $breakStart = Carbon::parse($w->break_start);
            $breakEnd   = Carbon::parse($w->break_end);

            $periods = [
                ['from' => Carbon::parse($w->start_time), 'to' => $breakStart],
                ['from' => $breakEnd, 'to' => Carbon::parse($w->end_time)],
            ];

            foreach ($periods as $p) {
                $cursor = $p['from']->copy();
                while ($cursor->lt($p['to'])) {
                    $slotStart = $cursor->copy();
                    $slotEnd   = $slotStart->copy()->addMinutes($slotLength);
                    if ($slotEnd->gt($p['to'])) break;

                    $dtStart = $day->copy()->setTime($slotStart->hour, $slotStart->minute);

                    $booked = $appts->contains(fn($c) => $c->equalTo($dtStart));
                    $blocked = collect($unavs)->contains(fn($u) => $dtStart->betweenIncluded($u['from'], $u['to']));

                    if (! $booked && ! $blocked) {
                        $slots[] = [
                            'start_time' => $dtStart->toTimeString(),
                            'duration'   => $isTreatment ? 0 : $slotLength,
                        ];
                    }

                    $cursor->addMinutes($slotLength);
                }
            }

            $isAvailable = count($slots) > 0;
            $response[] = [
                'date'         => $dateKey,
                'is_available' => $isAvailable,
                'slots'        => $slots,
                'reason'       => $isAvailable ? null : ($w->reason ?? 'No available slots on this day'),
            ];
        }

        return response()->json($response);
    }
}
