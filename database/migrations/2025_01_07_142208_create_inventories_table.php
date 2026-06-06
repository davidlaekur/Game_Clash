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
        Schema::create('inventories', function (Blueprint $collection) {
            $collection->id(); // ID único
            $collection->string('type'); // Tipo de inventario
            $collection->string('name'); // Nombre del inventario
            $collection->integer('quantity')->default(0); // Cantidad inicial
            $collection->unsignedBigInteger('inventoriable_id')->nullable(); // ID polimórfico
            $collection->string('inventoriable_type')->nullable(); // Tipo polimórfico
            $collection->softDeletes();
            $collection->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
