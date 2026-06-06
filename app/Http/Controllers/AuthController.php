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
        $teams = Team::with('users')->get();
        $roles = Role::all(); // Obtener todos los roles desde la tabla `rol`

        if ($request->has('team_id')) {
            $team = Team::find($request->team_id);

            // buscamos en la bbdd los roles ocupados en el equipo seleccionado
            $rolesInTeam = $team->users->pluck('role_id')->toArray();

            // Filtrarlos roles ya ocupados 
            $roles = $roles->whereNotIn('id', $rolesInTeam);
        }

        return view('auth.register', compact('teams', 'roles'));
    }


    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:2|max:9',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4|confirmed',
            'team_id' => 'required|exists:teams,id',
            'role_id' => 'required|exists:roles,id', // Validar con la tabla roles
        ]);

        $team = Team::findOrFail($request->team_id); // Obtener el equipo seleccionado
        $role = Role::findOrFail($request->role_id);

        // restringir admin
        if ($role->name === 'Admin') {
            return redirect()->back()->withInput()->withErrors(['role_id' => 'No puedes seleccionar este rol. Esta reservado para el administrador']);
        }
    
        if ($team->users->where('role_id', $request->role_id)->count() > 0) {
            return redirect()->back()->withInput()->withErrors(['role_id' => 'Este rol ya está ocupado en este equipo.']);
        }

        $role = Role::findOrFail($request->role_id);
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'points' => 0,
            'role_id' => $role->id,
            'team_id' => $request->team_id,
            'zone_id' => $team->zones->first()->id ?? null,
        ]);

        // Redirigir al login después del registro
        return redirect()->route('login')->with('success', 'Registro completado. Por favor, inicia sesión.');
    }
}
