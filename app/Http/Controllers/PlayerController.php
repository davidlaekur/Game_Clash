<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Zone;
use App\Models\Action;
use App\Models\Type;
use App\Models\InventionType;
use App\Models\Inventory;
use App\Models\Item;
use App\Models\UserAdventure;
use App\Models\Scenario;

use App\Services\ZoneService;
use App\Services\UserService;
use App\Services\GameService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PlayerController extends Controller
{

    protected $zoneService;
    protected $gameService;
    protected $userService;

    public function __construct(ZoneService $zoneService, GameService $gameService, UserService $userService)
    {
        $this->zoneService = $zoneService;
        $this->gameService = $gameService;
        $this->userService = $userService;
    }


    // lista de jugadores 
    public function index()
    {
        $users = User::all();
        return view('players.index', compact('users'));
    }


    /**
     *  perfil de un jugador
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        $totalPoints = $this->userService->getTotalPoints($user);
        $totalStats = $this->userService->getTotalStats($user);
        $totalCapacity = $this->userService->getTotalCapacity($user);
        $userInventory = $user->inventory;
        $teamInventory = $user->team ? $user->team->inventory : null;

        $userAdventure = UserAdventure::where('user_id', $user->id)
            ->where('completed', true)
            ->latest()
            ->first();

        // premios de  la aventura
        $rewards = [];
        if ($userAdventure) {
            $rewards = Item::where('itemable_id', $userAdventure->adventure_id)
                ->where('itemable_type', 'App\Models\Adventure')
                ->get();
        }

        // recompensas de la aventura
        $earnedItems = [];
        if ($userAdventure) {
            $scenarioIds = Scenario::where('adventure_id', $userAdventure->adventure_id)
                ->pluck('id');

            $earnedItems = Item::whereIn('itemable_id', $scenarioIds)
                ->where('itemable_type', 'App\Models\Scenario')
                ->get();
        }

        return view('players.show', compact(
            'user',
            'totalPoints',
            'totalStats',
            'totalCapacity',
            'userInventory',
            'teamInventory',
            'rewards',
            'earnedItems'
        ));
    }


    /**
     * formulario para editar el perfil del jugador
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('players.edit', compact('user'));
    }



    /**
     * Actualizar perfil del jugador
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|min:2|max:9',
            'email' => 'required|email|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // validacion para el avatar
            'password' => 'nullable|min:4|confirmed', // Validar contraseña, si se proporciona
            'current_password' => 'nullable|required_with:password',
        ]);

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath; // ruta del avatar
        }

        // chequear contraseña actual
        if ($request->filled('password') && !Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'La contraseña actual es incorrecta.']);
        }


        $user->name = $request->input('name');
        $user->email = $request->input('email');
        $user->bio = $request->input('bio', ''); // Si existe bio en el formulario, se asigna, de lo contrario se asigna un string vacio
        $user->save();

        return redirect()->route('players.show', $user->id)->with('success', 'Perfil actualizado correctamente.');
    }

    /** 
     * Mostrar el inventario del jugador
     */
    public function showInventory($id)
    {
        $user = User::findOrFail($id);
        $userInventory = $user->inventory;
        return view('players.inventory', compact('user', 'userInventory'));
    }


    /**
     * Mover al jugador a una zona.
     */
    public function move(Request $request, Zone $zone)
    {
        $user = Auth::user();

        // Verificar si puede moverse a la zona
        if ($zone->team_id && $zone->team_id !== $user->team_id) {
            return back()->withErrors(['error' => 'No puedes moverte a una zona controlada por otro equipo. Atacala cuando reunas fuerzas y estes en una zona adyacente.']);
        }

        // Obtener la zona actual del jugador
        $currentZone = Zone::find($user->zone_id);

        if (!$currentZone) {
            // Si el jugador no tiene zona asignada, aplica solo el tiempo base
            $distance = 1; // Distancia predeterminada para movimientos iniciales
        } else {
            // Calcular la distancia entre la zona actual y la de destino
            $distance = $currentZone->calculateDistance($zone);
        }

        // Calcular la duración en base a la distancia y tiempo base
        $baseTime = 15; // Tiempo base en segundos
        $duration = ceil($baseTime * $distance); // redindeo hacia arriba


        // Crear la acción de moverse
        $action = Action::create([
            'user_id' => $user->id,
            'type_id' => Type::where('name', 'move')->first()->id,
            'actionable_id' => $zone->id,
            'actionable_type' => Zone::class,
            'duration' => $duration,
            'finish' => false,
        ]);

        // Actualizar la zona actual del jugador usando where + update
        User::where('_id', $user->id)->update(['zone_id' => $zone->id]);

        // Calcular el tiempo restante para la vista inicial
        $timeRemaining = $action->duration;

        return view('zones.move', compact('zone', 'timeRemaining'))
            ->with('success', 'Te estás moviendo hacia la zona: ' . $zone->name);
    }


    /**
     * Explorar una zona.
     */
    public function explore(Request $request, Zone $zone)
    {
        $user = Auth::user();

        // partida terminada: nada de conquistar hasta que el admin reinicie
        if ($this->gameService->checkVictoryCondition()) {
            return redirect()->route('zones.index')->with('error', 'La partida ha terminado. Espera a que el admin inicie una nueva.');
        }

        // Validar que el usuario está en la zona
        if ($user->zone_id !== $zone->id) {
            return back()->with('error', 'Debes moverte a esta zona para explorarla.');
        }

        // Validar que la zona es neutral
        if ($zone->team_id) {
            return back()->with('error', 'Esta zona ya está controlada por otro equipo.');
        }

        // Bloqueo anti-carrera: si otro jugador ya la está explorando, no se puede
        if ($zone->explore_until && \Carbon\Carbon::parse($zone->explore_until)->isFuture()) {
            return back()->with('error', 'Otro jugador ya está explorando esta zona. Prueba en otra.');
        }

        // tiempo base en segundos 
        $baseTime = 15;

        // Determinar el modificador de tiempo según el tipo de paisaje
        $landscapeModifier = match ($zone->landscape) {
            'bosque', 'pradera' => 1,
            'montaña', 'selva' => 2,
            'polo', 'desierto' => 4,
            default => 1, // Valor por defecto para paisajes no definidos
        };

        // calcula duración de la acción
        $duration = $baseTime * $landscapeModifier;

        //  penaliza  si el usuario no es un Explorador
        if ($user->role->name !== 'explorer') {
            $duration *= 1.5; // Penalización del 50%
        }
        $duration *= $this->userService->actionSpeedFactor($user); // el ingenio acelera

        // fijar el bloqueo de exploración mientras dura (anti-carrera entre equipos)
        $zone->explore_until = now()->addSeconds((int) ceil($duration));
        $zone->save();

        // Crear la acción de explorar
        $action = Action::create([
            'user_id' => $user->id,
            'type_id' => Type::where('name', 'explore')->first()->id,
            'actionable_id' => $zone->id,
            'actionable_type' => Zone::class,
            'duration' => ceil($duration), // Usar ceil para redondear hacia arriba
            'finish' => false,
        ]);

        $timeRemaining = $action->duration;

        return view('zones.explore', compact('zone', 'timeRemaining'))
            ->with('success', "Estás explorando la zona: {$zone->name}");
    }



    /**
     * Inventar en la zona.
     */
    public function invent(Request $request, Zone $zone)
    {
        $user = Auth::user();

        if ($user->zone_id !== $zone->id) {
            return back()->with('error', 'Debes estar en esta zona para inventar.');
        }

        if ($zone->team_id !== $user->team_id) {
            return back()->with('error', 'Solo puedes inventar en zonas controladas por tu equipo.');
        }

        // crear el inventario del usuario si no existe
        $userInventory = Inventory::firstOrCreate(
            [
                'inventoriable_id' => $user->id,
                'inventoriable_type' => get_class($user),
            ],
            [
                'type' => 'personal',
                'name' => 'Inventario de ' . $user->name,
            ]
        );


        // obtener los materiales del inventario
        $inventoryMaterials = $userInventory->materials->where('quantity', '>', 0);

        // obtener los tipos de invento disponibles
        $inventionTypes = InventionType::all();

        return view('zones.invent', compact('zone', 'inventoryMaterials', 'inventionTypes', 'user'))
            ->with('success', 'Selecciona el material de tu inventario para crear el invento.');
    }



    /**
     * Recolectar materiales de la zona.
     */
    public function collect(Request $request, Zone $zone)
    {
        $user = Auth::user();

        // Validar que el jugador está en la zona
        if ($user->zone_id !== $zone->id) {
            return back()->with('error', 'Debes estar en esta zona para recolectar materiales.');
        }

        // Validar que la zona es propia o neutral
        if ($zone->team_id !== null && $zone->team_id !== $user->team_id) {
            return back()->with('error', 'No puedes recolectar materiales en una zona enemiga.');
        }


        // Obtener materiales disponibles en la zona
        $availableMaterials = $zone->materials->where('quantity', '>', 0);

        // Capacidad del inventario del usuario
        $inventoryCapacity = $this->userService->getTotalCapacity($user);

        // chequear el inventario
        $userInventory = $user->inventory;

        // chequer el usuario tiene inventario
        $inventory = $user->inventory;
        if (!$inventory) {
            return back()->withErrors(['error' => 'No se encontró un inventario, por favor crea uno antes de recolectar o inventar.']);
        }


        // Si no tiene inventario 
        $totalMaterials = $userInventory ? $userInventory->materials->sum('quantity') : 0;
        $totalInventions = $userInventory ? $userInventory->inventions->count() : 0;



        return view('zones.collect', compact('zone', 'availableMaterials', 'inventoryCapacity'))
            ->with('success', 'Selecciona los materiales que deseas recolectar.');
    }



    /**
     * Atacar una zona.
     */
    public function attack(Zone $zone, ZoneService $zoneService)
    {
        $user = auth()->user();

        // partida terminada: no se puede seguir atacando hasta el reinicio del admin
        if ($this->gameService->checkVictoryCondition()) {
            return redirect()->route('zones.index')->with('error', 'La partida ha terminado. Espera a que el admin inicie una nueva.');
        }

        $originZone = $user->zone;

        // verificar que el jugador está en una zona propia
        if (!$originZone || $originZone->team_id !== $user->team_id) {
            return redirect()->back()->with('error', 'Solo puedes atacar desde una zona propia.');
        }

        // verificar que la zona objetivo es enemiga
        if (!$zone->team_id || $zone->team_id === $user->team_id) {
            return redirect()->back()->with('error', 'Solo puedes atacar zonas enemigas.');
        }

        // verificar que la zona objetivo es adyacente
        if (!$zoneService->zonesAdjacent($originZone, $zone)) {
            return redirect()->back()->with('error', 'Solo puedes atacar zonas adyacentes.');
        }

        // registrar la acción de ataque en la base de datos
        $action = Action::create([
            'user_id' => $user->id,
            'type_id' => Type::where('name', 'attack')->first()->id,
            'actionable_id' => $zone->id,
            'actionable_type' => Zone::class,
            'duration' => 20,
            'finish' => false,
        ]);



        $combatData = $this->gameService->calculateCombatPoints($zone);

        // resolver la batalla


        // atacantes = guarnición del equipo en la zona de origen (modelo posicional)
        $attackers = User::where('zone_id', $originZone->id)
            ->where('team_id', $user->team_id)
            ->players()
            ->with(['stats', 'inventory.inventions.stats'])->get();


        // obtener defensores
        $defenders = $zone->users()->players()->with(['stats', 'inventory.inventions.stats'])->get();


        foreach ($attackers as $attacker) {
            // Obtener puntos y stats del atacante
            $attacker->attackStats = $this->userService->getTotalStats($attacker);
            $attacker->attackPoints = $this->userService->getTotalPoints($attacker);
        }

        foreach ($defenders as $defender) {
            // Obtener puntos y stats del defensor
            $defender->defenseStats = $this->userService->getTotalStats($defender);
            $defender->defensePoints = $this->userService->getTotalPoints($defender);
        }



        // Resolver la batalla con los MISMOS datos que se muestran (una sola vez)
        $battleResult = $this->gameService->resolveAttack($zone, $combatData);

        // enviar los datos a la vista de la batalla
        return view('zones.battle', [
            'zone' => $zone,
            'attackResult' => $battleResult,
            'totalDefense' => $combatData['totalDefense'],
            'attackPoints' => $combatData['attackPoints'],
            'playerDefense' => $combatData['playerDefense'],
            'bonusTimeDefense' => $combatData['bonusTimeDefense'],
            'totalDefensePoints' => $combatData['totalDefensePoints'],
            'totalAttackPoints' => $combatData['totalAttackPoints'],
            'user' => $user,
            'attackers' => $attackers,
            'defenders' => $defenders,
            'timeRemaining' => $action->duration,
        ]);
    }

    /**
     * Ranking: jugadores por mérito y equipos por territorios.
     */
    public function ranking()
    {
        $players = User::players()->with('team')->get()
            ->sortByDesc(fn($u) => $u->glory())
            ->take(15)->values();

        $teams = \App\Models\Team::all()->map(fn($t) => (object) [
            'name'  => $t->name,
            'zones' => Zone::where('team_id', $t->id)->count(),
        ])->sortByDesc('zones')->values();

        $hallOfFame = \App\Models\HallOfFame::orderBy('ended_at', 'desc')->take(5)->get();

        return view('ranking', compact('players', 'teams', 'hallOfFame'));
    }
}
