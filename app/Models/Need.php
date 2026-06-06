<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Need extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $connection = 'mongodb';

    protected $fillable = [
        'parent_id',    // ID del invento "padre"
        'child_id',     // ID del invento "hijo"
        'quantity',     // Cantidad requerida
    ];

    /**
     * Relación N:1 con el invento "padre".
     */
    public function parent()
    {
        return $this->belongsTo(InventionType::class, 'parent_id');
    }

    /**
     * Relación N:1 con el invento "hijo".
     */
    public function child()
    {
        return $this->belongsTo(InventionType::class, 'child_id');
    }
}
