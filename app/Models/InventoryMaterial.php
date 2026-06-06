<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;


class InventoryMaterial extends Model
{

    use SoftDeletes;

    protected $connection = 'mongodb'; 

    protected $fillable = [ 'material_id', 'inventory_id', 'quantity'];

    /**
     * Relación N:1 con User.
     */
    public function material()

    {
        return $this->belongsTo(Material::class);
    }
  

    /**
     * Relación N:1 con Stat.
     */
    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }
}

