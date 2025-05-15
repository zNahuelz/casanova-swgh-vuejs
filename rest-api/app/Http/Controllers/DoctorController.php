<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorAvailability;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class DoctorController extends Controller
{
    //TODO: TEST....!
    public function createDoctor(Request $request)
    {
        $validator =  Validator::make($request->all(), [
            'name' => ['required', 'string', 'min:2', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'paternal_surname' => ['nullable', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'maternal_surname' => ['nullable', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'dni' => ['required', 'string', 'min:8', 'max:15', 'regex:/^[0-9]{8,15}$/', Rule::unique('doctors', 'dni')],
            'email' => ['required', 'email', 'max:50', Rule::unique('doctors', 'email'), Rule::unique('users', 'email'), Rule::unique('workers','email')],
            'phone' => ['required', 'string', 'min:6', 'max:15', 'regex:/^\+?\d{6,15}$/'],
            'address' => ['required', 'string', 'min:5', 'max:100'],
            'created_by' => ['required', 'numeric', 'exists:users,id'],
            'availabilities' => ['required', 'array', 'min:5', 'max:7'],
            'availabilities.*.weekday' => ['required', 'integer', 'between:1,7'],
            'availabilities.*.start_time' => ['required', 'date_format:H:i'],
            'availabilities.*.end_time' => ['required', 'date_format:H:i'],
            'availabilities.*.break_start' => ['required', 'date_format:H:i'],
            'availabilities.*.break_end' => ['required', 'date_format:H:i']
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
                    'is_active' => true,
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

    public function updateDoctor(Request $request)
    {
        return response()->json($request);
    }

    public function getDoctors(Request $request)
    {
        $query = Doctor::with(['availabilities','unavailabilities']);

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
        $doctor = Doctor::where('id',$id)->with(['availabilities', 'unavailabilities'])->first();
        if (!$doctor) {
            return response()->json([
                'message' => 'Doctor de ID: ' . $id . ' no encontrado.',
            ], 404);
        }

        return response()->json($doctor, 200);
    }
}
