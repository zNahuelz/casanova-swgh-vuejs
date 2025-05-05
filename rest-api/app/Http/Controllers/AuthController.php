<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPasswordMail;
use App\Models\RecoveryToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request) 
    {
        $request->validate([
            'username' => ['required', 'string', 'min:5', 'max:20'],
            'password' => ['required', 'string', 'min:5', 'max:20']
        ]);

        $credentials = $request->only('username', 'password');

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
            'expiration' => Carbon::now()->addMinutes(5),
        ]);

        Mail::to($user->email)->send(new ForgotPasswordMail($user,$recoveryLink));
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
}
