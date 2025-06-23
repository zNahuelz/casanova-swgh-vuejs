<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PatientController extends Controller
{
    public function createPatient(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'paternal_surname' => ['nullable', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'maternal_surname' => ['nullable', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'birth_date' => ['required', 'date_format:Y-m-d'],
            'dni' => ['required', 'string', 'min:8', 'max:15', 'regex:/^[0-9]{8,15}$/', Rule::unique('patients', 'dni')],
            'email' => ['required', 'email', 'max:50', Rule::unique('patients', 'email')],
            'phone' => ['required', 'string', 'min:6', 'max:15', 'regex:/^\+?\d{6,15}$/'],
            'address' => ['required', 'string', 'min:5', 'max:100'],
            'created_by' => ['required', 'numeric', 'exists:users,id'],
        ]);

        $patient = Patient::create([
            'name' => strtoupper(trim($request->name)),
            'paternal_surname' => strtoupper(trim($request->paternal_surname)) ?? '',
            'maternal_surname' => strtoupper(trim($request->maternal_surname)) ?? '',
            'birth_date' => $request->birth_date,
            'dni' => trim($request->dni),
            'email' => strtoupper(trim($request->email)),
            'phone' => trim($request->phone),
            'address' => trim(strtoupper($request->address)),
            'created_by' => $request->created_by,
        ]);

        return response()->json([
            'message' => 'Paciente registrado correctamente.',
            'patient' => $patient
        ], 201);
    }

    public function updatePatient(Request $request, $id)
    {
        $oldPatient = Patient::find($id);
        if (!$oldPatient) {
            return response()->json(['message' => "Paciente de ID: {$id} no encontrado."], 404);
        }

        $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'paternal_surname' => ['nullable', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'maternal_surname' => ['nullable', 'max:30', 'regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'birth_date' => ['required', 'date_format:Y-m-d'],
            'dni' => ['required', 'string', 'min:8', 'max:15', 'regex:/^[0-9]{8,15}$/', Rule::unique('patients', 'dni')->ignore($id)],
            'email' => ['required', 'email', 'max:50', Rule::unique('patients', 'email')->ignore($id)],
            'phone' => ['required', 'string', 'min:6', 'max:15', 'regex:/^\+?\d{6,15}$/'],
            'address' => ['required', 'string', 'min:5', 'max:100'],
            'updated_by' => ['required', 'numeric', 'exists:users,id'],
        ]);

        $oldPatient->update([
            'name' => strtoupper(trim($request->name)),
            'paternal_surname' => strtoupper(trim($request->paternal_surname)) ?? '',
            'maternal_surname' => strtoupper(trim($request->maternal_surname)) ?? '',
            'birth_date' => $request->birth_date,
            'dni' => trim($request->dni),
            'email' => strtoupper(trim($request->email)),
            'phone' => trim($request->phone),
            'address' => trim(strtoupper($request->address)),
            'updated_by' => $request->updated_by,
        ]);

        return response()->json([
            'message' => "Paciente de ID: {$id} actualizado correctamente."
        ], 200);
    }

    public function getPatients(Request $request)
    {
        $query = Patient::with('appointments');

        if ($request->has('id')) {
            $query->where('id', $request->input('id'));
        }

        if ($request->has('name')) {
            $query->where('name', 'ilike', '%' . $request->input('name') . '%');
        }

        if ($request->has('dni')) {
            $query->where('dni', 'like', '%' . $request->input('dni') . '%');
        }

        $sortField = $request->input('sort_by', 'created_at');
        $sortDirection = $request->input('sort_dir', 'desc');

        if (in_array($sortField, ['id', 'name', 'dni', 'position', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $perPage = $request->input('per_page', 10);
        $patients = $query->paginate($perPage);
        return response()->json($patients, 200);
    }

    public function getPatient($id)
    {
        $patient = Patient::with(['appointments' => function ($query) {
            $query->orderBy('date', 'desc')->orderBy('time', 'desc')->limit(5);
        }, 'createdBy:id,username','updatedBy:id,username','appointments.doctor','vouchers'])->findOrFail($id);

        return response()->json($patient);
    }
    public function getPatientByDni($dni)
    {
        $patient = Patient::with(['appointments' => function ($query) {
            $query->orderBy('date', 'desc')->orderBy('time', 'desc')->limit(5);
        }])->where('dni', $dni)->first();

        if (!$patient) {
            return response()->json([
                'message' => "Paciente de DNI: {$dni} no encontrado.",
                'code' => 404,
            ], 404);
        }
        return response()->json($patient, 200);
    }
}
