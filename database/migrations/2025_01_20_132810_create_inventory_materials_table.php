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
        Schema::create('inventory_materials', function (Blueprint $collection) {
            $collection->id(); 
            $collection->unsignedBigInteger('material_id'); // Relación con Material
            $collection->unsignedBigInteger('inventory_id'); // Relación con Inventory
            $collection->integer('quantity'); // Cantidad de material
            $collection->softDeletes();
            $collection->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_materials');
    }
};
