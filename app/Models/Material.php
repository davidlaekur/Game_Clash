<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Material extends Model
{

    use SoftDeletes;
    protected $connection = 'mongodb'; 


    protected $fillable = [
        'name',
        'density',
        'efficiency',
        'probability',
        'attributes',
        'quantity',
        'max_quantity',     // tope al que regenera la zona
        'regenerated_at',   // última regeneración (cálculo perezoso)
        'materialtype_id',
        'zone_id',
    ];

    protected $casts = [
        'attributes' => 'array', // Laravel convierte automáticamente JSON en array 
    ];


    /**
     * Relación N:M con InventoryMaterial.
     * Un material puede estar en varios inventarios.
     */

    public function inventories()
    {
        return $this->hasMany(InventoryMaterial::class, 'material_id');

    }


    /**
     * Relación polimórfica 1:N con Result.
     * 
     */
    public function results()
    {
        return $this->morphMany(Result::class, 'resultable');
    }

    /**

     * Relación 1:N con Invention.
     * Un material puede estar asociado con muchos inventos.
     */
    public function inventions()
    {
        return $this->hasMany(Invention::class);
    }


     /**
     * Relación N:1 con MaterialType.
     */
    public function materialType()
    {
        return $this->belongsTo(MaterialType::class, 'materialtype_id');
    }

    /**
     * Relación N:1 con Zone.
     */

    public function zone()

    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    
}
