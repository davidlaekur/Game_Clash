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
        Schema::create('user_adventures', function (Blueprint $collection) {
            $collection->id();
            $collection->unsignedBigInteger('user_id'); // relacion con user
            $collection->unsignedBigInteger('adventure_id'); // relacion con adventure
            $collection->unsignedBigInteger('scenario_id')->nullable(); // necesito saber donde esta el usuario
            $collection->boolean('completed')->default(false); // inicialemente en false, lo pondré a true si completa la aaventura
            $collection->softDeletes();
            $collection->timestamps();

            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_adventures');
    }
};
