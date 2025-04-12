<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        if (!$token) {
            return response()->json([
                'message' => 'Intento de inicio de sesión fallido.'
            ], 401);
        }

        return response()->json([
            'auth' => [
                'token' => $token,
                'type' => 'Bearer'
            ]
        ]);
    }

    public function profile()
    {
        $user = auth()->user();
        if(!$user)
        {
            return response()->json([
                'message' => 'Error! Token expirado o invalido.',
            ],401);
        }
        return response()->json($user->load('role'));
    }
}
