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
        Schema::table('ml_ordenes', function (Blueprint $table) {
           $table->json('envio')->nullable()->after('payload');
           $table->json('facturacion')->nullable()->after('envio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ml_ordenes', function (Blueprint $table) {
           	$table->dropColumn(['envio','facturacion']);
        });
    }
};
