<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class UserAdventure extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'user_adventures';

    protected $fillable = [
        'user_id',
        'adventure_id',
        'scenario_id',
        'completed'
    ];


    // relacion 1:N con Adventure simulando la pivot N:M entre user  y adventure

    public function adventure()
    {
        return $this->belongsTo(Adventure::class);
    }


    // relacion 1:N con User simulando la pivot N:M entre user y adventure
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
    // relacion N:1 con Scenarios ( necesito saber donde esta en que escenario esta el user y que aventura)
    public function scenario()
    {
        return $this->belongsTo(Scenario::class, 'scenario_id');
    }
}
