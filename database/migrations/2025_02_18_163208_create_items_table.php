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
        Schema::create('items', function (Blueprint $collection) {
            $collection->id();
            $collection->string('name');
            $collection->string('image');
            $collection->string('description');
            $collection->unsignedBigInteger('itemable_id')->nullable(); // relacion polimorfica con adventure y scenario
            $collection->string('itemable_type')->nullable(); 
            $collection->softDeletes();
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};


