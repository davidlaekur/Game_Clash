<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $collection) {
            $collection->id();
            $collection->string('result'); // Resultado principal
            $collection->text('description')->nullable(); // Descripción del resultado
            $collection->unsignedBigInteger('action_id')->nullable(); // Relación con Action
            $collection->string('resultable_id')->nullable(); // ID polimórfico
            $collection->string('resultable_type')->nullable(); // Tipo polimórfico
            $collection->softDeletes();
            $collection->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
