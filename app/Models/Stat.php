<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\SoftDeletes;
class Stat extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'mongodb'; 

    protected $fillable = [
        'name'
    ];

    /**
     * Relación 1:N  con UserStat.
     */
    public function userStats()
    {
        return $this->hasMany(UserStat::class, 'stat_id'); // Relación con UserStat simulando la pivot table en mongo   
    }

    /**
     * Relación 1:N  con InventionStat.
     */
    public function inventionStats()
    {
        return $this->hasMany(InventionStat::class, 'stat_id'); // Relación con InventionStat simulando la pivot table en mongo
    }
}
