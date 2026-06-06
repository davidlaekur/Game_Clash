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
        Schema::create('zones', function (Blueprint $collection) {
            $collection->id(); 
            $collection->string('name'); // Nombre de la zona
            $collection->string('landscape'); // Paisaje de la zona
            $collection->integer('latitude'); // Coordenada latitud
            $collection->integer('longitude'); // Coordenada longitud
            $collection->integer('difficulty'); // Nivel de dificultad
            $collection->integer('defense'); // Defensa base de la zona
            $collection->string('image')->nullable(); // Imagen
            $collection->string('image_detail')->nullable(); // Mapa detallado
            $collection->unsignedBigInteger('team_id')->nullable(); // Relación con Team
            $collection->softDeletes();
            $collection->timestamps(); 
        
            $collection->index(['latitude', 'longitude']);// segun docuementacion oficial Mongo/laravel optimiza las consultas 
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('zones'); 
    }
};
