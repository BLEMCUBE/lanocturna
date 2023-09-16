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
        Schema::table('productos', function (Blueprint $table) {
            //
            $table->float('arribado',8,2)->after('stock_futuro')->default(0);
            $table->float('en_camino',8,2)->after('arribado')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            //
            $table->dropColumn(['arribado','en_camino']);
        });
    }
};
