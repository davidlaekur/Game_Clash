<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Action;
use App\Models\Invention;
use App\Models\InventionType;
use App\Models\InventionStat;
use App\Models\Stat;
use App\Models\User;

class MonitorAction
{
    public function handle(Request $request, Closure $next)
    {
        // 1) Finalizar TODAS las acciones vencidas (de cualquier jugador), no solo
        //    las del conectado: así el juego "avanza en segundo plano" en cuanto
        //    cualquiera carga una página. (Sin cron; sirve para el multijugador.)
        $this->finalizeElapsedActions();

        // 2) Estado de bloqueo/tiempo restante del jugador actual.
        $user = auth()->user();
        if ($user) {
            $action = Action::where('user_id', $user->id)
                ->where('finish', false)
                ->latest('created_at')
                ->first();

            if ($action) {
                $remaining = max(0, $action->duration - Carbon::now()->diffInSeconds($action->created_at));
                User::where('_id', $user->id)->update([
                    'actionBlocked' => $remaining > 0,
                    'timeRemaining' => $remaining,
                ]);
                if ($remaining <= 0) {
                    session()->forget('actionBlocked');
                }
            } else {
                session()->forget('actionBlocked');
            }
        }

        return $next($request);
    }

    /** Cierra todas las acciones cuyo temporizador ya venció y aplica sus efectos. */
    private function finalizeElapsedActions(): void
    {
        $pending = Action::where('finish', false)->get();
        foreach ($pending as $action) {
            $elapsed = Carbon::now()->diffInSeconds($action->created_at);
            if ($elapsed >= (int) $action->duration) {
                $this->finalize($action);
            }
        }
    }

    /** Finaliza una acción concreta para su dueño (no para el conectado). */
    private function finalize(Action $action): void
    {
        $action->update(['finish' => true]);

        $owner = User::find($action->user_id);
        if (!$owner) {
            return;
        }
        // solo se muestra aviso al jugador que está mirando ahora mismo
        $isCurrent = auth()->id() && (string) auth()->id() === (string) $action->user_id;

        $type = optional($action->type)->name;

        if ($type === 'explore') {
            $zone = $action->actionable;
            if (!$zone) {
                return;
            }
            if ($zone->isClaimLocked()) {
                $zone->explore_until = null;
                $zone->save();
                if ($isCurrent) {
                    session()->flash('warning', "La {$zone->name} está en revuelta tras una rendición; aún no puede reclamarse.");
                }
            } elseif ($zone->team_id === null) {
                $zone->team_id = $owner->team_id;
                $zone->explore_until = null;
                $zone->save();
                $owner->addMerit(5);
                if ($isCurrent) {
                    session()->flash('success', "Has completado la exploración. La {$zone->name} ahora pertenece a tu equipo.");
                }
            } else {
                $zone->explore_until = null;
                $zone->save();
                if ($isCurrent) {
                    session()->flash('warning', "La {$zone->name} ya fue tomada por otro equipo mientras la explorabas.");
                }
            }
            return;
        }

        if ($type === 'invent') {
            $inventionType = InventionType::find($action->actionable_id);
            if (!$inventionType || !$owner->inventory) {
                return;
            }
            // datos guardados en la acción (no en sesión): funciona para cualquiera
            $data = $action->invention_data ?: ['points' => $inventionType->level, 'efficiency' => 15, 'statFactor' => 1];

            $invention = Invention::create([
                'name' => $inventionType->name,
                'efficiency' => $data['efficiency'] ?? 15,
                'level' => $inventionType->level,
                'points' => $data['points'] ?? $inventionType->level,
                'inventiontype_id' => $inventionType->id,
                'inventory_id' => $owner->inventory->id,
            ]);

            $statFactor = $data['statFactor'] ?? 1;
            foreach ((config('invention_stats')[$invention->name] ?? []) as $statName => $value) {
                $stat = Stat::where('name', $statName)->first();
                if ($stat) {
                    InventionStat::create([
                        'invention_id' => $invention->id,
                        'stat_id' => $stat->id,
                        'value' => max(1, (int) round($value * $statFactor)),
                    ]);
                }
            }

            if ($inventionType->level >= 3) {
                $owner->addMerit(10);
            }
            if ($isCurrent) {
                session()->flash('success', "Se ha completado la creación del invento {$inventionType->name}.");
            }
        }
    }
}
