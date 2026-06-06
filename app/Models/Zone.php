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
    ];


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
