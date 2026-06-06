<?php

use Illuminate\Database\Migrations\Migration;
use MongoDB\Laravel\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'mongodb';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('combats', function (Blueprint $collection) {
            $collection->id();
            $collection->integer('random_factor');
            $collection->integer('score');
            $collection->string('result_type'); // Polimórfico: tipo del resultado
            $collection->string('result_id'); // Polimórfico: ID del resultado
            $collection->unsignedBigInteger('attacker_team_id')->nullable(); // Equipo atacante
            $collection->unsignedBigInteger('defender_team_id')->nullable(); // Equipo defensor
            $collection->unsignedBigInteger('winner_team_id')->nullable(); // Equipo ganador
            $collection->softDeletes();
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('combats');
    }
};
