<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Action;
use App\Services\GameService;
use App\Services\ZoneService;
use App\Services\UserService;
use App\Services\ZoneApiService;
use App\Models\Material;
use App\Models\MaterialType;
use App\Models\Type;
use App\Models\User;

class ZoneController extends Controller
{

    protected $gameService;
    protected $zoneService;
    protected $userService;
    protected $zoneApiService;
    protected $regenService;
    protected $worldEventService;

    //servicios
    public function __construct(GameService $gameService, ZoneService $zoneService, UserService $userService, ZoneApiService $zoneApiService, \App\Services\RegenService $regenService, \App\Services\WorldEventService $worldEventService)
    {
        $this->gameService = $gameService;
        $this->zoneService = $zoneService;
        $this->userService = $userService;
        $this->zoneApiService = $zoneApiService;
        $this->regenService = $regenService;
        $this->worldEventService = $worldEventService;
    }


    /**
     * Mostrar el mapa de zonas.
     */
    public function index()
    {
        // el mundo cobra vida: caduca eventos viejos y puede generar uno nuevo
        $this->worldEventService->tick();

        // cargar todas las zonas con sus equipos
        $zones = Zone::with('team')->get();

        // feed de actividad: qué hacen tus compañeros y qué eventos ocurren
        $feed = $this->buildTeamFeed(auth()->user(), $zones);

        return view('zones.index', compact('zones', 'feed'));
    }

    /** Actividad reciente del equipo y del mundo para la barra del mapa. */
    private function buildTeamFeed($me, $zones): array
    {
        $feed = [];
        $zById = $zones->keyBy(fn($z) => (string) $z->id);

        if ($me->team_id) {
            $mateIds = User::where('team_id', $me->team_id)->players()
                ->where('_id', '!=', $me->id)->pluck('id')->map(fn($i) => (string) $i)->all();
            if (!empty($mateIds)) {
                $actions = Action::where('finish', false)->whereIn('user_id', $mateIds)
                    ->with('type')->latest('created_at')->get();
                $mates = User::whereIn('_id', $actions->pluck('user_id')->unique()->all())
                    ->get()->keyBy(fn($u) => (string) $u->id);
                foreach ($actions as $a) {
                    $u = $mates[(string) $a->user_id] ?? null;
                    if (!$u || !$a->type) {
                        continue;
                    }
                    $zoneName = $a->actionable_type === Zone::class
                        ? optional($zById[(string) $a->actionable_id] ?? null)->name
                        : optional($zById[(string) $u->zone_id] ?? null)->name;
                    [$verb, $icon] = match ($a->type->name) {
                        'explore' => ['explora', 'fa-compass'],
                        'collect' => ['recolecta en', 'fa-hand-holding'],
                        'invent'  => ['forja en', 'fa-lightbulb'],
                        'attack'  => ['ataca', 'fa-khanda'],
                        'move'    => ['marcha a', 'fa-walking'],
                        default   => ['actúa en', 'fa-bolt'],
                    };
                    $feed[] = ['icon' => $icon, 'kind' => 'team', 'text' => trim("{$u->name} {$verb} " . ($zoneName ?? ''))];
                }
            }
        }

        foreach ($zones->whereNotNull('event_type') as $z) {
            if ($e = $z->activeEvent()) {
                $feed[] = ['icon' => $e['icon'], 'kind' => $z->event_type, 'text' => "{$e['label']} en {$z->name}"];
            }
        }

        return $feed;
    }

    public function show(Zone $zone, ZoneService $zoneService)
    {
        $zone->refresh();
        // activar la mina si su construcción (2º plano) ha terminado
        if ($zone->mine_ready_at && \Carbon\Carbon::parse($zone->mine_ready_at)->isPast()) {
            $zone->regen_boost = 3;
            $zone->mine_ready_at = null;
            $zone->save();
            session()->flash('success', "¡Mina terminada en {$zone->name}! Los recursos se regeneran x3.");
        }
        $mineRemaining = ($zone->mine_ready_at && \Carbon\Carbon::parse($zone->mine_ready_at)->isFuture())
            ? (int) now()->diffInSeconds($zone->mine_ready_at)
            : 0;
        $this->regenService->regenerateZone($zone); // los recursos se reponen con el tiempo
        $zone->load(['team', 'materials', 'users.inventory.inventions.stats', 'users.stats']);

        $user = Auth::user();
        $playerZone = $user->zone;

        if ($user->zone_id === $zone->id && $zone->team_id === $user->team_id) {
            if (!session()->has('zone_entry_time')) {
                session(['zone_entry_time' => now()]);
            }
        } else {
            // si sale de la zona, reiniciamos el tiempo
            session()->forget('zone_entry_time');
        }

        // los ataques se resuelven al atacar (PlayerController::attack), no aquí:
        // así no se resuelve el mismo combate cada vez que se abre la zona.
        $attackResult = null;

        // elegibilidad para construir mina (feedback antes de intentarlo)
        $mineMissing = [];
        if ($user->rankLevel() < 1) {
            $mineMissing[] = 'rango Soldado';
        }
        $metalHave = $this->ownedQty($user, 'Metal');
        $woodHave = $this->ownedQty($user, 'Madera');
        if ($metalHave < 10) {
            $mineMissing[] = (10 - $metalHave) . ' metal';
        }
        if ($woodHave < 15) {
            $mineMissing[] = (15 - $woodHave) . ' madera';
        }
        $mineCanBuild = empty($mineMissing);

        // datos de defensa/ataque para mostrar (estimación; varía con las condiciones)
        $combatData = $this->gameService->calculateCombatPoints($zone);


        //  comprobar adyacencia
        if ($playerZone) {
            // Si el jugador tiene una zona asignada, comprobamos si las zonas son adyacentes solo sies enemiga
            if ($zone->team_id !== null && $zone->team_id !== $user->team_id) {
                // La zona es rival chequear  la adyacencia
                $zoneAdjacent = $zoneService->zonesAdjacent($playerZone, $zone);
            } else {
                // Sino es zona rival, no chequeamos adyacencia
                $zoneAdjacent = true;
            }
        } else {

            $zoneAdjacent = true;
        }

        $pendingAction = Action::where('user_id', $user->id)
            ->where('finish', false)
            ->latest('created_at')
            ->first();

        $timeRemaining = $pendingAction
            ? max(0, $pendingAction->duration - now()->diffInSeconds($pendingAction->created_at))
            : 0;



        $defenders = $zone->users()->players()->with(['inventory.inventions.stats'])->get(); // solo jugadores (sin admin)

        // Defensores en la zona
        $totalDefenderStats = 0;
        $totalDefenderPoints = 0;

        $totalDefender = 0;

        foreach ($defenders as $defender) {
            // puntos y stats del defensor
            $defenderStats = $this->userService->getTotalStats($defender);
            $defenderPoints = $this->userService->getTotalPoints($defender);


            $totalDefenderStats += $defenderStats['defensa'] ?? 0;
            $totalDefenderStats += $defenderStats['salud'] ?? 0;

            $totalDefenderPoints += $defenderPoints;

            $totalDefender = $totalDefenderStats + $totalDefenderPoints;
        }

        return view('zones.show', [
            'zone' => $zone,
            'attackResult' => $attackResult,
            'totalDefense' => $combatData['totalDefense'],
            'playerDefense' => $combatData['playerDefense'],
            'bonusTimeDefense' => $combatData['bonusTimeDefense'],
            'attackPoints' => $combatData['attackPoints'],
            'user' => $user,
            'zoneAdjacent' => $zoneAdjacent,
            'timeRemaining' => $timeRemaining,
            'totalDefender' => $totalDefender,
            'defenders' => $defenders,
            'mineRemaining' => $mineRemaining,
            'mineCanBuild' => $mineCanBuild,
            'mineMissing' => $mineMissing,
        ]);
    }


    /**
     * Explorar una zona.
     */
    public function explore(Request $request, Zone $zone)
    {
        $user = auth()->user();

        if ($zone->team_id) {
            return back()->with('error', 'Esta zona ya está controlada por un equipo.');
        }

        // Marcar la zona como controlada por el equipo del usuario
        $zone->update(['team_id' => $user->team_id]);

        return back()->with('success', 'Has explorado la zona. Ahora es controlada por tu equipo.');
    }

    /**
     * Importar zonas desde la API externa (GitHub Gist)
     */
    public function importZones()
    {

        $zones = $this->zoneApiService->fetchZones();


        if (isset($zones['error'])) {
            return back()->withErrors($zones['error']);
        }

        $existZones = Zone::pluck('name')->toArray(); // chequeo las zonas si ya existen

        $zonesCount = 0;

        foreach ($zones as $zoneData) {

            if (!in_array($zoneData['name'], $existZones)) {
                Zone::create([
                    'name' => $zoneData['name'],
                    'landscape' => $zoneData['landscape'],
                    'image' => $zoneData['image'],
                    'image_detail' => $zoneData['image_detail'],
                    'latitude' => $zoneData['latitude'],
                    'longitude' => $zoneData['longitude'],
                    'defense' => $this->calculateDefense($zoneData['landscape']),
                    'team_id' => null,
                ]);

                $zonesCount++;
            }
        }

        if ($zonesCount > 0) {
            $this->materialsImportZones();
            return back()->with('success', "{$zonesCount} nuevas zonas importadas correctamente.");
        }
        return back()->with('warning', 'No se importaron nuevas zonas porque ya existen en la base de datos.');
    }


    /**
     * para las zonas importadas desde la API, calcular la defensa
     */
    private function calculateDefense(string $landscape): int
    {
        return match ($landscape) {
            'bosque', 'selva' => rand(40, 70),
            'pradera', 'desierto' => rand(5, 40),
            'montaña', 'polo' => rand(70, 120),
            'volcán' => rand(80, 150),
            'cueva' => rand(60, 100),
            'pantano' => rand(30, 70),
            'playa' => rand(10, 50),
            'isla' => rand(20, 60),
            'glaciar' => rand(90, 140),
            'ciénaga' => rand(40, 90),
            'meseta' => rand(20, 50),
            'jungla' => rand(50, 90),
            default => rand(10, 50),
        };
    }

    /**
     *  materiales a las nuevas zonas importadas
     */

    public function materialsImportZones()
    {


        $newZones = Zone::doesntHave('materials')->get();

        if ($newZones->isEmpty()) {
            return;
        }

        $materials = Material::all();
        $landscapes = config('material_landscapes');

        foreach ($newZones as $zone) {
            // solo materiales cuya familia pertenece al paisaje de la zona; uno por nombre
            $pool = $materials->filter(function ($m) use ($zone, $landscapes) {
                $cat = optional($m->materialType)->category;
                return $cat && in_array($zone->landscape, $landscapes[$cat] ?? [], true);
            })->unique('name');
            if ($pool->isEmpty()) {
                $pool = $materials->unique('name');
            }
            $selectedMaterials = $pool->random(min(rand(3, 6), $pool->count()));

            foreach ($selectedMaterials as $material) {
                $qty = rand(10, 50);
                $zone->materials()->create([
                    'name' => $material->name,
                    'materialtype_id' => $material->materialtype_id, // conserva la familia (Roca, Metal…)
                    'density' => $material->density,                 // conserva la densidad (potencia del invento)
                    'efficiency' => $material->efficiency,
                    'quantity' => $qty,
                    'max_quantity' => $qty,
                    'regenerated_at' => now(),
                    'probability' => $material->probability,          // probabilidad real (no aleatoria rota)
                ]);
            }
        }
    }

    /**
     * Construir una mina en una zona del equipo: triplica la regeneración de
     * recursos. Cuesta 5 de metal del inventario del jugador.
     */
    public function buildMine(Zone $zone)
    {
        $user = auth()->user();

        if ($user->zone_id !== $zone->id || $zone->team_id !== $user->team_id) {
            return redirect()->back()->with('error', 'Solo puedes construir una mina en una zona de tu equipo donde estés.');
        }
        if (($zone->regen_boost ?? 1) > 1) {
            return redirect()->back()->with('error', 'Esta zona ya tiene una mina.');
        }
        if ($zone->mine_ready_at && \Carbon\Carbon::parse($zone->mine_ready_at)->isFuture()) {
            return redirect()->back()->with('error', 'Ya hay una mina en construcción en esta zona.');
        }
        if ($user->rankLevel() < 1) {
            return redirect()->back()->with('error', 'Necesitas ser al menos Soldado (30 méritos) para construir minas. Gánalos en batallas y conquistas.');
        }

        // coste de la mina: metal (herramientas) + madera (estructura)
        $cost = ['Metal' => 10, 'Madera' => 15];

        foreach ($cost as $cat => $amount) {
            if ($this->ownedQty($user, $cat) < $amount) {
                $list = collect($cost)->map(fn($a, $c) => "$a de $c")->implode(' + ');
                return redirect()->back()->with('error', "Construir una mina cuesta $list. Te falta $cat.");
            }
        }
        foreach ($cost as $cat => $amount) {
            $this->consumeByCategory($user, $cat, $amount);
        }

        // la mina se construye en 2º plano: no bloquea, se activa al cumplir el tiempo
        $minutes = 10;
        $zone->mine_ready_at = now()->addMinutes($minutes);
        $zone->save();

        return redirect()->back()->with('success', "Mina iniciada en {$zone->name}: lista en {$minutes} min. Puedes seguir jugando.");
    }

    /** Total de un material (por familia) en el inventario del jugador. */
    private function ownedQty($user, string $category): int
    {
        if (!$user->inventory) {
            return 0;
        }
        $typeIds = MaterialType::where('category', $category)->pluck('id')->map(fn($i) => (string) $i)->all();
        return $user->inventory->materials
            ->filter(fn($im) => in_array((string) optional($im->material)->materialtype_id, $typeIds, true))
            ->sum('quantity');
    }

    /** Consume cierta cantidad de una familia de material del inventario. */
    private function consumeByCategory($user, string $category, int $amount): void
    {
        $typeIds = MaterialType::where('category', $category)->pluck('id')->map(fn($i) => (string) $i)->all();
        $lines = $user->inventory->materials
            ->filter(fn($im) => in_array((string) optional($im->material)->materialtype_id, $typeIds, true) && $im->quantity > 0);
        $need = $amount;
        foreach ($lines as $im) {
            $take = min($need, $im->quantity);
            $im->quantity -= $take;
            $im->save();
            $need -= $take;
            if ($need <= 0) {
                break;
            }
        }
    }
}
