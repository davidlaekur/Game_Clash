<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class InventionType extends Model
{

    use SoftDeletes;
    protected $connection = 'mongodb'; 

    protected $fillable = [
        'type',
        'level',
        'materialtype_id',
        'image'
    ];

    /**
     * Relación N:1 con MaterialType.
     * Un tipo de invento pertenece a un tipo de material.
     */
    public function materialType()
    {
        return $this->belongsTo(MaterialType::class);
    }

    /**
     * Relación 1:N con Invention.
     * Un tipo de invento puede tener varios inventos.
     */ 

    public function inventions()
    {
        return $this->hasMany(Invention::class);
    }
    
 /**
     * Relación 1:N con Need (como "padre").
     *  Este tipo de invento puede ser requerido por varios otros tipos de inventos.
     */
    public function uses()
    {
        return $this->hasMany(Need::class, 'parent_id');
    }

    /**
     * Relación 1:N con Need (como "hijo").
     * Este tipo de invento necesita varios otros tipos de inventos.
     */
    public function needs()
    {
        return $this->hasMany(Need::class, 'child_id');
    }


}
