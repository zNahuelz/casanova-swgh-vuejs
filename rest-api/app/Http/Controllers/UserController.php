<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function createAdmin(Request $request)
    {
        $request->validate([
            'username' => ['required','string','min:5','max:20','regex:/^[a-zA-Z0-9@_.-]{5,20}$/',Rule::unique('users','username')],
            'email' => ['required','email','max:50',Rule::unique('users','email'),Rule::unique('doctors','email'),Rule::unique('workers','email')]
        ]);

        $role = Role::where('name','ADMINISTRADOR')->first();
        if(!$role)
        {
            return response()->json([
                'message' => 'El sistema no pudo encontrar el rol de ADMINISTRADOR. Comuniquese con administración.'
            ],500);
        }

        $password = trim(strrev($request->username));
        $user = User::create([
            'username' => trim($request->username),
            'password' => Hash::make($password),
            'email' => trim(strtoupper($request->email)),
            'role_id' => $role->id,
        ]);

        return response()->json([
            'message' => "Administrador creado correctamente con el nombre de usuario $user->username e ID: $user->id <br> La contraseña de la cuenta es el nombre de usuario invertido.",
            'user' => $user,
        ],201);
    }

    public function getUsers(Request $request)
    {
        $query = User::withTrashed()->with('role','doctor','worker');
        if ($request->has('id')) { 
            $query->where('id', $request->input('id'));
        }
        // Filtrar por nombre si esta presente.
        if ($request->has('username')) {
            $query->where('username', 'ilike', '%' . $request->input('username') . '%');
            //ILIKE -> Exclusivo de posgres. Non-case-sensitive search.
        }
        if ($request->has('email')) {
            $query->where('email', 'ilike', '%' . $request->input('email') . '%');
        }
        //Ordenar.
        $sortField = $request->input('sort_by', 'id'); // Ordenar por ID (Default)
        $sortDirection = $request->input('sort_dir', 'asc'); // Asc or Desc
        if (in_array($sortField, ['id','username', 'email', 'created_at'])) {
            $query->orderBy($sortField, $sortDirection);
        } //Verificar si el ordenado esta en el array.
        // Pagination
        $perPage = $request->input('per_page', 20); // Default: 20.
        $users = $query->paginate($perPage);

        $users->getCollection()->transform(function($user){
            $customUser = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'deleted_at' => $user->deleted_at
            ];
            $roleName = $user->role->name ?? '';
            if($roleName === 'DOCTOR'){
                $customUser['doctor'] = $user->doctor;
            }
            elseif (in_array($roleName, ['SECRETARIA','ENFERMERA'])){
                $customUser['worker'] = $user->worker;
            }
            return $customUser;
        });
        return response()->json($users,200);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'user_id' => ['required','numeric','exists:users,id'],
        ]);

        $user = User::find($request->user_id);
        if(!$user)
        {
            return response()->json([
                'message' => 'Usuario de ID: '.$request->id.' no encontrado.'
            ],404);
        }
        $currentYear = Carbon::now()->format('Y');
        $newPassword = strtoupper(trim($user->username)).$currentYear;
        $user->update([
            'password' => Hash::make($newPassword)
        ]);
        return response()->json([
            'message' => 'Contraseña de usuario de ID: '.$request->user_id.' reseteada correctamente.'
        ],200);
        
    }

    public function deleteUser($id)
    {
        $user = User::find($id);
        if(!$user)
        {
            return response()->json([
                'message' => 'Usuario de ID: '.$id.' no encontrado.'
            ],404);
        }
        $user->delete();
        return response()->json([
            'message' => 'Usuario de ID: '.$id.' deshabilitado correctamente.'
        ],200);
    }

    public function restoreUser($id)
    {
        $user = User::withTrashed()->where('id',$id);
        if(!$user)
        {
            return response()->json([
                'message' => 'Usuario de ID: '.$id.' no encontrado.'
            ],404);
        }
        $user->restore();
        return response()->json([
            'message' => 'Usuario de ID: '.$id.' habilitado correctamente.'
        ],200);
    }
}
