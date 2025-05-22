<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\SendForgotPasswordToken;
use App\Mail\ForgotPasswordMail;
use App\Models\Doctor;
use App\Models\RecoveryToken;
use App\Models\User;
use App\Models\Worker;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function login(Request $request) 
    {
        $request->validate([
            'username' => ['required', 'string', 'min:5', 'max:20'],
            'password' => ['required', 'string', 'min:5', 'max:20']
        ]);

        $credentials = $request->only('username', 'password');

        $user = User::withTrashed()
                ->where('username', $credentials['username'])
                ->first();

        if ($user && $user->trashed()) {
            return response()->json([
                'message' => 'Esta cuenta está deshabilitada. Comuníquese con administración',
                'aux' => 'ACCOUNT_DISABLED'
            ], 403); 
        }

        $token = Auth::guard('api')->attempt($credentials);
        if (!$token) 
        {
            return response()->json([
                'message' => 'Intento de inicio de sesión fallido.'
            ], 401);
        }

        $user = Auth::guard('api')->user()->load('role');

        if($user->role->name == 'ENFERMERA' || $user->role->name == 'SECRETARIA')
        {
            return response()->json([
                'auth' => [
                    'token' => $token,
                    'type' => 'Bearer'
                ],
                'userData' => $user->worker
            ],200);
        }
        else if($user->role->name == 'DOCTOR')
        {
            return response()->json([
                'auth' => [
                    'token' => $token,
                    'type' => 'Bearer'
                ],
                'userData' => $user->doctor
            ],200);
        }
        else
        {
            return response()->json([
                'auth' => [
                    'token' => $token,
                    'type' => 'Bearer'
                ]
            ],200);
        }
    }

    public function profile()
    {
        $user = auth()->user();
    
        if (!$user) {
            return response()->json([
                'message' => 'Error! Token expirado o invalido.',
            ], 401);
        }
    
        $response = ['user' => $user];
    
        if ($user->role->name == 'ENFERMERA' || $user->role->name == 'SECRETARIA') {
            $user->load(['role', 'worker']);
            $response['worker'] = $user->worker;
        } 
        else if ($user->role->name == 'DOCTOR') {
            $user->load(['role', 'doctor']);
            $response['doctor'] = $user->doctor;
        } 
        else {
            $user->load(['role']);
        }
    
        // Quitar relaciones de clase usuario para evitar duplicados.
        unset($user->doctor, $user->worker);
    
        return response()->json($response, 200);
    }

    public function sendRecoveryMail(Request $request)
    {
        $request->validate([
            'email' => ['required','email','max:50']
        ]);

        $user = User::where('email',strtoupper($request->email))->first();

        if(!$user)
        {
            return response()->json([
                'message' => 'Operación completada correctamente. Si el e-mail ingresado pertenece a un usuario las instrucciones para recuperar su cuenta seran enviadas.'
            ],200);
        }

        RecoveryToken::where('email',strtoupper($request->email))->delete();
        $token = Str::random(100);
        $recoveryLink = env('VUEJS_FRONTEND_URL','http://localhost:3000').'/recover-account?token='.$token;

        RecoveryToken::create([
            'email' => trim(strtoupper($request->email)),
            'token' => $token,
            'expiration' => Carbon::now()->addMinutes(10),
        ]);

        //Mail::to($user->email)->send(new ForgotPasswordMail($user,$recoveryLink));
        SendForgotPasswordToken::dispatch($user,$recoveryLink)->delay(now()->addMinutes(2)); //TODO: Ver 
        return response()->json([
            'message' => 'Operación completada correctamente. Si el e-mail ingresado pertenece a un usuario las instrucciones para recuperar su cuenta seran enviadas.'
        ],200);
    }

    public function verifyRecoveryToken(Request $request)
    {
        $request->validate([
            'token' => ['required','string','min:100','max:100']
        ]);

        $databaseToken = RecoveryToken::where('token',$request->token)->first();

        if(!$databaseToken)
        {
            return response()->json(['message' => 'Error! Token expirado o invalido'],404);
        }

        $expirationDate = Carbon::parse($databaseToken->expiration);
        $currentDate = Carbon::now();
        if($expirationDate->diffInMinutes($currentDate) >= 5)
        {
            return response()->json(['message' => 'Error! Token expirado o invalido'],404);
        }
        return response()->json([
            'message' => 'Token valido.'
        ],200);
    }

    public function changePasswordWithToken(Request $request)
    {
        $request->validate([
            'password' => ['required','string','min:5','max:20'],
            'token' => ['required','string','min:100','max:100']
        ]);

        $databaseToken = RecoveryToken::where('token',$request->token)->first();

        if(!$databaseToken)
        {
            return response()->json([
                'message' => 'Error! Token expirado o invalido.'
            ],404);
        }
        else 
        {
            $expirationDate = Carbon::parse($databaseToken->expiration);
            $currentDate = Carbon::now();

            if($expirationDate->diffInMinutes($currentDate) >= 5)
            {
                return response()->json(['message' => 'Error! Token expirado o invalido'],404);
            }

            $user = User::where('email', $databaseToken->email)->first();
            if(!$user)
            {
                return response()->json(['message' => 'Error! Usuario no encontrado.'],404);
            }

            $user->update([
                'password' => Hash::make($request->password)
            ]);

            $databaseToken->delete();
            return response()->json([
                'message' => 'Contraseña actualizada. Inicie sesión con sus nuevas credenciales.',
                'user' => $user,
            ],200);
        }
    }

    public function changePasswordAndEmail(Request $request)
    {
        $user = auth()->user();
    
        if (!$user) {
            return response()->json([
                'message' => 'Error! Token expirado o invalido.',
                'aux' => 'INVALID_TOKEN'
            ], 401);
        }

        $request->validate([
            'email' => ['required','email','max:50',Rule::unique('users','email')->ignore($user->id)],
            'current_password' => ['required','string','min:5','max:20'],
            'new_password' => ['required','string','min:5','max:20'],
        ]);

        $dbUser = User::find($user->id);
        $validPassword = Hash::check($request->current_password,$dbUser->password);
        
        if(!$validPassword)
        {
            return response()->json([
                'message' => 'La contraseña ingresada no coincide con los datos almacenados. Intente nuevamente.',
                'aux' => 'INVALID_PASSWORD'
            ],400);
        }
        $userRole = $dbUser->role->name;

        try
        {
            if($userRole == 'ADMINISTRADOR')
            {
                $dbUser->update([
                    'email' => trim(strtoupper($request->email)),
                    'password' => Hash::make($request->new_password)
                ]);
            }
            if($userRole == 'DOCTOR')
            {
                $doctor = Doctor::where('user_id',$dbUser->id)->first();
                $dbUser->update([
                    'email' => trim(strtoupper($request->email)),
                    'password' => Hash::make($request->new_password)
                ]);
                $doctor->update([
                    'email' => trim(strtoupper($request->email))
                ]);
            }
            if($userRole == 'ENFERMERA' || $userRole == 'SECRETARIA')
            {
                $worker = Worker::where('user_id',$dbUser->id)->first();
                $dbUser->update([
                    'email' => trim(strtoupper($request->email)),
                    'password' => Hash::make($request->new_password)
                ]);
                $worker->update([
                    'email' => trim(strtoupper($request->email))
                ]);
            }
        }
        catch(Exception $e)
        {
            return response()->json([
                'message' => 'Error en actualización de datos. Intente nuevamente',
                'aux' => 'SERVER_ERROR'
            ],500);
        }
        return response()->json([
            'message' => 'Correo y contraseña actualizados correctamente. Debe volver a iniciar sesión con sus nuevas credenciales.'
        ],200);
    }

    public function changeUsername(Request $request)
    {
        $user = auth()->user();
    
        if (!$user) {
            return response()->json([
                'message' => 'Error! Token expirado o invalido.',
                'aux' => 'INVALID_TOKEN'
            ], 401);
        }

        $request->validate([
            'username' => ['required','string','min:5','max:20',Rule::unique('users','username')->ignore($user->id)],
            'password' => ['required','string','min:5','max:20']
        ]);

        $oldUser = User::find($user->id);
        $validPassword = Hash::check($request->password,$oldUser->password);

        if(!$validPassword)
        {
            return response()->json([
                'message' => 'La contraseña ingresada no coincide con los datos almacenados. Intente nuevamente.',
                'aux' => 'INVALID_PASSWORD'
            ],400);
        }
  
        $oldUser->update([
            'username' => trim($request->username)
        ]);

        return response()->json([
            'message' => 'Nombre de usuario actualizado correctamente. </br> Nombre de usuario anterior: '.$user->username.'</br> Nuevo nombre de usuario: '.$request->username.'</br> Debe volver a iniciar sesión con sus nuevas credenciales.'
        ],200);
    }

    public function changeAddressAndPhone(Request $request)
    {
        $user = auth()->user();
    
        if (!$user) {
            return response()->json([
                'message' => 'Error! Token expirado o invalido.',
                'aux' => 'INVALID_TOKEN'
            ], 401);
        }

        $request->validate([
            'address' => ['required','max:100'],
            'phone' => ['required','string','min:6','max:15'],
            'password' => ['required','string','min:5','max:20']
        ]);

        $oldUser = User::find($user->id);
        $validPassword = Hash::check($request->password,$oldUser->password);

        if(!$validPassword)
        {
            return response()->json([
                'message' => 'La contraseña ingresada no coincide con los datos almacenados. Intente nuevamente.',
                'aux' => 'INVALID_PASSWORD'
            ],400);
        }

       $userRole = $user->role->name;
       if($userRole == 'ENFERMERA' || $userRole == 'SECRETARIA')
       {
            $oldWorker = Worker::where('user_id',$user->id)->first();
            $oldWorker->update([
                'address' => trim(strtoupper($request->address)),
                'phone' => trim($request->phone),
            ]);
       }
       else 
       {
            $oldDoctor = Doctor::where('user_id',$user->id)->first();
            $oldDoctor->update([
                'address' => trim(strtoupper($request->address)),
                'phone' => trim($request->phone),
            ]);
       }

       return response()->json([
            'message' => 'Información personal actualizada correctamente. Debe volver a iniciar sesión.',
       ]);
    }
}
