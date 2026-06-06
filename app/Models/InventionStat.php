<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\SoftDeletes;

class InventionStat extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'mongodb'; 
    protected $collection = 'invention_stats';

    protected $fillable = [
        'invention_id', 
        'stat_id',     
        'value',       
    ];

    /**
     * Relación N:1 con Invention.
     * Una estadística pertenece a una invención.
     */
    public function invention()
    {
        return $this->belongsTo(Invention::class); // simulando la pivot table en mongo
    }

    /**
     * Relación N:1 con Stat.
     * Una estadística pertenece a un tipo de Stat.
     */
    public function stat()
    {
        return $this->belongsTo(Stat::class); // simulando la pivot table en mongo
    }
}
