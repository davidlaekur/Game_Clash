<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Zone;
use App\Models\Action;
use Illuminate\Support\Facades\Auth;


class ActionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
    
        if (!$user->team) {
            return redirect()->back()->with('error', 'No perteneces a ningún equipo.');
        }
    
        // Obtener acciones del equipo
        $teamActions = Action::whereIn('user_id', $user->team->users->pluck('id')) // Usuarios del equipo
            ->where('finish', true) // Solo acciones finalizadas
            ->with(['user', 'type', 'actionable']) 
            ->orderBy('created_at', 'desc')
            ->paginate(15);
    
        return view('actions.index', compact('teamActions'));
    }
    
    


}
