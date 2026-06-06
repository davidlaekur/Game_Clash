<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Zone;
use App\Models\Action;
use Illuminate\Support\Facades\Auth;

class ActionController extends Controller
{
    /**
     * lista de actions
     */
    public function index()
    {
        $user = auth()->user();
    
        if ($user->role && $user->role->name === 'Admin') {
            // El admin puede ver todas las acciones
            $allActions = Action::where('finish', true)
                ->with(['user', 'type', 'actionable'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);
    
            return response()->json(['actions' => $allActions], 200);
        }
    
        if (!$user->team) {
            return response()->json(['error' => 'No perteneces a ningún equipo.'], 403);
        }
    
        // Los jugadores solo ven las acciones de su equipo
        $teamActions = Action::whereIn('user_id', $user->team->users->pluck('id'))
            ->where('finish', true)
            ->with(['user', 'type', 'actionable']) 
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    
        return response()->json(['actions' => $teamActions], 200);
    }
    


    /**
     * Mostrar una accion en concreto
     */
    public function show($id)
    {
        $action = Action::with(['user', 'type', 'actionable'])->find($id);

        if (!$action) {
            return response()->json(['error' => 'Acción no encontrada.'], 404);
        }

        return response()->json(['action' => $action], 200);
    }



    /**
     * Crear una nueva acción.
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        if (!$user->team) {
            return response()->json(['error' => 'Debes pertenecer a un equipo para realizar acciones.'], 403);
        }

        $validatedData = $request->validate([
            'type_id' => 'required|exists:action_types,id',
            'zone_id' => 'required|exists:zones,id',
            'finish' => 'boolean',
        ]);

        $action = Action::create([
            'user_id' => $user->id,
            'type_id' => $validatedData['type_id'],
            'zone_id' => $validatedData['zone_id'],
            'finish' => $validatedData['finish'] ?? false,
        ]);

        return response()->json(['message' => 'Acción creada correctamente.', 'action' => $action], 201);
    }



    /**
     * Eliminar una accion en concreto
     */
    public function destroy($id)
    {
        $action = Action::find($id);

        if (!$action) {
            return response()->json(['error' => 'Acción no encontrada.'], 404);
        }

        $action->delete();

        return response()->json(['message' => 'Acción eliminada correctamente.'], 200);
    }
}

