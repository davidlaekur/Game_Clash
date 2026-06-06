<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Invention extends Model
{

    use SoftDeletes, HasFactory;
    protected $connection = 'mongodb';
    protected $collection = 'inventions';

    protected $fillable = [
        'name',           // Nombre de la invención
        'efficiency',     // Eficiencia de la invención
        'level',          // Nivel de la invención
        'points',         // Puntos que aporta la invención
        'material_id',    // ID del material asociado a la invención
        'inventiontype_id', // ID del tipo de invención
        'inventory_id',    // ID del inventor

    ];

    /**
     * Relación N:1 con Material.
     * Un invento solo tiene  un material.
     */
    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Relación 1:N polimórfica con Result.
     */
    public function result()
    {
        return $this->morphMany(Result::class, 'resultable');
    }

   /**
     * Relación N:1 polimórfica con Inventory.
     * Un invento pertenece a un inventario.
     */
    public function inventory()
    {
        return $this->morphTo();
    }

    /**
     * Relación 1:N polimórfica con Action.
     */
    public function actions()
    {
        return $this->morphMany(Action::class, 'actionable');
    }

    /**
     * Relación N:1 con InventionType.
     */
    public function inventionType()
    {
        return $this->belongsTo(InventionType::class);
    }
    
    
    /**
     * Relación N:M con stat a través de InventionStat. usando una relacion 1:N con InventionStat
     * Un invento tiene muchos stats.
     */

    public function stats()
    {
        return $this->hasMany(InventionStat::class, 'invention_id');
    }




}
