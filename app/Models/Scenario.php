<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Scenario extends Model
{
    use HasFactory, SoftDeletes;


    protected $connection = 'mongodb';

    protected $collection = 'scenarios';
    
    protected $fillable = [
        'question', 
        'adventure_id'     
      
    ];
    /**
     * Relación N:1 con adventures 
     */
    public function adventure()
    {
        return $this->belongsTo(Adventure::class, 'adventure_id'); 
    }

    // polimorfica 1:1 con item
    public function item()
    {
        return $this->morphOne(Item::class, 'itemable');
    }

   
    /**
     * Relación 1:N con option
     */
    public function options()
    {
        return $this->hasMany(Option::class, 'scenario_id'); 
    }

    // relacion 1:N con UserAdventure 

    public function userAdventures()
    {
        return $this->hasMany(UserAdventure::class, 'scenario_id');
    }
    

}
