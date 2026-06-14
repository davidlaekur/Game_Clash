<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Team;
use App\Models\Role;


class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }


    public function login(Request $request)
    {
        // Validar los datos del formulario
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (auth()->attempt($request->only('email', 'password'))) {
            // Redirigir al mapa (vista de zonas) después del login
            return redirect()->route('zones.index')->with('success', 'Sesión iniciada correctamente.');
        }



        // Si falla la autenticación, redirigir al login con un mensaje de error
        return back()->withErrors(['email' => 'Las credenciales no son correctas.'])->withInput();
    }

    public function logout()
    {
        // Cerrar la sesión del usuario
        auth()->logout();

        // Redirigir al usuario al login con un mensaje de éxito
        return redirect()->route('login')->with('success', 'Sesión cerrada correctamente.');
    }

    public function showRegisterForm(Request $request)
    {
        // el registro solo elige facción; el rol se elige al unirse a la partida
        $teams = Team::with('users')->get();
        return view('auth.register', compact('teams'));
    }


    public function register(Request $request)
    {
        // El registro solo crea la cuenta. La FACCIÓN y el ROL se eligen al unirse
        // a la partida (sala de espera), para poder equilibrar bandos cada partida.
        $request->validate([
            'name' => 'required|string|min:2|max:9',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'points' => 0,
            'role_id' => null,  // facción y rol se eligen al unirse
            'team_id' => null,
            'zone_id' => null,
            'joined' => false,
        ]);

        return redirect()->route('login')->with('success', 'Registro completado. Elige tu facción y tu rol al unirte a la partida.');
    }
}
