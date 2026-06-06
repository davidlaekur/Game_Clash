<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
class Message extends Model
{
    Use SoftDeletes;
    protected $connection = 'mongodb'; 

    protected $fillable = [
        'content',        // Contenido del mensaje
        'read',           // Estado de lectura del mensaje (true/false)
        'user_id',        // Relación directa con el User (receiver)
        'messageable_id', // ID de la relación polimórfica
        'messageable_type' // Tipo del modelo polimórfico
    ];

    /**
     * Relación N:1 con el usuario que recibe el mensaje.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación polimórfica N:1.
     */
    public function messageable()
    {
        return $this->morphTo();
    }
}
