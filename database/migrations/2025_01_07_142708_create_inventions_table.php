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
        Schema::create('inventions', function (Blueprint $collection) {
            $collection->id();  
            $collection->string('name'); // Nombre del invento
            $collection->integer('efficiency'); // Eficiencia
            $collection->integer('level');    
            $collection->integer('points'); // Puntos que aporta el invento
            $collection->unsignedBigInteger('material_id'); // ID del material asociado
            $collection->unsignedBigInteger('inventiontype_id'); // ID del tipo de invento
            $collection->unsignedBigInteger('inventory_id'); // ID del inventario
            $collection->softDeletes();
            $collection->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventions');
    }
};
