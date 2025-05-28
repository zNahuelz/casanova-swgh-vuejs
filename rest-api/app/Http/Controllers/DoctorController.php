<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\DoctorUnavailability;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator as ValidationValidator;

class DoctorController extends Controller
{

    public function createDoctor(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:2', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'paternal_surname' => ['nullable', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'maternal_surname' => ['nullable', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'dni' => ['required', 'string', 'min:8', 'max:15', 'regex:/^[0-9]{8,15}$/', Rule::unique('doctors', 'dni'), Rule::unique('workers', 'dni')],
            'email' => ['required', 'email', 'max:50', Rule::unique('doctors', 'email'), Rule::unique('users', 'email'), Rule::unique('workers', 'email')],
            'phone' => ['required', 'string', 'min:6', 'max:15', 'regex:/^\+?\d{6,15}$/'],
            'address' => ['required', 'string', 'min:5', 'max:100'],
            'created_by' => ['required', 'numeric', 'exists:users,id'],
            'availabilities' => ['required', 'array', 'min:5', 'max:7'],
            'availabilities.*.weekday' => ['required', 'integer', 'between:1,7'],
            'availabilities.*.start_time' => ['required', 'date_format:H:i'],
            'availabilities.*.end_time' => ['required', 'date_format:H:i'],
            'availabilities.*.break_start' => ['required', 'date_format:H:i'],
            'availabilities.*.break_end' => ['required', 'date_format:H:i'],
            'availabilities.*.is_active' => ['required', 'boolean'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $seenWeekdays = [];
            $availabilities = $request->input('availabilities', []);
            foreach ($availabilities as $index => $a) {
                $weekday = $a['weekday'];
                if (in_array($weekday, $seenWeekdays)) {
                    $validator->errors()->add('availabilities.$index.weekday', 'Día de la semana duplicado: $weekday');
                }
                $seenWeekdays[] = $weekday;

                $start = Carbon::createFromFormat('H:i', $a['start_time']);
                $end = Carbon::createFromFormat('H:i', $a['end_time']);
                $breakStart = Carbon::createFromFormat('H:i', $a['break_start']);
                $breakEnd = Carbon::createFromFormat('H:i', $a['break_end']);

                if ($start->greaterThanOrEqualTo($end)) {
                    $validator->errors()->add("availabilities.$index.end_time", 'El horario de cierre debe estar despues del horario de inicio.');
                }

                // Validar break dentro de horario laboral.
                if ($breakStart->lessThan($start) || $breakEnd->greaterThan($end)) {
                    $validator->errors()->add("availabilities.$index.break_start", 'El horario del break debe estar dentro del horario laboral..');
                }

                // Validar break_end > break_start
                if ($breakStart->greaterThanOrEqualTo($breakEnd)) {
                    $validator->errors()->add("availabilities.$index.break_end", 'El final del break debe ser luego del inicio del break.');
                }

                if (count($seenWeekdays) !== count(array_unique($seenWeekdays))) {
                    $validator->errors()->add('availabilities', 'No se permiten valores duplicados en los días de la semana.');
                }
            }
        });
        $validator->validate();

        $userName = strtoupper(substr(trim($request->name), 0, 1))
            . trim($request->dni)
            . random_int(1, 9);

        $password = strrev($userName);

        $role = Role::where('name', 'DOCTOR')->first();
        if (!$role) {
            return response()->json([
                'message' => 'El rol de: DOCTOR no ha sido encontrado.'
            ]);
        }

        try {
            DB::beginTransaction();

            $user = User::create([
                'username' => $userName,
                'password' => Hash::make($password),
                'email' => strtoupper(trim($request->email)),
                'role_id' => $role->id,
            ]);

            $doctor = Doctor::create([
                'name' => strtoupper(trim($request->name)),
                'paternal_surname' => strtoupper(trim($request->paternal_surname)) ?? '',
                'maternal_surname' => strtoupper(trim($request->maternal_surname)) ?? '',
                'dni' => trim($request->dni),
                'email' => strtoupper(trim($request->email)),
                'phone' => trim($request->phone),
                'address' => trim(strtoupper($request->address)),
                'created_by' => $request->created_by,
                'user_id' => $user->id
            ]);

            foreach ($request->availabilities as $av) {
                DoctorAvailability::create([
                    'doctor_id' => $doctor->id,
                    'weekday' => $av['weekday'],
                    'start_time' => $av['start_time'],
                    'end_time' => $av['end_time'],
                    'break_start' => $av['break_start'],
                    'break_end' => $av['break_end'],
                    'is_active' => $av['is_active'],
                ]);
            }
            DB::commit();
            return response()->json([
                'message' => 'Doctor registrado correctamente. Asignado ID: ' . $doctor->id,
                'doctor' => $doctor,
                'user' => $user,
                'availabilities' => $request->availabilities,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error durante el registro del doctor o asignación de horario. Vuelva a intentarlo.'
            ], 500);
        }
    }

    public function updateDoctorInfo(Request $request, $id)
    {
        $oldDoctor = Doctor::find($id);
        if (!$oldDoctor) {
            return response()->json([
                'message' => 'Error. Doctor de ID: ' . $id . ' no encontrado.'
            ], 404);
        }

        $userFromDoctor = User::find($oldDoctor->user_id);
        if (!$userFromDoctor) {
            return response()->json([
                'message' => 'Error. Imposible actualizar doctor, el mismo no tiene cuenta asociada. Comuniquese con administración.'
            ], 404);
        }

        //TODO: Something fails? Here!
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'paternal_surname' => ['nullable', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'maternal_surname' => ['nullable', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'dni' => ['required', 'string', 'min:8', 'max:15', 'regex:/^[0-9]{8,15}$/', Rule::unique('doctors', 'dni')->ignore($id), Rule::unique('workers', 'dni')],
            'email' => ['required', 'email', 'max:50', Rule::unique('doctors', 'email')->ignore($id), Rule::unique('users', 'email')->ignore($userFromDoctor->id), Rule::unique('workers', 'email')],
            'phone' => ['required', 'string', 'min:6', 'max:15', 'regex:/^\+?\d{6,15}$/'],
            'address' => ['required', 'string', 'min:5', 'max:100'],
            'updated_by' => ['required', 'numeric', 'exists:users,id'],
        ]);

        try {
            DB::beginTransaction();

            $oldDoctor->update([
                'name' => strtoupper(trim($request->name)),
                'paternal_surname' => strtoupper(trim($request->paternal_surname)) ?? '',
                'maternal_surname' => strtoupper(trim($request->maternal_surname)) ?? '',
                'dni' => trim($request->dni),
                'email' => strtoupper(trim($request->email)),
                'phone' => trim($request->phone),
                'address' => trim(strtoupper($request->address)),
                'updated_by' => $request->updated_by,
            ]);

            $userFromDoctor->update([
                'email' => strtoupper(trim($request->email)),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Doctor actualizado correctamente.'
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error en la actualización de doctor. Operación cancelada. Vuelva a intentarlo.'
            ], 500);
        }
    }

    public function updateDoctorAvailabilities(Request $request, $id)
    {
        $oldDoctor = Doctor::find($id);
        if (!$oldDoctor) {
            return response()->json([
                'message' => 'Error. Doctor de ID: ' . $id . ' no encontrado.'
            ], 404);
        }

        $validator =  Validator::make($request->all(), [
            'availabilities' => ['required', 'array', 'min:5', 'max:7'],
            'availabilities.*.weekday' => ['required', 'integer', 'between:1,7'],
            'availabilities.*.start_time' => ['required', 'date_format:H:i'],
            'availabilities.*.end_time' => ['required', 'date_format:H:i'],
            'availabilities.*.break_start' => ['required', 'date_format:H:i'],
            'availabilities.*.break_end' => ['required', 'date_format:H:i'],
            'availabilities.*.is_active' => ['required', 'boolean']
        ]);

        $validator->after(function ($validator) use ($request) {
            $seenWeekdays = [];
            $availabilities = $request->input('availabilities', []);
            foreach ($availabilities as $index => $a) {
                $weekday = $a['weekday'];
                if (in_array($weekday, $seenWeekdays)) {
                    $validator->errors()->add("availabilities.$index.weekday", "Día de la semana duplicado: $weekday");
                }
                $seenWeekdays[] = $weekday;

                $start = Carbon::createFromFormat('H:i', $a['start_time']);
                $end = Carbon::createFromFormat('H:i', $a['end_time']);
                $breakStart = Carbon::createFromFormat('H:i', $a['break_start']);
                $breakEnd = Carbon::createFromFormat('H:i', $a['break_end']);

                if ($start->greaterThanOrEqualTo($end)) {
                    $validator->errors()->add("availabilities.$index.end_time", 'El horario de cierre debe estar despues del horario de inicio.');
                }

                // Validar break dentro de horario laboral.
                if ($breakStart->lessThan($start) || $breakEnd->greaterThan($end)) {
                    $validator->errors()->add("availabilities.$index.break_start", 'El horario del break debe estar dentro del horario laboral..');
                }

                // Validar break_end > break_start
                if ($breakStart->greaterThanOrEqualTo($breakEnd)) {
                    $validator->errors()->add("availabilities.$index.break_end", 'El final del break debe ser luego del inicio del break.');
                }

                if (count($seenWeekdays) !== count(array_unique($seenWeekdays))) {
                    $validator->errors()->add('availabilities', 'No se permiten valores duplicados en los días de la semana.');
                }
            }
        });
        $validator->validate();

        try {
            DB::beginTransaction();
            DoctorAvailability::where('doctor_id', $id)->forceDelete();
            $availabilities = $request->availabilities;

            // Wrap in a Collection, sort it, and then iterate
            collect($availabilities)
                ->sortBy('weekday')
                ->each(function ($av) use ($id) {
                    DoctorAvailability::create([
                        'doctor_id'   => $id,
                        'weekday'     => $av['weekday'],
                        'start_time'  => $av['start_time'],
                        'end_time'    => $av['end_time'],
                        'break_start' => $av['break_start'],
                        'break_end'   => $av['break_end'],
                        'is_active'   => $av['is_active'],
                    ]);
                });
            DB::commit();
            return response()->json([
                'message' => 'Disponibilidad del doctor de ID: ' . $id . ' actualizada correctamente',
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => "Error en la actualización de disponibilidad del doctor de ID: $id Operación cancelada. Vuelva a intentarlo.",
                $e->getMessage()
            ], 500);
        }
    }

    public function getDoctors(Request $request)
    {
        $query = Doctor::with(['availabilities', 'unavailabilities']);

        if ($request->has('id')) {
            $query->where('id', $request->input('id'));
        }

        if ($request->has('name')) {
            $query->where('name', 'ilike', '%' . $request->input('name') . '%');
        }

        if ($request->has('dni')) {
            $query->where('dni', 'like', '%' . $request->input('dni') . '%');
        }

        $sortField = $request->input('sort_by', 'name');
        $sortDirection = $request->input('sort_dir', 'asc');

        if (in_array($sortField, ['id', 'name', 'dni', 'created_at', 'updated_at'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $perPage = $request->input('per_page', 10);
        $doctors = $query->paginate($perPage);
        return response()->json($doctors, 200);
    }

    public function getDoctor($id)
    {
        $doctor = Doctor::where('id', $id)->with(['availabilities', 'unavailabilities', 'createdBy', 'updatedBy', 'user'])->first();
        if (!$doctor) {
            return response()->json([
                'message' => 'Doctor de ID: ' . $id . ' no encontrado.',
            ], 404);
        }

        return response()->json($doctor, 200);
    }

    public function getAvailableDoctors(Request $request)
    {
        $slotLength = 30;

        $doctors = Doctor::whereHas('availabilities', function ($q) {
            $q->where('is_active', true);
        })->with(['availabilities' => function ($q) {
            $q->where('is_active', true);
        }])->get();

        // Compute weekly availability slots and attach info
        $doctors = $doctors->map(function ($doctor) use ($slotLength) {
            // Total slots per week = sum of slots per availability day
            $weeklySlots = $doctor->availabilities->sum(function ($av) use ($slotLength) {
                $start      = Carbon::parse($av->start_time);
                $breakStart = Carbon::parse($av->break_start);
                $breakEnd   = Carbon::parse($av->break_end);
                $end        = Carbon::parse($av->end_time);

                // Morning period minutes
                $morningMinutes   = $breakStart->diffInMinutes($start);
                // Afternoon period minutes
                $afternoonMinutes = $end->diffInMinutes($breakEnd);

                $workingMinutes = $morningMinutes + $afternoonMinutes;
                return intdiv($workingMinutes, $slotLength);
            });

            // Attach human-readable weekly availability property
            $weeklySlots = str_replace('-', '', $weeklySlots);
            $doctor->current_week_availabilities = "{$weeklySlots} citas disponibles está semana.";
            return $doctor;
        });

        return response()->json($doctors);
    }

    public function getAllDoctors()
    {
        $doctors = Doctor::all();
        return response()->json($doctors, 200);
    }

    public function createUnavailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'start_datetime' => ['required', 'date'],
            'end_datetime' => ['required', 'date', 'after:start_datetime'],
            'reason' => ['nullable', 'string', 'min:5', 'max:20'],
            'doctor_id' => ['required', 'numeric', 'exists:doctors,id']
        ]);

        $validator->after(function ($validator) use ($request) {
            $start = Carbon::parse($request->input('start_datetime'));
            $end   = Carbon::parse($request->input('end_datetime'));
            $docId = $request->input('doctor_id');

            // Overlap check: any existing where existing.start <= new.end
            // and existing.end   >= new.start
            $overlap = DoctorUnavailability::where('doctor_id', $docId)
                ->where('start_datetime', '<=', $end)
                ->where('end_datetime',   '>=', $start)
                ->exists();

            if ($overlap) {
                $validator->errors()->add(
                    'start_datetime',
                    'Ya existe una indisponibilidad que se solapa con el rango de fechas ingresado.'
                );
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $unavailability = DoctorUnavailability::create([
            'doctor_id' => $request->doctor_id,
            'start_datetime' => $request->start_datetime,
            'end_datetime' => $request->end_datetime,
            'reason' => trim($request->reason ?? 'NO ESPECIFICADO')
        ]);

        return response()->json([
            'message' => 'Indisponibilidad creada correctamente. Asignado ID: ' . $unavailability->id,
            'unavailability' => $unavailability
        ], 201);
    }

    public function removeUnavailability($id)
    {
        $unav = DoctorUnavailability::find($id);
        if (!$unav) {
            return response()->json([
                'message' => "Indisponibilidad de ID: {$id} no encontrada. Intente nuevamente o comuníquese con administración."
            ], 404);
        }

        $today = Carbon::today();                         
        $startDate = Carbon::parse($unav->start_datetime)->startOfDay();
        $endOfToday = $today->copy()->endOfDay();     

        $data = [
            'end_datetime' => $endOfToday,
        ];

        //Si startDate es en el futuro setear startDate a HOY inicio día.
        //Entonces endDate sera HOY al final del día.
        //Si el inicio de indisponibilidad es en el pasado o HOY, final sera fin del dia, inicio permanece igual.
        if ($startDate->gt($today)) {
            $data['start_datetime'] = $today->copy()->startOfDay();
        }

        $unav->update($data);

        return response()->json([
            'message' => "Indisponibilidad modificada correctamente. " .
                "La reserva de citas del doctor se normalizará desde el día: " .
                $today->format('d-m-Y') . " a la medianoche."
        ], 200);
    }
}
