<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Team;
use App\Models\Inventory;
use App\Models\InventoryMaterial;
use App\Models\Invention;

use App\Services\UserService; 

class TeamController extends Controller
{

    protected $userService;

    // servicio
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $teams = Team::all();
        $user = auth()->user();

        //  inventario personal y del equipo del usuario actual
        $userInventory = Inventory::where('inventoriable_id', auth()->id())
            ->where('inventoriable_type', 'App\Models\User')
            ->first();

        $teamInventory = null;
        if (auth()->user()->team) {
            $teamInventory = Inventory::where('inventoriable_id', auth()->user()->team->id)
                ->where('inventoriable_type', 'App\Models\Team')
                ->first();
        }
/* 
        $teamMembers = $user->team;
        $membersData = $teamMembers->map(function ($member) {
            return [
                'user' => $member,
                'totalPoints' => $this->userService->getTotalPoints($member),
                'totalStats' => $this->userService->getTotalStats($member),
                'totalCapacity' => $this->userService->getTotalCapacity($member),
            ];
        });
 */

        //  miembros del equipo
        $teamMembers = $user->team ? $user->team->users : [];

        // añadir puntos etc con el servicio para mostrar en la vista
        foreach ($teamMembers as $member) {
            $member->totalPoints = $this->userService->getTotalPoints($member);
            $member->totalStats = $this->userService->getTotalStats($member);
            $member->totalCapacity = $this->userService->getTotalCapacity($member);
        }

        return view('teams.index', compact('teams', 'user', 'userInventory', 'teamInventory', 'teamMembers'));
    }



    public function transfer()
    {
        /* 
        dd('Entrando a transfer'); */

        $team = auth()->user()->team;

        // Verificar si el usuario pertenece a un equipo
        if (!$team) {
            return redirect()->route('teams.index')->with('error', 'No perteneces a ningún equipo.');
        }

        // Inventario del equipo
        $teamInventory = Inventory::with('materials.material')
            ->where('inventoriable_id', $team->id)
            ->where('inventoriable_type', 'App\Models\Team')
            ->first();

        // Inventario personal del usuario autenticado
        $userInventory = Inventory::with('materials.material')
            ->where('inventoriable_id', auth()->id())
            ->where('inventoriable_type', 'App\Models\User')
            ->firstOrCreate([
                'type' => 'personal',
                'name' => 'Inventario de ' . auth()->user()->name,
            ]);

        return view('teams.transfer', compact('team', 'teamInventory', 'userInventory'));
    }


    public function processTransfer(Request $request)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'materials' => 'nullable|array', // Para materiales
            'materials.*' => 'nullable|integer|min:1',
            'items' => 'nullable|array', // Para inventos
            'items.*' => 'nullable|integer|min:1',
            'direction' => 'required|in:team_to_personal,personal_to_team',
            'team_id' => 'required|exists:teams,id',
        ]);

        // comprobar  inventarios del equipo y del usuario
        $team = Team::findOrFail($validated['team_id']);

        // buscar los inventarios del equipo y del usuario
        $teamInventory = Inventory::where('inventoriable_id', $team->id)
            ->where('inventoriable_type', 'App\Models\Team')
            ->first();

        $userInventory = Inventory::where('inventoriable_id', auth()->id())
            ->where('inventoriable_type', 'App\Models\User')
            ->first();

        if (!$teamInventory && $validated['direction'] === 'personal_to_team') {
            return redirect()->route('teams.transfer')
                ->with('error', 'El equipo no tiene un inventario. Debes crear un inventario para el equipo.');
        }


        // transferencia de materiales
        if (!empty($validated['materials'])) {
            $materials = array_filter($validated['materials'], function ($quantity) {
                return $quantity !== null && $quantity > 0;
            });

            foreach ($materials as $inventoryMaterialId => $quantity) {
                $fromMaterial = InventoryMaterial::findOrFail($inventoryMaterialId);

                if ($validated['direction'] === 'team_to_personal') {
                    $this->transferMaterial($teamInventory, $userInventory, $fromMaterial, $quantity);
                } elseif ($validated['direction'] === 'personal_to_team') {
                    $this->transferMaterial($userInventory, $teamInventory, $fromMaterial, $quantity);
                }
            }
        }

        // transferencia de inventos
        if (!empty($validated['items'])) {
            $inventionIds = array_keys($validated['items']); // Extraer los IDs de los inventos
            foreach ($inventionIds as $itemId) {
                $fromInvention = Invention::findOrFail($itemId);

                if ($validated['direction'] === 'team_to_personal') {
                    $this->transferInvention($teamInventory, $userInventory, $fromInvention);
                } elseif ($validated['direction'] === 'personal_to_team') {
                    $this->transferInvention($userInventory, $teamInventory, $fromInvention);
                }
            }
        }

        return redirect()->route('teams.transfer')->with('success', 'Transferencia completada correctamente.');
    }



    private function transferMaterial($fromInventory, $toInventory, $fromMaterial, $quantity)
    {
        // Verificar que el material tenga cantidad suficiente en el inventario de origen
        if ($fromMaterial->quantity < $quantity) {
            throw new \Exception("Cantidad insuficiente para el material {$fromMaterial->material_id}.");
        }

        // Reducir la cantidad en el inventario de origen
        $fromMaterial->quantity -= $quantity;
        $fromMaterial->save();

        // Buscar o crear el material en el inventario de destino
        $toMaterial = InventoryMaterial::where('inventory_id', $toInventory->_id)
            ->where('material_id', $fromMaterial->material_id)
            ->first();

        if ($toMaterial) {
            // Si ya existe, sumar la cantidad
            $toMaterial->quantity += $quantity;
        } else {
            // Si no existe, crear un nuevo registro
            $toMaterial = InventoryMaterial::create([
                'inventory_id' => $toInventory->_id,
                'material_id' => $fromMaterial->material_id,
                'quantity' => $quantity,
            ]);
        }

        $toMaterial->save();
    }


    private function transferInvention($fromInventory, $toInventory, $fromInvention)
    {
        // verificar que el invento realmente pertenece al inventario de origen
        if ($fromInvention->inventory_id !== $fromInventory->id) {
            return redirect()->back()->with('error', 'El invento no pertenece al inventario seleccionado.');
        }

        // transferir el invento al inventario de destino
        $fromInvention->inventory_id = $toInventory->id;
        $fromInvention->save();

        return redirect()->route('teams.transfer')->with('success', 'Invento transferido correctamente.');
    }
}
