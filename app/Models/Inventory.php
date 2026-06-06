<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;


class Inventory extends Model
{

    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $fillable = [
        'type',          // Tipo del inventario
        'name',          // Nombre del inventario
        'quantity',      // Cantidad total
        'inventoriable_id',   // ID polimórfico
        'inventoriable_type', // Tipo polimórfico
    ];


    /**
     * Relación N:M con material 
     * Un material puede estar en varios inventarios.
     */
    public function materials()
    {
        return $this->hasMany(InventoryMaterial::class, 'inventory_id'); // Relación con InventoryMaterial

    }

    public function stats()
    {
        return $this->hasMany(UserStat::class, 'user_id'); // Relación con UserStat
    }


    /**
     * Relación 1:N con Invention.
     * Un inventario contiene múltiples inventos.
     */
    public function inventions()
    {
        return $this->hasMany(Invention::class, 'inventory_id');
    }

    /**
     * Relación polimórfica 1:1 (own) con Team.
     */
    public function inventoriable()
    {
        return $this->morphTo(); // Relación polimórfica
    }
}
