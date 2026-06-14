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

        // solo se forja con la partida EN CURSO (ni en inscripción ni terminada)
        if (!\App\Models\GameState::current()->isActive()) {
            return redirect()->route('zones.index')->with('error', 'La partida no está en curso (en inscripción o terminada).');
        }

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
    
        // verificar si el invento requiere material principal (sin consumir todavía)
        $inventoryMaterial = null;
        if ($inventionType->requiresMaterial()) {
            if (!$materialId) {
                return view('zones.invent', compact('zone', 'inventoryMaterials', 'inventionTypes', 'user'))
                    ->with('error', 'Debes seleccionar un material para este invento.');
            }

            $inventoryMaterial = $user->inventory->materials->firstWhere('material_id', $materialId);
            if (!$inventoryMaterial || $inventoryMaterial->quantity < 1) {
                return view('zones.invent', compact('zone', 'inventoryMaterials', 'inventionTypes', 'user'))
                    ->with('error', 'No tienes suficientes materiales para crear este invento.');
            }

            // el material debe ser de una categoría admitida por el invento
            $category = optional($inventoryMaterial->material->materialType)->category;
            if (!$inventionType->acceptsMaterialCategory($category)) {
                return view('zones.invent', compact('zone', 'inventoryMaterials', 'inventionTypes', 'user'))
                    ->with('error', 'Con ' . $inventoryMaterial->material->name . ' no puedes forjar ' . $inventionType->name
                        . '. Necesitas material de tipo: ' . implode(' o ', $inventionType->material_types) . '.');
            }
        }

        // ingredientes extra (Fósforo, etc.): validar disponibilidad antes de consumir nada
        $extraMaterials = $inventionType->extra_materials ?? [];
        foreach ($extraMaterials as $req) {
            $reqQty = $req['qty'] ?? 1;
            $owned = $user->inventory->materials
                ->filter(fn($im) => optional($im->material)->name === $req['name'])
                ->sum('quantity');
            if ($owned < $reqQty) {
                return view('zones.invent', compact('zone', 'inventoryMaterials', 'inventionTypes', 'user'))
                    ->with('error', 'Te falta el ingrediente ' . $req['name'] . ' (necesitas ' . $reqQty . ') para forjar ' . $inventionType->name . '.');
            }
        }

        // verificar prerrequisitos (inventos previos) ANTES de consumir nada:
        // si no, se perdería el material aunque falte un requisito.
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

        // ── todo validado: a partir de aquí SÍ se consume ──
        // consumir material principal (si llega a 0 se elimina del inventario)
        if ($inventoryMaterial) {
            $inventoryMaterial->quantity -= 1;
            $inventoryMaterial->quantity <= 0 ? $inventoryMaterial->delete() : $inventoryMaterial->save();
        }

        // consumir ingredientes extra (de cualquier depósito que tenga el material)
        foreach ($extraMaterials as $req) {
            $remaining = $req['qty'] ?? 1;
            foreach ($user->inventory->materials->filter(fn($im) => optional($im->material)->name === $req['name'] && $im->quantity > 0) as $im) {
                $take = min($remaining, $im->quantity);
                $im->quantity -= $take;
                $im->quantity <= 0 ? $im->delete() : $im->save();
                $remaining -= $take;
                if ($remaining <= 0) break;
            }
        }

        // consumir SOLO los inventos previos REALMENTE requeridos (no los que el
        // jugador marcara por error). Para cada prerequisito, gasta su cantidad.
        foreach ($inventionType->needs as $need) {
            $qtyNeeded = max(1, (int) ($need->quantity ?? 1));
            $toConsume = $user->inventory->inventions
                ->whereIn('_id', $selectedInventions)
                ->where('inventiontype_id', $need->parent_id)
                ->take($qtyNeeded);
            foreach ($toConsume as $inv) {
                $inv->delete();
            }
        }
    
        // calcular puntos/eficiencia AHORA y guardarlos EN LA ACCIÓN (no en sesión),
        // para que cualquiera (segundo plano) pueda finalizar el invento.
        $baseDuration = $inventionType->level * 15;
        $duration = (strtolower(optional($user->role)->name ?? '') === 'inventor') ? $baseDuration : $baseDuration * 1.5;
        $duration *= app(\App\Services\UserService::class)->actionSpeedFactor($user); // ingenio acelera

        $pointsAndEfficiency = (new InventionPointsService())->calculatePointsAndEfficiency($inventionType, $materialId);

        Action::create([
            'user_id' => $user->id,
            'type_id' => Type::where('name', 'invent')->first()->id,
            'actionable_id' => $inventionType->id,
            'actionable_type' => InventionType::class,
            'duration' => $duration,
            'finish' => false,
            'invention_data' => $pointsAndEfficiency,
        ]);

        session()->put('actionBlocked', true);

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
