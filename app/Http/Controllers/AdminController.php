<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Team;
use App\Models\Zone;
use App\Models\Role;
use App\Models\Action;
use App\Models\GameState;
use App\Services\GameService;
use App\Services\WorldEventService;

/**
 * Panel de mando del admin (árbitro): control de partida, eventos del mundo,
 * intervención en jugadores/zonas y vigilancia. Todo restringido al rol Admin.
 */
class AdminController extends Controller
{
    protected $gameService;
    protected $worldEventService;

    public function __construct(GameService $gameService, WorldEventService $worldEventService)
    {
        $this->gameService = $gameService;
        $this->worldEventService = $worldEventService;
    }

    private function denyIfNotAdmin()
    {
        $u = auth()->user();
        if (!$u || optional($u->role)->name !== 'Admin') {
            return redirect()->route('zones.index')->with('error', 'Solo el admin puede acceder al panel.');
        }
        return null;
    }

    public function index()
    {
        if ($r = $this->denyIfNotAdmin()) return $r;

        $state = GameState::current();
        $teams = Team::all();
        $zones = Zone::with('team')->get()->sortBy('name')->values();
        $roles = Role::where('name', '!=', 'Admin')->get();
        $eventTypes = array_keys(config('world_events'));

        $players = User::players()->with(['team', 'role', 'zone'])->get()->map(function ($u) {
            $last = Action::where('user_id', $u->id)->latest('created_at')->first();
            return (object) [
                'id'      => $u->id,
                'name'    => $u->name,
                'team'    => optional($u->team)->name,
                'role'    => optional($u->role)->name,
                'zone'    => optional($u->zone)->name,
                'joined'  => (bool) ($u->joined ?? false),
                'wounded' => $u->isWounded(),
                'glory'   => $u->glory(),
                'merit'   => (int) ($u->merit ?? 0),
                'lastAt'  => $last ? $last->created_at : null,
            ];
        })->sortByDesc('joined')->values();

        $joinedCount = $players->where('joined', true)->count();

        return view('admin.panel', compact('state', 'teams', 'zones', 'roles', 'eventTypes', 'players', 'joinedCount'));
    }

    public function settings(Request $request)
    {
        if ($r = $this->denyIfNotAdmin()) return $r;
        $request->validate([
            'min_per_team' => 'required|integer|min:1|max:10',
            'event_level'  => 'required|in:off,low,normal,high',
        ]);
        $state = GameState::current();
        $state->min_per_team = (int) $request->min_per_team;
        $state->event_level = $request->event_level;
        $state->save();
        return back()->with('success', 'Ajustes de partida guardados.');
    }

    public function forceEvent(Request $request)
    {
        if ($r = $this->denyIfNotAdmin()) return $r;
        $request->validate(['zone_id' => 'required|exists:zones,_id', 'type' => 'required|string']);
        $zone = Zone::find($request->zone_id);
        $ok = $zone && $this->worldEventService->force($zone, $request->type);
        return back()->with($ok ? 'success' : 'error',
            $ok ? "Evento «{$request->type}» desatado en {$zone->name}." : 'No se pudo lanzar el evento.');
    }

    public function reassignZone(Request $request)
    {
        if ($r = $this->denyIfNotAdmin()) return $r;
        $request->validate(['zone_id' => 'required|exists:zones,_id', 'team_id' => 'nullable|exists:teams,id']);
        $zone = Zone::find($request->zone_id);
        if (!$zone) return back()->with('error', 'Zona no encontrada.');
        $zone->team_id = $request->team_id ?: null; // vacío = neutral
        $zone->save();
        return back()->with('success', "Zona {$zone->name} reasignada.");
    }

    public function adjustMerit(Request $request)
    {
        if ($r = $this->denyIfNotAdmin()) return $r;
        $request->validate(['user_id' => 'required|exists:users,_id', 'amount' => 'required|integer']);
        $u = User::find($request->user_id);
        if (!$u) return back()->with('error', 'Jugador no encontrado.');
        $amount = (int) $request->amount;
        if ($amount >= 0) {
            $u->addMerit($amount);
        } else {
            $u->spendMerit(abs($amount));
        }
        return back()->with('success', "Méritos ajustados a {$u->name}.");
    }

    public function healPlayer(Request $request)
    {
        if ($r = $this->denyIfNotAdmin()) return $r;
        $request->validate(['user_id' => 'required|exists:users,_id']);
        $u = User::find($request->user_id);
        if ($u) {
            $u->wounded_until = null;
            $u->save();
        }
        return back()->with('success', 'Jugador curado.');
    }

    public function expelPlayer(Request $request)
    {
        if ($r = $this->denyIfNotAdmin()) return $r;
        $request->validate(['user_id' => 'required|exists:users,_id']);
        $u = User::find($request->user_id);
        if ($u) {
            $u->joined = false;
            $u->zone_id = null;
            $u->role_id = null;
            $u->team_id = null;
            $u->save();
        }
        return back()->with('success', 'Jugador expulsado de la partida.');
    }
}
