<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ml_ordenes', function (Blueprint $table) {

            $table->string('shipping_paid_by', 20)
                ->nullable()
                ->comment('COMPRADOR | VENDEDOR | COMPARTIDO | GRATIS');

            $table->decimal('shipping_buyer_cost', 10, 2)
                ->nullable()
                ->comment('Costo de envío pagado por el comprador');

            $table->decimal('shipping_seller_cost', 10, 2)
                ->nullable()
                ->comment('Costo de envío absorbido por el vendedor');

            $table->string('shipping_detected_by', 50)
                ->nullable()
                ->comment('Fuente: shipping.costs | base_cost | logistic_type | fallback');
        });
    }

    public function down(): void
    {
        Schema::table('ml_ordenes', function (Blueprint $table) {
            $table->dropColumn([
                'shipping_paid_by',
                'shipping_buyer_cost',
                'shipping_seller_cost',
                'shipping_detected_by',
            ]);
        });
    }
};
