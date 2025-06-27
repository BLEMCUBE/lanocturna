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
		Schema::create('pagos_compras', function (Blueprint $table) {
			$table->id();
			$table->timestamp('fecha_pago')->nullable();
			$table->string('moneda')->nullable();
			$table->unsignedBigInteger('concepto_pago_id')->nullable();
			$table->string('nro_factura')->nullable();
			$table->double('monto')->default(0);
			$table->string('banco')->nullable();
			$table->string('nro_transaccion')->nullable();
			$table->text('observacion')->nullable();
			$table->unsignedBigInteger('user_id')->nullable();
			$table->unsignedBigInteger('compra_id')->nullable();
			$table->timestamps();

			$table->foreign('concepto_pago_id')
				->references('id')
				->on('concepto_pago')
				->onDelete('set null')
				->onUpdate('set null');

			$table->foreign('user_id')
				->references('id')
				->on('users')
				->onDelete('set null')
				->onUpdate('set null');

			$table->foreign('compra_id')
				->references('id')
				->on('compras')
				->onDelete('set null')
				->onUpdate('set null');
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::dropIfExists('pagos_compras');
	}
};
