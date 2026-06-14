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

    /** Solo admin: corta aquí si no lo es. */
    private function ensureAdmin()
    {
        $user = auth()->user();
        if (!$user || optional($user->role)->name !== 'Admin') {
            return redirect()->route('zones.index')->with('error', 'Solo el admin puede gestionar la partida.');
        }
        return null;
    }

    /**
     * Empezar YA la partida (override del admin): pasa de inscripción a en curso
     * aunque no se haya llegado al mínimo de jugadores. Normalmente arranca sola.
     */
    public function startGame()
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }
        $state = \App\Models\GameState::current();
        if (!$state->isLobby()) {
            return redirect()->route('zones.index')->with('error', 'La partida no está en fase de inscripción.');
        }
        $state->setPhase('active');
        return redirect()->route('zones.index')->with('success', '¡Partida en marcha! Que empiece la conquista.');
    }

    /**
     * Terminar la partida a mano (admin): para partidas muertas, jugadores
     * inactivos, etc. Pasa a fase terminada y muestra el podio con lo que haya.
     */
    public function forceEnd()
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }
        $state = \App\Models\GameState::current();
        if (!$state->isActive()) {
            return redirect()->route('zones.index')->with('error', 'No hay ninguna partida en curso que terminar.');
        }
        // si ya hay un ganador real, respétalo; si no, fin administrativo
        $victory = $this->gameService->checkVictoryCondition()
            ?: 'La partida ha sido finalizada por el administrador.';
        $state->endWith($victory);
        return redirect()->route('zones.index')->with('success', 'Partida finalizada. Revisa el podio.');
    }

    /**
     * Abrir una NUEVA partida (admin) una vez terminada: archiva el podio en el
     * Salón de la Fama y reinicia todo, dejando la sala de espera abierta.
     */
    public function newGame()
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }
        $state = \App\Models\GameState::current();
        if (!$state->isEnded()) {
            return redirect()->route('zones.index')->with('error', 'Solo puedes abrir una nueva partida cuando la actual ha terminado.');
        }

        $message = $state->result_message ?: ($this->gameService->checkVictoryCondition() ?: 'Partida finalizada.');
        $this->gameService->archiveAndReset($message);

        return redirect()->route('zones.index')->with('success', 'Nueva partida abierta: ¡que se apunten los jugadores!');
    }
}
