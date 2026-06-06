<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class MaterialType extends Model
{

    use SoftDeletes;
    protected $connection = 'mongodb'; 

    protected $fillable = [
        'category',
    ];

    /**
     * Relación 1:N con Material.
     */
    public function materials()
    {
        return $this->hasMany(Material::class, 'materialtype_id');
    }

     /**
     * Relación 1:N con InventionType.
     * Un tipo de material puede tener varios tipos de inventos.
     */
    public function inventionTypes()
    {
        return $this->hasMany(InventionType::class, 'materialtype_id');
    }
}
