<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'mongodb';

    protected $collection = 'items';

    protected $fillable = [
        'name',
        'image',
        'description',
        'itemable_id',
        'itemable_type',
    ];

    /**
     * Relación polimórfica 
     */
    public function itemable()
    {
        return $this->morphTo();
    }

 

}

    



