<?php
namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Result extends Model
{

    use SoftDeletes;
    
    protected $connection = 'mongodb';

    protected $fillable = [
        'result',
        'description',
        'action_id',      // Relación con Action
        'resultable_id',  // ID polimórfico
        'resultable_type' // Tipo polimórfico
    ];

    /**
     * Relación N:1 con Action.
     */
    public function action()
    {
        return $this->belongsTo(Action::class, 'action_id');
    }

    /**
     * Relación polimórfica N:1 con Material e Inventiomn
     */
    public function resultable()
    {
        return $this->morphTo();
    }

    /**
     * Relación 1:1 con Combat (polimórfica).
     */
    public function combat()
    {
        return $this->morphOne(Combat::class, 'resultable');
    }
}
