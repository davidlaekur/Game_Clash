<?php

namespace App\Services;

use App\Models\User;
use App\Models\Team;
use App\Models\Zone;
use App\Models\Action;
use App\Models\Combat;
use App\Models\Result;
use App\Models\Type;
use App\Models\Material;
use App\Models\Inventory;
use App\Models\InventoryMaterial;
use App\Models\HallOfFame;

use App\Services\UserService;


class GameService
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }



    /**
     * calculo de puntos globales 
     */
    public function calculateGlobalPoints(User $user)
    {
        $pointsFromInventions = $user->inventory->inventions->sum('points');

        $pointsFromResources = 0;
        foreach ($user->inventory->materials as $material) {
            if ($material->quantity > 0) {
                $pointsFromResources += round($material->quantity / (1000 * ($material->probability / 100)));
            }
        }

        $pointsFromZones = $user->team ? $user->team->zones->count() * 10 : 0;

        return $pointsFromInventions + $pointsFromResources + $pointsFromZones;
    }

    public function calculateCombatPoints(Zone $zone)
    {
        $baseDefense = $zone->effectiveDefense(); // la tormenta baja la defensa
        $controlledByTeam = optional($zone->team)->id === optional(auth()->user())->team_id;

        // === DEFENSA ===
        // La mandan los stats: defensa + salud de los defensores. El genérico
        // 'points' ya NO infla la defensa (lo deciden los stats que se forjan).
        $defenders = $zone->users()->players()->with(['inventory.inventions.stats'])->get();

        $playerDefense = 0;   // Σ (defensa + salud) de los defensores
        $teamDefense = 0;     // refuerzo si el equipo controla la zona
        $bonusTimeDefense = count($defenders) * 2; // +2 por defensor presente

        foreach ($defenders as $player) {
            $s = $this->userService->getTotalStats($player);
            $def = ($s['defensa'] ?? 0) + ($s['salud'] ?? 0);
            $playerDefense += $def;
            if ($controlledByTeam) {
                $teamDefense += (int) floor($def / 2); // refuerzo: la mitad
            }
        }

        // bonus por tiempo atrincherado (condiciones de la zona)
        if (session()->has('zone_entry_time')) {
            $timeInZone = now()->diffInSeconds(session('zone_entry_time'));
            $bonusTimeDefense += floor($timeInZone / 1800) * 2; // +2 cada 30 min
        }

        // factor de azar por condiciones (lluvia, enfermedades...): varía por consulta
        $defenseFactor = rand(70, 130) / 100;
        $totalDefense = round($baseDefense + ($playerDefense + $teamDefense + $bonusTimeDefense) * $defenseFactor);

        // === ATAQUE (posicional) ===
        // El ataque lo lanza un jugador desde una zona propia adyacente; la fuerza
        // = suma de TODOS los compañeros de su equipo presentes en esa zona de origen
        // (la guarnición). Espejo de la defensa. velocidad = iniciativa; suerte = tirada.
        $attackTypeId = Type::where('name', 'attack')->first()->id;
        $attackAction = Action::where('actionable_id', $zone->id)
            ->where('actionable_type', Zone::class)
            ->where('type_id', $attackTypeId)
            ->where('finish', false)
            ->latest('created_at')
            ->first();

        $attackBase = 0;
        $initiativeBonus = 0;
        $luck = 0;
        if ($attackAction) {
            $initiator = User::find($attackAction->user_id);
            $origin = $initiator ? $initiator->zone : null;
            if ($origin && $initiator->team_id) {
                $garrison = User::where('zone_id', $origin->id)
                    ->where('team_id', $initiator->team_id)
                    ->players()
                    ->with(['inventory.inventions.stats'])
                    ->get();
                foreach ($garrison as $g) {
                    $s = $this->userService->getTotalStats($g);
                    $attackBase += $s['ataque'] ?? 0;
                    $initiativeBonus += $s['velocidad'] ?? 0;
                    $luck += $s['suerte'] ?? 0;
                }
            }
        }

        $luckBonus = min(0.30, $luck * 0.01); // suerte mejora la tirada, máx +0.30
        $attackFactor = rand(70, 130) / 100 + $luckBonus;
        $attackPoints = round(($attackBase + $initiativeBonus) * $attackFactor);

        return [
            'totalDefense' => $totalDefense,
            'playerDefense' => $playerDefense,
            'teamDefense' => $teamDefense,
            'bonusTimeDefense' => $bonusTimeDefense,
            'attackPoints' => $attackPoints,
            'attackBase' => $attackBase,
            'initiativeBonus' => $initiativeBonus,
            'luckBonus' => $luckBonus,
            'defenseFactor' => $defenseFactor,
            'attackFactor' => $attackFactor,
            'defenders' => $defenders,
            // alias para vistas existentes
            'totalDefensePoints' => $teamDefense,
            'totalAttackPoints' => $initiativeBonus,
        ];
    }




    /**
     * comporbacion si un equipo ha ganado la partida
     */
    public function checkVictoryCondition()
    {
        // se gana eliminando al rival: un solo equipo controla zonas y además
        // domina al menos la mitad del mapa (no basta con empatar a la mitad).
        $total = Zone::count();
        $umbral = (int) ceil($total / 2);

        $withZones = [];
        foreach (Team::all() as $team) {
            $count = Zone::where('team_id', $team->id)->count();
            if ($count > 0) {
                $withZones[$team->name] = $count;
            }
        }

        if (count($withZones) === 1) {
            $name = array_key_first($withZones);
            $count = $withZones[$name];
            if ($count >= $umbral) {
                return "¡{$name} ha expulsado a sus rivales y domina {$count} de {$total} territorios: gana la partida!";
            }
        }

        return null;
    }

    public function resolveAttack(Zone $zone, ?array $combatData = null)
    {
        // usa el combate ya calculado (lo que ve el jugador == lo que decide).
        $combatData = $combatData ?: $this->calculateCombatPoints($zone);
        $totalDefense = $combatData['totalDefense'];
        $attackPoints = $combatData['attackPoints'];

        // todas las acciones de ataque pendientes en la zona
        $attackActions = Action::where('actionable_id', $zone->id)
            ->where('actionable_type', Zone::class)
            ->where('type_id', Type::where('name', 'attack')->first()->id)
            ->where('finish', false)
            ->get();

        if ($attackActions->isEmpty()) {
            return "No hay ataque registrado.";
        }

        $attackingTeam = optional(User::find($attackActions->first()->user_id))->team_id;
        $defendingTeam = $zone->team_id;

        $winnerTeamId = null;
        $resultDescription = "";

        if ($attackPoints > $totalDefense) {
            // ataque exitoso: la zona pasa directamente al equipo atacante
            $winnerTeamId = $attackingTeam;
            $resultDescription = "¡Ataque exitoso! La zona pasa a tu equipo.";
            $zone->update(['team_id' => $attackingTeam]);

            // mérito por conquistar: a la guarnición de la zona de origen
            $initiator = User::find($attackActions->first()->user_id);
            if ($initiator && $initiator->zone_id) {
                foreach (User::where('zone_id', $initiator->zone_id)->where('team_id', $attackingTeam)->players()->get() as $g) {
                    $g->addMerit(20);
                }
            }

            // defensores derrotados: pierden parte de sus suministros, quedan heridos
            // y se repliegan a una zona propia (o fuera del mapa si no les queda).
            $retreat = $this->retreatFromZone($zone, $defendingTeam, true);
            if ($retreat['count'] > 0) {
                $resultDescription .= ' ' . $retreat['message'];
            }

            // BOTÍN: lo que sueltan los defensores lo saquea y reparte la guarnición
            // atacante (a partes iguales entre los presentes en la zona de origen).
            $origin = $initiator ? $initiator->zone_id : null;
            $taken = $this->distributeLoot($retreat['loot'] ?? [], $attackingTeam, $origin);
            if (!empty($taken)) {
                $resultDescription .= ' Botín repartido entre los conquistadores: ' . implode(', ', $taken) . '.';
            }
        } else {
            // la defensa aguanta (incluye empate)
            $winnerTeamId = $defendingTeam;
            $resultDescription = $attackPoints == $totalDefense
                ? "¡Empate! La zona se mantiene bajo control del equipo defensor."
                : "¡El equipo defensor ha resistido el ataque!";

            // castigo: cada atacante pierde ALGUNOS inventos (los más débiles)
            $lost = [];
            foreach ($attackActions->pluck('user_id')->unique() as $aid) {
                $attacker = User::find($aid);
                if ($attacker) {
                    $lost = array_merge($lost, $this->loseSomeInventions($attacker));
                }
            }
            if (!empty($lost)) {
                $resultDescription .= ' Los atacantes pierden: ' . implode(', ', $lost) . '.';
            }

            // recompensa: los defensores ganan experiencia y mérito por aguantar
            $defenders = $zone->users()->players()->get();
            foreach ($defenders as $d) {
                $d->points = ($d->points ?? 0) + 5;
                $d->merit = (int) ($d->merit ?? 0) + 10;
                $d->save();
            }
            if ($defenders->isNotEmpty()) {
                $resultDescription .= ' Los defensores ganan +5 de experiencia y +10 de mérito por resistir.';
            }
        }

        // CERRAR las acciones para que el combate no se vuelva a resolver
        Action::where('actionable_id', $zone->id)
            ->where('actionable_type', Zone::class)
            ->where('type_id', Type::where('name', 'attack')->first()->id)
            ->where('finish', false)
            ->update(['finish' => true]);

        $combat = Combat::create([
            'random_factor' => $combatData['attackFactor'] ?? 1,
            'score' => $attackPoints - $totalDefense,
            'attacker_team_id' => $attackingTeam,
            'defender_team_id' => $defendingTeam,
            'winner_team_id' => $winnerTeamId,
        ]);

        Result::create([
            'result' => $resultDescription,
            'description' => "Combate en la zona " . $zone->name,
            'resultable_id' => $combat->id,
            'resultable_type' => Combat::class
        ]);

        return $resultDescription;
    }

    /**
     * El atacante derrotado pierde ~1/3 de sus inventos, los de menor valor
     * (points) primero. No lo pierde todo: castigo real pero no demoledor.
     */
    private function loseSomeInventions(User $attacker): array
    {
        if (!$attacker->inventory) {
            return [];
        }
        $inventions = $attacker->inventory->inventions;
        $count = $inventions->count();
        if ($count === 0) {
            return [];
        }
        $toLose = max(1, (int) floor($count / 3));
        $lost = [];
        foreach ($inventions->sortBy('points')->take($toLose) as $invention) {
            $lost[] = $invention->name;
            $invention->delete();
        }
        return $lost;
    }

    /**
     * Repliega a los jugadores de un equipo presentes en una zona hacia una zona
     * propia: adyacente si la hay, si no cualquiera, y si al equipo no le queda
     * ninguna, fuera del mapa ("en retirada"). Si $penalize (derrota defendiendo)
     * pierden 1/3 de sus materiales y quedan Heridos. Reutilizado por "rendir zona".
     */
    public function retreatFromZone(Zone $zone, $teamId, bool $penalize = false): array
    {
        if (!$teamId) {
            return ['count' => 0, 'message' => '', 'refuge' => null];
        }
        $players = User::where('zone_id', $zone->id)->where('team_id', $teamId)->players()->get();
        if ($players->isEmpty()) {
            return ['count' => 0, 'message' => '', 'refuge' => null];
        }

        $refuge = $this->nearestFriendlyZone($zone, $teamId);
        $loot = []; // material_id => cantidad soltada (botín para el atacante)
        foreach ($players as $p) {
            if ($penalize) {
                foreach ($this->loseSomeMaterials($p) as $mid => $q) {
                    $loot[$mid] = ($loot[$mid] ?? 0) + $q;
                }
                $p->wound(20);
            }
            $p->zone_id = $refuge?->id;
            $p->save();
        }

        if ($penalize) {
            $where = $refuge ? "se repliegan a {$refuge->name}" : 'quedan dispersados fuera del mapa';
            $msg = "Los defensores pierden parte de sus suministros, quedan heridos y {$where}.";
        } else {
            $where = $refuge ? "se repliega a {$refuge->name}" : 'queda fuera del mapa';
            $msg = "La guarnición {$where} sana y salva.";
        }
        return ['count' => $players->count(), 'message' => $msg, 'refuge' => $refuge, 'loot' => $loot];
    }

    /** Zona propia más a mano para replegarse: adyacente primero, si no cualquiera. */
    private function nearestFriendlyZone(Zone $zone, $teamId): ?Zone
    {
        $friendly = Zone::where('team_id', $teamId)->get()
            ->filter(fn($z) => (string) $z->id !== (string) $zone->id)
            ->values();
        if ($friendly->isEmpty()) {
            return null;
        }
        $adjKeys = config('zone_adjacency')[(int) $zone->latitude . ',' . (int) $zone->longitude] ?? [];
        foreach ($adjKeys as $key) {
            [$lat, $lon] = array_map('intval', explode(',', $key));
            $match = $friendly->first(fn($z) => (int) $z->latitude === $lat && (int) $z->longitude === $lon);
            if ($match) {
                return $match;
            }
        }
        return $friendly->first();
    }

    /**
     * El jugador suelta ~1/3 de cada materia prima (suministros perdidos al huir).
     * Devuelve [material_id => cantidad] de lo soltado, para que el atacante lo saquee.
     */
    private function loseSomeMaterials(User $user): array
    {
        $dropped = [];
        if (!$user->inventory) {
            return $dropped;
        }
        foreach ($user->inventory->materials as $m) {
            $qty = (int) $m->quantity;
            if ($qty <= 0) {
                continue;
            }
            $loss = max(1, (int) floor($qty / 3));
            $mid = (string) $m->material_id;
            $dropped[$mid] = ($dropped[$mid] ?? 0) + $loss;
            $left = $qty - $loss;
            if ($left <= 0) {
                $m->delete();
            } else {
                $m->quantity = $left;
                $m->save();
            }
        }
        return $dropped;
    }

    /**
     * Reparte el botín (lo soltado por los defensores) a partes iguales entre la
     * guarnición atacante (los presentes en la zona de origen). Devuelve un
     * resumen legible de lo saqueado.
     */
    private function distributeLoot(array $loot, $attackingTeam, $originZoneId): array
    {
        if (empty($loot) || !$originZoneId || !$attackingTeam) {
            return [];
        }
        $garrison = User::where('zone_id', $originZoneId)
            ->where('team_id', $attackingTeam)
            ->players()
            ->get();
        if ($garrison->isEmpty()) {
            return [];
        }

        $n = $garrison->count();
        $taken = [];
        foreach ($loot as $materialId => $qty) {
            $qty = (int) $qty;
            if ($qty <= 0) {
                continue;
            }
            $per = intdiv($qty, $n);
            $rest = $qty % $n;
            $i = 0;
            foreach ($garrison as $g) {
                $give = $per + ($i < $rest ? 1 : 0);
                $i++;
                if ($give > 0) {
                    $this->addMaterialToInventory($g, $materialId, $give);
                }
            }
            $name = optional(Material::find($materialId))->name ?? 'material';
            $taken[] = "{$qty} de {$name}";
        }
        return $taken;
    }

    /** Añade (o incrementa) una materia prima en el inventario del jugador. */
    private function addMaterialToInventory(User $user, $materialId, int $qty): void
    {
        $inventory = Inventory::firstOrCreate(
            ['inventoriable_id' => $user->id, 'inventoriable_type' => get_class($user)],
            ['type' => 'personal', 'name' => 'Inventario de ' . $user->name]
        );
        $line = InventoryMaterial::where('inventory_id', $inventory->_id)
            ->where('material_id', $materialId)
            ->first();
        if ($line) {
            $line->quantity += $qty;
            $line->save();
        } else {
            InventoryMaterial::create([
                'inventory_id' => $inventory->_id,
                'material_id' => $materialId,
                'quantity' => $qty,
            ]);
        }
    }

    /**
     * Archiva la partida terminada en el Salón de la Fama (ganador + podio Top 3
     * por Gloria) y luego reinicia todo para una nueva partida.
     */
    public function archiveAndReset(string $victoryMessage): void
    {
        HallOfFame::create([
            'winner'   => $victoryMessage,
            'podium'   => $this->currentPodium(3),
            'ended_at' => now(),
        ]);
        $this->resetGame();
    }

    /** Top N jugadores por Gloria (para el podio y el Salón de la Fama). */
    public function currentPodium(int $top = 3): array
    {
        return User::players()->with('team')->get()
            ->sortByDesc(fn($u) => $u->glory())
            ->take($top)
            ->map(fn($u) => [
                'name'  => $u->name,
                'team'  => optional($u->team)->name,
                'glory' => $u->glory(),
            ])
            ->values()
            ->all();
    }

    /**
     * Reinicia la partida (modo "match"): zonas a neutral sin eventos/minas,
     * depósitos rellenados, jugadores a cero (puntos, méritos, gloria, herido,
     * ubicación) e inventarios vaciados. El Salón de la Fama se conserva aparte.
     */
    public function resetGame(): void
    {
        Zone::query()->update([
            'team_id' => null,
            'regen_boost' => 1,
            'mine_ready_at' => null,
            'event_type' => null,
            'event_ends_at' => null,
            'event_magnitude' => null,
            'explore_until' => null,
            'claim_locked_until' => null,
        ]);

        foreach (Material::whereNotNull('zone_id')->get() as $m) {
            if ($m->max_quantity) {
                $m->quantity = $m->max_quantity;
                $m->regenerated_at = now();
                $m->save();
            }
        }

        foreach (User::players()->get() as $user) {
            $user->points = 0;
            $user->merit = 0;
            $user->rank_score = 0;
            $user->wounded_until = null;
            $user->zone_id = null;
            $user->save();
            if ($user->inventory) {
                $user->inventory->inventions()->delete();
                $user->inventory->materials()->delete();
            }
        }
    }
}
