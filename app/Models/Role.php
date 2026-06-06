<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;
class Role extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'mongodb'; 


    protected $fillable = [
        'name',           // Nombre del rol (explorer, collector, strategist, inventor)
        'base_capacity',  // Capacidad base asociada al rol
    ];

    /**
     * Relación 1:N con User.
     * Un rol puede estar asociado a varios usuarios.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }
}
