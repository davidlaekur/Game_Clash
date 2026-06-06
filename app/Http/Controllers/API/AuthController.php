<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class AuthController extends Controller
{


    // Registro de usuario
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:9',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4|confirmed',
            'role_id' => 'nullable|exists:roles,_id', 
            'team_id' => 'nullable|exists:teams,_id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id ?? null,  
            'team_id' => $request->team_id ?? null,  
            'points' => 0, 
            'zone_id' => null, 
        ]);

  /*    $user = User::find($user->_id); 

        $token = JWTAuth::fromUser($user); */

        return response()->json([
            'message' => 'Nuevo administrador registrado correctamente',
            'user' => $user,
        
        ], 201);
    }

    // Login de usuario
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        return response()->json(['token' => $token], 200);
    }

    // Logout de usuario
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'Sesión cerrada'], 200);
    }

    // Obtener perfil del usuario autenticado
    public function userProfile()
    {
        return response()->json(Auth::user(), 200);
    }

    // refrescar token
    public function refresh()
    {
        return response()->json([
            'token' => JWTAuth::refresh(JWTAuth::getToken())
        ], 200);
    }
}
