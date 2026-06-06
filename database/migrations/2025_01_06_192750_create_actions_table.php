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
        Schema::create('actions', function (Blueprint $collection) {
            $collection->id(); 
            $collection->integer('duration')->nullable(); // Duración de la acción 
            $collection->boolean('finish')->default(false); // Indica si la acción ha terminado
            $collection->unsignedBigInteger('type_id'); // Relación con Type
            $collection->unsignedBigInteger('user_id'); // Relación con User
            $collection->unsignedBigInteger('actionable_id')->nullable(); // ID en la relación polimórfica
            $collection->string('actionable_type')->nullable(); // Tipo en la relación polimórfica
            $collection->softDeletes();
            $collection->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('actions');
    }
};
