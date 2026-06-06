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

    //servicios 
    public function __construct(GameService $gameService, ZoneService $zoneService, UserService $userService, ZoneApiService $zoneApiService)
    {
        $this->gameService = $gameService;
        $this->zoneService = $zoneService;
        $this->userService = $userService;
        $this->zoneApiService = $zoneApiService;
    }


    /**
     * Mostrar el mapa de zonas.
     */
    public function index()
    {
        // cargar todas las zonas con sus equipos
        $zones = Zone::with('team')->get();

        return view('zones.index', compact('zones'));
    }

    public function show(Zone $zone, ZoneService $zoneService)
    {
        $zone->refresh();
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

        //  comporbar cualquier ataque en la zona antes de calcular defensa y ataque
        $attackResult = $this->gameService->resolveAttack($zone);

        // recoger datos de defensa y ataque desde GameService
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



        $defenders = $zone->users()->with(['inventory.inventions.stats'])->get(); // una sola consulta

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

        foreach ($newZones as $zone) {
            $selectedMaterials = $materials->random(rand(3, 6));

            foreach ($selectedMaterials as $material) {
                $zone->materials()->create([
                    'name' => $material->name,
                    'type_id' => $material->type_id,
                    'material_id' => $material->id,  // En MongoDB, esto debe ser un ObjectId
                    'quantity' => rand(1, 50),
                    'probability' => rand(1, 70) / 100,
                ]);
            }
        }
    }
}
