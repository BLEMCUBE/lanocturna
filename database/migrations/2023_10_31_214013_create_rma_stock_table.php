<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rma_stock', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->nullable();
            $table->string('producto_completo')->nullable();
            $table->float('cantidad_total',8,2)->default(0);
            $table->boolean('eliminado')->default(false);
            $table->unsignedBigInteger('rma_id')->nullable();
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rma_stock');
    }
};
