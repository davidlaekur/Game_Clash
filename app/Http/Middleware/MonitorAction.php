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
        $user = auth()->user();

        if ($user) {
            $action = Action::where('user_id', $user->id)
                ->where('finish', false)
                ->latest('created_at')
                ->first();

            if ($action) {
                $timeElapsed = Carbon::now()->diffInSeconds($action->created_at);
                $timeRemaining = max(0, $action->duration - $timeElapsed);

                if ($timeRemaining > 0) {
                    User::where('_id', $user->id)->update([
                        'actionBlocked' => true,
                        'timeRemaining' => $timeRemaining
                    ]);
                } else {
                    
                    // marcar la acción como finalizada
                    $action->update(['finish' => true]);
                    session()->forget('actionBlocked');

                    if ($action->type->name === 'explore') {
                        $zone = $action->actionable;
                        if ($zone && $zone->team_id === null) {
                            $zone->team_id = $user->team_id;
                            $zone->explore_until = null; // libera el bloqueo
                            $zone->save();
                            $user->addMerit(5); // mérito por conquistar territorio
                            session()->flash('success', "Has completado la exploración. La {$zone->name} ahora pertenece a tu equipo.");
                        } elseif ($zone) {
                            $zone->explore_until = null;
                            $zone->save();
                            session()->flash('warning', "La {$zone->name} ya fue tomada por otro equipo mientras la explorabas.");
                        }
                    }

                    if ($action->type->name === 'invent') {
                        $inventionType = InventionType::find($action->actionable_id);
                        if ($inventionType) {
                               // Recuperar los datos de puntos y eficiencia desde la sesión
                               $pointsAndEfficiency = session('inventionPoints');

                            $invention = Invention::create([
                                'name' => $inventionType->name,
                                'efficiency' => $pointsAndEfficiency['efficiency'],
                                'level' => $inventionType->level,
                                'points' => $pointsAndEfficiency['points'],
                                'inventiontype_id' => $inventionType->id,
                                'inventory_id' => $user->inventory->id,
                            ]);

                                    // asignar estadísticas al invento (fuente única en config)
                            $inventionStats = config('invention_stats');

                            // el material modula los stats: más denso => más puntos
                            $statFactor = $pointsAndEfficiency['statFactor'] ?? 1;

                            if (isset($inventionStats[$invention->name])) {
                                foreach ($inventionStats[$invention->name] as $statName => $value) {
                                    $stat = Stat::where('name', $statName)->first();
                                    if ($stat) {
                                        InventionStat::create([
                                            'invention_id' => $invention->id,
                                            'stat_id' => $stat->id,
                                            'value' => max(1, (int) round($value * $statFactor)),
                                        ]);
                                    }
                                }
                            }

                            if ($inventionType->level >= 3) {
                                $user->addMerit(10); // mérito por forjar un invento de élite
                            }

                            session()->flash('success', "Se ha completado la creación del invento {$inventionType->name}.");
                        } 
                        
                        else {
                            session()->flash('error', "Error: El tipo de invento no existe.");
                        }
                    }
                }
            }
        }

        return $next($request);
    }
}
