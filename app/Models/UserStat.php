<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model; 
use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\SoftDeletes;
class UserStat extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $connection = 'mongodb'; 

    protected $fillable = ['user_id', 'stat_id', 'value']; 

    /**
     * Relación N:1 con User.
     */
    public function user()
    {
        return $this->belongsTo(User::class); // Relación con User simulando tabal pivot en mongo
    }

    /**
     * Relación N:1 con Stat.
     */
    public function stat()
    {
        return $this->belongsTo(Stat::class); // Relación con Stat simulando  pivot en mongo
    }
}
