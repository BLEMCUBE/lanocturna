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
            $table->string('item_id')->nullable()->after('orden_id');
            $table->string('item_sku')->nullable()->after('item_id');
            $table->timestamp('last_updated')->nullable()->after('date_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ml_ordenes', function (Blueprint $table) {
          $table->dropColumn([
				'last_updated',
				'item_sku',
				'item_id'
			]);
        });
    }
};
