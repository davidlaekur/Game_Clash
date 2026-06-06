<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Option extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'options';

    protected $fillable = [
        'text',
        'is_correct',
         'scenario_id'    
    ];


   /**
     * Relación N:1 con scenario 
     */
    public function scenario()
    {
        return $this->belongsTo(Scenario::class, 'scenario_id');
    }

}
