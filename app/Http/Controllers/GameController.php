<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Team;
use App\Models\Zone;
use App\Models\Action;
use App\Services\GameService;

class GameController extends Controller
{
    protected $gameService;

    /**
     * constructor del servicio **
     */
    public function __construct(GameService $gameService)
    {
        $this->gameService = $gameService;
    }

    /**
     * Calcular  puntos globales del jugador **
     */
    public function calculateGlobalPoints(User $user)
    {
        return $this->gameService->calculateGlobalPoints($user);
    }

    /**
     * Calcular puntos de ataque y defensa en una zona**
     */
    public function calculateCombatPoints(Zone $zone)
    {
        return $this->gameService->calculateCombatPoints($zone);
    }

    /**
     * Comprobar si un equipo ha ganado la partida**
     */
    public function checkVictoryCondition()
    {
        return $this->gameService->checkVictoryCondition();
    }

    /**
     *  Estado actual del juego **
     */
    public function gameStatus()
    {
        $users = User::with('inventory.inventions', 'team')->get();
        $teams = Team::with('zones')->get();
        $zones = Zone::with('team')->get();
        $victoryMessage = $this->checkVictoryCondition();

        return view('game.status', compact('users', 'teams', 'zones', 'victoryMessage'));
    }

    /**
     *  Reiniciar el juego**
     */
    public function resetGame()
    {
        $this->gameService->resetGame();

        return redirect()->route('game.status')->with('success', 'El juego ha sido reiniciado.');
    }

    /**
     * Iniciar una NUEVA partida (solo admin): archiva la terminada en el Salón de
     * la Fama (ganador + podio) y reinicia todo. Solo si la partida ya tiene ganador.
     */
    public function newGame()
    {
        $user = auth()->user();
        if (!$user || optional($user->role)->name !== 'Admin') {
            return redirect()->route('zones.index')->with('error', 'Solo el admin puede iniciar una nueva partida.');
        }

        $victory = $this->gameService->checkVictoryCondition();
        if (!$victory) {
            return redirect()->route('zones.index')->with('error', 'La partida sigue en curso; aún no hay ganador.');
        }

        $this->gameService->archiveAndReset($victory);

        return redirect()->route('zones.index')->with('success', 'Nueva partida iniciada. ¡Que empiece la conquista!');
    }
}
