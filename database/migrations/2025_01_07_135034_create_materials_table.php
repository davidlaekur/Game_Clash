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
        Schema::create('materials', function (Blueprint $collection) {
            $collection->id(); 
            $collection->string('name'); // Nombre del material
            $collection->float('efficiency'); // Eficiencia del material
            $collection->float('probability'); // Probabilidad de encontrarlo
            $collection->json('attributes')->nullable(); // Atributos adicionales del material en formato JSON, ejemplo ("peso": 10, "resistencia": 20, "dureza": 30)
            $collection->integer('quantity'); // Cantidad de material
            $collection ->unsignedBigInteger('zone_id'); // Relación con Zone
            $collection->unsignedBigInteger('materialtype_id'); // Relación con MaterialType
            $collection->softDeletes();
            $collection->timestamps(); 

            $collection->foreign('zone_id')->references('id')->on('zones'); 
            $collection->foreign('materialtype_id')->references('id')->on('material_types');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
