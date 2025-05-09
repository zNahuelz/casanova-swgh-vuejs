<?php

namespace App\Http\Controllers;

use App\Enums\WorkerType;
use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Models\Worker;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class WorkerController extends Controller
{
    public function createWorker(Request $request)
    {
        $request->validate([
            'name' => ['required','string','min:2','max:30','regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'paternal_surname' => ['nullable','max:30','regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'maternal_surname' => ['nullable','max:30','regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'dni' => ['required','string','min:8','max:15','regex:/^[0-9]{8,15}$/',Rule::unique('workers','dni')],
            'email' => ['required','email','max:50', Rule::unique('workers','email'), Rule::unique('users','email')],
            'phone' => ['required','string','min:6','max:15','regex:/^\+?\d{6,15}$/'],
            'address' => ['required','string','min:5','max:100'],
            'hiring_date' => ['required','date'],
            'position' => ['required','string',new Enum(WorkerType::class)],
            'created_by' => ['required','numeric','exists:users,id'],
        ]);

        $userName = strtoupper(substr(trim($request->name),0,1))
        .trim($request->dni)
        .random_int(1,9);

        $password = strrev($userName);

        $role = Role::where('name',$request->position)->first();
        if(!$role)
        {
            return response()->json([
                'message' => 'El rol de: '.$request->position.' no ha sido encontrado.'
            ]);
        }

        try
        {
            DB::beginTransaction();

            $user = User::create([
                'username' => $userName,
                'password' => Hash::make($password),
                'email' => strtoupper(trim($request->email)),
                'role_id' => $role->id,
            ]);

            $worker = Worker::create([
                'name' => strtoupper(trim($request->name)),
                'paternal_surname' => strtoupper(trim($request->paternal_surname)) ?? '',
                'maternal_surname' => strtoupper(trim($request->maternal_surname)) ?? '',
                'dni' => trim($request->dni),
                'email' => strtoupper(trim($request->email)),
                'phone' => trim($request->phone),
                'address' => trim(strtoupper($request->address)),
                'hiring_date' => $request->hiring_date,
                'position' => strtoupper(trim($request->position)),
                'created_by' => $request->created_by,
                'user_id' => $user->id
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Trabajador registrado correctamente. Asignado ID: '.$worker->id.' y rol: '.$role->name,
                'worker' => $worker,
                'user' => $user
            ],201);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            if (isset($user) && $user->exists) {
                $user->forceDelete(); 
            }
            if (isset($worker) && $worker->exists) {
                $worker->forceDelete(); 
            }

            return response()->json([
                'message' => 'Error en el registro de trabajador o asignación automática de cuenta. Vuelva a intentarlo.'
            ],500);
        }
    }

    public function updateWorker(Request $request, $id)
    {
        $oldWorker = Worker::find($id);
        if(!$oldWorker)
        {
            return response()->json([
                'message' => 'Error. Trabajador de ID: '.$id.' no encontrado.'
            ],404);
        }

        $userFromWorker = User::find($oldWorker->user_id);
        if(!$userFromWorker)
        {
            return response()->json([
                'message' => 'Error. Imposible actualizar trabajador, el mismo no tiene cuenta asociada. Comuniquese con administración.'
            ],404);
        }

        $request->validate([
            'name' => ['required','string','min:2','max:30','regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'paternal_surname' => ['nullable','max:30','regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'maternal_surname' => ['nullable','max:30','regex:/^[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]{2,30}$/'],
            'dni' => ['required','string','min:8','max:15','regex:/^[0-9]{8,15}$/',Rule::unique('workers','dni')->ignore($id)],
            'email' => ['required','email','max:50', 
            Rule::unique('workers','email')->ignore($id),
            Rule::unique('users','email')->ignore($userFromWorker->id)
            ],
            'phone' => ['required','string','min:6','max:15','regex:/^\+?\d{6,15}$/'],
            'address' => ['required','string','min:5','max:100'],
            'hiring_date' => ['required','date'],
            'position' => ['required','string',new Enum(WorkerType::class)],
            'updated_by' => ['required','numeric','exists:users,id'],
        ]);

        $role = Role::where('name',$request->position)->first();
        if(!$role)
        {
            return response()->json([
                'message' => 'El rol de: '.$request->position.' no ha sido encontrado.'
            ]);
        }

        try
        {
            DB::beginTransaction();

            $oldWorker->update([
                'name' => strtoupper(trim($request->name)),
                'paternal_surname' => strtoupper(trim($request->paternal_surname)) ?? '',
                'maternal_surname' => strtoupper(trim($request->maternal_surname)) ?? '',
                'dni' => trim($request->dni),
                'email' => strtoupper(trim($request->email)),
                'phone' => trim($request->phone),
                'address' => trim(strtoupper($request->address)),
                'hiring_date' => $request->hiring_date,
                'position' => strtoupper(trim($request->position)),
                'updated_by' => $request->updated_by,
            ]);

            $userFromWorker->update([
                'email' => strtoupper(trim($request->email)),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Trabajador actualizado correctamente.'
            ],200);
        }
        catch(Exception $e)
        {
            DB::rollBack();
            return response()->json([
                'message' => 'Error en la actualización de trabajador. Operación cancelada. Vuelva a intentarlo.'
            ],500);
        }
    }

    public function getWorkers(Request $request)
    {
        $query = Worker::query();

        if ($request->has('id')) {
            $query->where('id', $request->input('id'));
        }

        if ($request->has('name')) {
            $query->where('name', 'ilike', '%' . $request->input('name') . '%');
        }

        if ($request->has('dni')) {
            $query->where('dni', 'like', '%' . $request->input('dni') . '%');
        }

        if ($request->has('position')) {
            $query->where('position', 'ilike', '%' . $request->input('position') . '%');
        }

        $sortField = $request->input('sort_by', 'id'); 
        $sortDirection = $request->input('sort_dir', 'asc'); 
    
        if (in_array($sortField, ['id', 'name', 'dni', 'position', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $perPage = $request->input('per_page', 10); 
        $workers = $query->paginate($perPage);
        return response()->json($workers, 200);
    }

    public function getWorker($id)
    {
        $worker = Worker::find($id);
        if(!$worker)
        {
            return response()->json([
                'message' => 'Trabajador de ID: '.$id.' no encontrado.'
            ],404);
        }
        $createdBy = User::find($worker->created_by);
        if(isset($worker->updated_by)){
            $updatedBy = User::find($worker->updated_by);
        }
        return response()->json([
            'worker' => $worker->load('user'),
            'createdBy' => $createdBy,
            'updatedBy' => $updatedBy ?? null,
        ],200);
    }
}
