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
		Schema::table('mercadolibre_ventas', function (Blueprint $table) {
			$table->text('pack_id')->after('mercadolibre_venta_id')->nullable();
			$table->string('pack_status')->after('pack_id')->nullable();
			$table->text('claim_id')->after('pack_status')->nullable();
			$table->string('claim_status')->after('claim_id')->nullable();
		});
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('mercadolibre_ventas', function (Blueprint $table) {
			$table->dropColumn(['pack_id','claim_id','pack_status','claim_status']);
		});
	}
};
