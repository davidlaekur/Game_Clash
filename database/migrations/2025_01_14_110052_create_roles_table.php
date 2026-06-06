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
        Schema::create('roles', function (Blueprint $collection) {
            $collection->id(); // ID único
            $collection->string('name')->unique(); // Nombre único del rol (explorer, collector, etc.)
            $collection->integer('base_capacity'); // Capacidad al rol
            $collection->softDeletes();
            $collection->timestamps(); 
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles'); 
    }
};
