<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Type extends Model
{
    use softDeletes, HasFactory;
        
    protected $connection = 'mongodb'; 

    protected $fillable = [
        'name' //nombre del tipo de acción
    ];

    /**
     * Relación 1:N con Action.
     */
    public function actions()
    {
        return $this->hasMany(Action::class, 'type_id'); // Relación con Action
    }
}
