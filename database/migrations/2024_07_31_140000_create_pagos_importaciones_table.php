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
        Schema::create('pagos_importaciones', function (Blueprint $table) {
            $table->id();
			$table->timestamp('fecha_pago')->nullable();
			$table->string('banco')->nullable();
			$table->string('nro_transaccion')->nullable();
			$table->float('monto',8,2)->default(0);
            $table->unsignedBigInteger('importacion_id')->nullable();
            $table->timestamps();

			$table->foreign('importacion_id')
            ->references('id')
            ->on('importaciones')
            ->onDelete('set null')
            ->onUpdate('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_importaciones');
    }
};
