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
        Schema::create('messages', function (Blueprint $collection) {
            $collection->id(); 
            $collection->string('content'); // Contenido del mensaje
            $collection->boolean('read')->default(false); // Estado de lectura
            $collection->unsignedBigInteger('user_id')->nullable(); // Relación con User
            $collection->unsignedBigInteger('messageable_id')->nullable(); // ID de la relación polimórfica
            $collection->string('messageable_type')->nullable(); // Tipo de la relación polimórfica
            $collection->softDeletes();
            $collection->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
