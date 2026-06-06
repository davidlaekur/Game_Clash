<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Adventure extends Model
{
    use HasFactory, SoftDeletes;


    protected $connection = 'mongodb';

    protected $collection = 'adventures';

    protected $fillable = [
        'name',      
        'image',     
        'description'
    ];


    //  1:N con UserAdventure  simulando la pivot N:M con user 

    public function users()
    {
        return $this->hasMany(UserAdventure::class, 'adventure_id'); 
    }


    // Relación 1:N  con  Scenarios
    public function scenarios()
    {
        return $this->hasMany(Scenario::class, 'adventure_id');
    }

        /**
     * Relación polimórfica 1:N con item
     */
    public function items()
    {
        return $this->morphMany(Item::class, 'itemable'); 
    }


}
