<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;



class PlayerController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    // Listar jugadores
    public function index()
    {
        
        $players = User::all();
        return response()->json($players, 200);
    }


    // crear jugador 
    public function store(Request $request)
    {
        // solo admin puede crear jugadores
        $admin = auth()->user();
        if (!$admin || $admin->role->name !== 'Admin') {
            return response()->json(['error' => 'Solo un administrador puede crear jugadores.'], 403);
        }
    
        // Validación de datos (similar a register)
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:9',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4|confirmed',
            'role_id' => 'required|exists:roles,_id', 
            'team_id' => 'required|exists:teams,_id'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
    
        // creación del player 
        $player = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role_id' => $request->role_id,
            'team_id' => $request->team_id,
            'points' => 0, 
            'zone_id' => null, 
        ]);
    
        return response()->json([
            'message' => 'Jugador creado correctamente',
            'player' => $player
        ], 201);
    }
    

    // Mostrar perfil del jugador
    public function show($id)
    {

        $player = User::findOrFail($id);
        $totalPoints = $this->userService->getTotalPoints($player);
        $totalStats = $this->userService->getTotalStats($player);

        return response()->json(
            $player,
            $totalPoints,
            $totalStats,
            200
        );
    }

    // update perfil del jugador
    public function update(Request $request, $id)
    {
        $player = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|min:2|max:9',
            'email' => 'required|email|max:255',
            'password' => 'nullable|min:4|confirmed',
            'current_password' => 'nullable|required_with:password',
            'role_id' => 'nullable|exists:roles,_id',
            'team_id' => 'nullable|exists:teams,_id'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // chequear  si la contraseña se quiere actualizar
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $player->password)) {
                return response()->json(['error' => 'La contraseña actual es incorrecta.'], 400);
            }
            $player->password = bcrypt($request->password);
        }

        $player->update($request->except('password'));

        return response()->json(['message' => 'Perfil actualizado correctamente.', 'player' => $player], 200);
    }


    public function destroy($id)
    {
           // solo admin puede eliminar  jugadores
           $admin = auth()->user();

           if (!$admin || $admin->role->name !== 'Admin') {
               return response()->json(['error' => 'Solo un administrador puede eliminar jugadores.'], 403);
           }
        try {

            $player = User::where('_id', $id)->first();

            if (!$player) {
                return response()->json(['error' => 'Jugador no encontrado.'], 404);
            }

            // Intentar eliminación
            $player->delete();

            return response()->json(['message' => 'Jugador eliminado correctamente.'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Excepción al eliminar el jugador: ' . $e->getMessage()], 500);
        }
    }
}
