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
        Schema::create('needs', function (Blueprint $collection) {
            $collection->id();
            $collection->unsignedBigInteger('parent_id'); // ID del invento "padre"
            $collection->unsignedBigInteger('child_id');  // ID del invento "hijo"
            $collection->integer('quantity'); // Cantidad requerida
            $collection->softDeletes();
            $collection->timestamps();


            //indice paara consultas en documentacion  oficial Mongo laravel    
            $collection->index('parent_id');
            $collection->index('child_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('needs');
    }
};
