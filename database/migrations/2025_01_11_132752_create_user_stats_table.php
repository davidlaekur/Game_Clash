<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint; 
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_stats', function (Blueprint $collection) {
            $collection->id(); 
            $collection->unsignedBigInteger('user_id'); // Relación con User  
            $collection->unsignedBigInteger('stat_id'); // Relación con Stat
            $collection->unsignedBigInteger('role_id'); // Relación con Role
            $collection->integer('value'); // Valor de la estadística
            $collection->softDeletes();
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_stats');
    }
};
