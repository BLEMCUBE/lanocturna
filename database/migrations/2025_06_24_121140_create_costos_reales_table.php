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
		Schema::create('costos_reales', function (Blueprint $table) {
			$table->id();
			$table->date('fecha')->nullable();
			$table->string('sku')->nullable();
			$table->string('origen')->nullable();
			$table->string('moneda')->nullable();
			$table->decimal('monto', 20, 2)->default(0);
			$table->unsignedBigInteger('producto_id')->nullable();
			$table->unsignedBigInteger('compra_detalle_id')->nullable();
			$table->unsignedBigInteger('compra_id')->nullable();
			$table->unsignedBigInteger('importaciones_detalle_id')->nullable();
			$table->unsignedBigInteger('importacion_id')->nullable();
			$table->unsignedBigInteger('creador_id')->nullable();
			 $table->boolean('activo')->default(false);
			$table->timestamps();

			$table->foreign('producto_id')
				->references('id')
				->on('productos')
				->onDelete('cascade');

			$table->foreign('creador_id')
				->references('id')
				->on('users')
				->onDelete('set null')
				->onUpdate('set null');

			$table->foreign('compra_detalle_id')
				->references('id')
				->on('compra_detalles')
				->onDelete('set null')
				->onUpdate('set null');
			$table->foreign('compra_id')
				->references('id')
				->on('compras')
				->onDelete('set null')
				->onUpdate('set null');

			$table->foreign('importaciones_detalle_id')
				->references('id')
				->on('importaciones_detalles')
				->onDelete('set null')
				->onUpdate('set null');

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
		Schema::dropIfExists('costos_reales');
	}
};
