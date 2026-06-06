<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\UserAdventure;

class CheckActiveAdventure
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */


    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login'); 
        }
    

        $adventure = UserAdventure::where('user_id', $user->id)
            ->where('completed', false)
            ->first();

        if (!$adventure) {
            return redirect()->route('players.show')->with('error', 'Aventura no encontrada.'); 
        }


        return $next($request);
    }
}
