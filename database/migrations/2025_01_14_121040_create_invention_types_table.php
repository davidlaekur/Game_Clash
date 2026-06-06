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
        Schema::create('invention_types', function (Blueprint $collection) {
            $collection->id(); 
            $collection->string('type'); // Tipo de invento 
            $collection->integer('level'); // Nivel del invento
            $collection->unsignedBigInteger('materialtype_id'); // Relación con MaterialType
            $collection->string('image')->nullable();
            $collection->softDeletes();
            $collection->timestamps(); 

            $collection->index('materialtype_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invention_types');
    }
};
