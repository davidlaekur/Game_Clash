<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class InventionType extends Model
{

    use SoftDeletes;
    protected $connection = 'mongodb'; 

    protected $fillable = [
        'name',
        'level',
        'materialtype_id',
        'image',
        'icon',           // icono FA de respaldo cuando no hay imagen
        'material_types', // categorías de material admitidas (p.ej. ['Fibra'])
        'extra_materials',// ingredientes extra por nombre: [['name'=>'Fósforo','qty'=>1]]
    ];

    /**
     * ¿Este invento consume material? (la Trampa, p.ej., no usa ninguno)
     */
    public function requiresMaterial(): bool
    {
        return !empty($this->material_types);
    }

    /**
     * ¿La categoría de material indicada sirve para forjar este invento?
     */
    public function acceptsMaterialCategory(?string $category): bool
    {
        return $category !== null && in_array($category, $this->material_types ?? [], true);
    }

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
