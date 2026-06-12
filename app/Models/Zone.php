<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Zone extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'mongodb'; 

    protected $fillable = [
        'name',
        'landscape',     
        'defense',       
        'latitude',
        'longitude',
        'difficulty',
        'team_id',
        'image',
        'image_detail',
        'regen_boost',   // multiplicador de regeneración de recursos (mina)
        'mine_ready_at', // cuándo termina de construirse la mina (en 2º plano)
        'event_type',      // evento de mundo activo (tormenta/bonanza/plaga)
        'event_ends_at',   // cuándo caduca el evento
        'event_magnitude', // intensidad del evento (p.ej. defensa que resta la tormenta)
        'explore_until',   // bloqueo: alguien está explorando la zona hasta este momento
    ];

    /**
     * Evento de mundo activo (o null si no hay o ya caducó).
     */
    public function activeEvent(): ?array
    {
        if (!$this->event_type || !$this->event_ends_at) {
            return null;
        }
        if (\Carbon\Carbon::parse($this->event_ends_at)->isPast()) {
            return null;
        }
        $meta = config('world_events')[$this->event_type] ?? null;
        if (!$meta) {
            return null;
        }
        return $meta + [
            'type' => $this->event_type,
            'magnitude' => (int) ($this->event_magnitude ?? 0),
            'remaining' => (int) now()->diffInSeconds($this->event_ends_at),
        ];
    }

    /** Defensa efectiva: la tormenta la reduce mientras dura. */
    public function effectiveDefense(): int
    {
        $d = (int) $this->defense;
        if ($this->activeEvent() && $this->event_type === 'tormenta') {
            $d -= (int) ($this->event_magnitude ?? 0);
        }
        return max(1, $d);
    }

    /** Multiplicador de regeneración por evento (bonanza acelera, plaga frena). */
    public function eventRegenMultiplier(): float
    {
        if (!$this->activeEvent()) {
            return 1.0;
        }
        return match ($this->event_type) {
            'bonanza' => 2.5,
            'plaga'   => 0.4,
            default   => 1.0,
        };
    }


    /**
     * Relación 1:N con User (usuarios en esta zona).
     */
    public function users()
    {
        return $this->hasMany(User::class, 'zone_id'); // Usuarios en esta zona
    }

    /**
     * Relación N:1 con Team (Equipo que controla esta zona).
     */
    public function team()
    {
        return $this->belongsTo(Team::class); // Equipo que controla esta zona
    }

 
    /**
     * Relación polimórfica 1:N con Action (acciones realizadas en la zona: atacar, explorar, mover, etc.).
     */
    public function actions()
    {
        return $this->morphMany(Action::class, 'actionable'); // Relación polimórfica
    }

   /**
 * Relación 1:N con Material.
 * Una zona puede contener muchos materiales.
 */
public function materials()
{
    return $this->hasMany(Material::class, 'zone_id'); // Materiales en esta zona
}





 /**
     * Calcular la distancia a otra zona.
     *
     * @param Zone $destination
     * @return float
     */
    public function calculateDistance(Zone $destination): float
    {
        return sqrt(
            pow($this->latitude - $destination->latitude, 2) + 
            pow($this->longitude - $destination->longitude, 2)
        );
    }


}
