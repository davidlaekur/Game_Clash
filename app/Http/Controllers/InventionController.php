<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Zone;
use App\Models\Action;
use App\Models\Type;
use App\Models\Material;
use App\Models\Invention;
use App\Models\InventionType;
use App\Services\InventionPointsService;
use Illuminate\Support\Facades\Auth;

class InventionController extends Controller
{
    
    public function create(Zone $zone) {}



    public function store(Request $request)
    {
        $user = Auth::user();
    
        // validar datos del formulario
        $validated = $request->validate([
            'zone_id' => 'required|exists:zones,_id',
            'material_id' => 'nullable|exists:materials,id',
            'invention_type' => 'required|exists:invention_types,id',
            'required_inventions' => 'nullable|array',
            'required_inventions.*' => 'exists:inventions,id',
        ]);
    
        $zone = Zone::findOrFail($validated['zone_id']);
        $materialId = $validated['material_id'] ?? null;
        $selectedInventions = $validated['required_inventions'] ?? [];
        $inventoryMaterials = $user->inventory->materials->where('quantity', '>', 0);
        $inventionTypes = InventionType::all();
    
        $inventionType = InventionType::with('needs.parent')->findOrFail($validated['invention_type']);
    
        // verificar si el invento requiere materiales
        if ($inventionType->name !== 'Trampa') {
            if (!$materialId) {
                return view('zones.invent', compact('zone', 'inventoryMaterials', 'inventionTypes', 'user'))
                    ->with('error', 'Debes seleccionar un material para este invento.');
            }
    
            $inventoryMaterial = $user->inventory->materials->firstWhere('material_id', $materialId);
            if (!$inventoryMaterial || $inventoryMaterial->quantity < 1) {
                return view('zones.invent', compact('zone', 'inventoryMaterials', 'inventionTypes', 'user'))
                    ->with('error', 'No tienes suficientes materiales para crear este invento.');
            }
            // restar material del inventario
        $inventoryMaterial->quantity -= 1;
        $inventoryMaterial->save();
        }
    
        // verificar que si el invento requiere otros inventos, el usuario haya seleccionado todos
        if (!$inventionType->needs->isEmpty()) {
            $requiredInventions = $inventionType->needs->pluck('parent_id')->toArray();
            $selectedInventionTypes = $user->inventory->inventions
                ->whereIn('_id', $selectedInventions)
                ->pluck('inventiontype_id')
                ->toArray();
    
            $missingInventions = array_diff($requiredInventions, $selectedInventionTypes);
    
            if (!empty($missingInventions)) {
                return view('zones.invent', compact('zone', 'inventoryMaterials', 'inventionTypes', 'user'))
                    ->with('error', 'Debes seleccionar todos los inventos previos requeridos para crear este invento.');
            }
        }
    
        // eliminar los inventos seleccionados del inventario
        foreach ($selectedInventions as $inventionId) {
            $inventionToRemove = $user->inventory->inventions->find($inventionId);
            if ($inventionToRemove) {
                $inventionToRemove->delete();
            }
        }
    
        // crear la acción, pero sin calcular aún los puntos
        $baseDuration = $inventionType->level * 15;
        $duration = ($user->role->name === 'invent') ? $baseDuration : $baseDuration * 1.5;
    
        Action::create([
            'user_id' => $user->id,
            'type_id' => Type::where('name', 'invent')->first()->id,
            'actionable_id' => $inventionType->id,
            'actionable_type' => InventionType::class,
            'duration' => $duration,
            'finish' => false,
        ]);
    
        session()->put('actionBlocked', true);

        $pointsAndEfficiency = (new InventionPointsService())->calculatePointsAndEfficiency($inventionType, $materialId);
        session()->put('inventionPoints', $pointsAndEfficiency);

        return view('zones.invent', compact('zone', 'inventoryMaterials', 'duration', 'inventionTypes', 'user'))
            ->with('success', 'Invento en proceso, por favor espera.')
            ->with('timeRemaining', $duration);
    }
    
  
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
