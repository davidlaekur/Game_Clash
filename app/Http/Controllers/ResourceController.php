<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material;
use App\Models\Zone;
use App\Models\Inventory;
use App\Models\InventoryMaterial;
use App\Models\Action;
use App\Models\Type;
use App\Models\User;

use Illuminate\Support\Facades\Auth;
use App\Services\UserService;

class ResourceController extends Controller
{


    protected $userService;

    // user service 
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        // Crear inventario personal 
        Inventory::firstOrCreate(
            [
                'inventoriable_id' => $user->id,
                'inventoriable_type' => get_class($user),
            ],
            [
                'type' => 'personal',
                'name' => 'Inventario de ' . $user->name,
            ]
        );

        // Crear inventario del equipo 
        if ($user->team) {
            Inventory::firstOrCreate(
                [
                    'inventoriable_id' => $user->team->id,
                    'inventoriable_type' => 'App\Models\Team',
                ],
                [
                    'type' => 'team',
                    'name' => 'Inventario del Equipo ' . $user->team->name,
                ]
            );
        }


        return redirect()->back()->with('success', 'Inventario creado correctamente.');

    }




    /**
     * Store materials in inventory.
     */

    public function store(Request $request)
    {
        $user = User::with(['inventory.inventions.stats'])->find(Auth::id());


        // Validar los datos del formulario
        $validated = $request->validate([
            'zone_id' => 'required|exists:zones,_id',
            'materials' => 'required|array',
            'materials.*' => 'nullable|integer|min:1',
            'storage_option' => 'required|in:personal,team',
        ]);

        $zone = Zone::findOrFail($validated['zone_id']);
        $materials = array_filter($validated['materials'], function ($quantity) {
            return $quantity !== null && $quantity > 0;
        });


        $inventoryCapacity = $this->userService->getTotalCapacity($user); 
        // sellecionar al menos un material
        if (empty($materials)) {
            $availableMaterials = $zone->materials->where('quantity', '>', 0);
            return view('zones.collect', compact('zone', 'availableMaterials','inventoryCapacity'))
                ->with('error', 'Debes seleccionar al menos un material para recolectar.');
        }

        $storageOption = $validated['storage_option'];

        // Determinar el inventario polimórfico
        $inventory = null;

        if ($storageOption === 'personal') {
            // Inventario personal del usuario
            $inventory = Inventory::firstOrCreate(
                [
                    'inventoriable_id' => $user->id,
                    'inventoriable_type' => get_class($user),
                ],
                [
                    'type' => 'personal',
                    'name' => 'Inventario de ' . $user->name,
                ]
            );

          
             $inventoryCapacity = $this->userService->getTotalCapacity($user); 

            //  sumar la cantidad de materiales que se van a recolectar
            $totalMaterials = array_sum($materials);
   
                if ($totalMaterials > $inventoryCapacity) {
                    $availableMaterials = $zone->materials->where('quantity', '>', 0);
                    return view('zones.collect', compact('zone', 'availableMaterials','inventoryCapacity'))
                        ->with('error', 'No puedes recolectar más materiales que la capacidad máxima de tu inventario.');
                }
                
            


        } elseif ($storageOption === 'team' && $user->team) {
            // Inventario del equipo del usuario
            $inventory = Inventory::firstOrCreate(
                [
                    'inventoriable_id' => $user->team->id,
                    'inventoriable_type' => get_class($user->team),
                ],
                [
                    'type' => 'team',
                    'name' => 'Inventario del Equipo ' . $user->team->name,
                ]
            );
        }

        if (!$inventory) {
            return redirect()->back()->with('error', 'No se encontró un inventario.');
        }

        // Procesar los materiales
        foreach ($materials as $materialId => $quantity) {
            $material = Material::findOrFail($materialId);

            // Validar que el material pertenece a la zona y tiene cantidad disponible
            if ($material->zone_id !== $zone->id || $material->quantity <= 0) {
                continue;
            }

            //  no recolectar más de lo disponible
            $quantity = min($quantity, $material->quantity);

            if ($quantity > 0) {
                // restar  la cantidad del material en la zona
                $material->quantity -= $quantity;
                $material->regenerated_at = now(); // la regeneración cuenta desde aquí
                $material->save();

                // Buscar si ya existe el registro en InventoryMaterial
                $inventoryMaterial = InventoryMaterial::where('inventory_id', $inventory->_id)
                    ->where('material_id', $material->_id)
                    ->first();

                if ($inventoryMaterial) {
                    // Si ya existe, sumar la cantidad
                    $inventoryMaterial->quantity += $quantity;
                    $inventoryMaterial->save();
                } else {
                    // Si no existe, crear un nuevo registro
                    InventoryMaterial::create([
                        'inventory_id' => $inventory->_id,
                        'material_id' => $material->_id,
                        'quantity' => $quantity,

                    ]);
                }
            }
        }

            // Capacidad del inventario del usuario
            $inventoryCapacity = $this->userService->getTotalCapacity($user); 

        // duración de la acción basada en el rol
        $baseDuration = 30; // Base de 30 segundos para recolectores
        $duration = ($user->role->name === 'collect') ? $baseDuration : $baseDuration * 1.5;
        $duration *= $this->userService->actionSpeedFactor($user); // el ingenio acelera

        // Crear la acción de recolectar con temporizador
        Action::create([
            'user_id' => $user->id,
            'type_id' => Type::where('name', 'collect')->first()->id,
            'actionable_id' => $zone->_id,
            'actionable_type' => Zone::class,
            'duration' => $duration,
            'finish' => false,
        ]);

        $availableMaterials = $zone->materials->where('quantity', '>', 0);
        $successMessage = 'Recolección iniciada. Por favor, espera a que termine.';

        // volvemos con mensaje de espera y redirigimos a la misma página
        return view('zones.collect', compact('zone', 'availableMaterials', 'duration','inventoryCapacity','successMessage'))->with('timeRemaining', $duration);
    }





    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        // Cargar inventario con materiales relacionados y sus detalles
        $inventory = Inventory::with('materials.material')->findOrFail($id);

        return view('teams.show', [
            'inventory' => $inventory,
            'materials' => $inventory->materials,
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
