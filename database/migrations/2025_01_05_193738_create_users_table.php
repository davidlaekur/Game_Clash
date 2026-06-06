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
        Schema::create('users', function (Blueprint $collection) {
            $collection->id(); 
            $collection->string('name'); 
            $collection->integer('capacity'); // Capacidad de inventario
            $collection->string('email')->unique();
            $collection->string('password');
            $collection->integer('points')->nullable(); // Puntos de experiencia
            $collection->unsignedBigInteger('role_id')->nullable(); // Relación con Rol
            $collection->unsignedBigInteger('team_id')->nullable(); // Relación con Team
            $collection->unsignedBigInteger('zone_id')->nullable(); // Relación con Zone
            $collection->softDeletes();
            $collection->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
