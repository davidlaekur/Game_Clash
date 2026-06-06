<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Action extends Model 
{

    use SoftDeletes, HasFactory;


    protected $connection = 'mongodb';

    protected $fillable = [
        'type_id',         // Relación N:1 con Type
        'actionable_id',   // ID para relación polimórfica (Zone, Invention)
        'actionable_type', // Tipo del modelo relacionado (Zone, Invention)
        'user_id',         // Relación N:1 con UserZone
        'type_id',         // Relación N:1 con Type
        'duration',        // Duración de la acción
        'finish',          // booleano finish  de la acción
    ];

    protected $casts = [
        'finish' => 'boolean',
        'duration' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    
  /**
     * Relación N:1 con User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }


    /**
     * Relación N:1 con Type.
     */
    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * Relación polimórfica N:1 (Zone, Invention).
     */
    public function actionable()
    {
        return $this->morphTo();
    }


    /**
     * Relación 1:N con Result.
     */
    public function results()
    {
        return $this->hasMany(Result::class);
    }

    /**
     * Relación polimórfica 1:N con Message.
     */
    public function messages()
    {
        return $this->morphMany(Message::class, 'messageable');
    }
}
