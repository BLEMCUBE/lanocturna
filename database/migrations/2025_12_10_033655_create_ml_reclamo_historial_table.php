<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('ml_reclamo_historial', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reclamo_id');

            $table->string('event_id')->unique();
            $table->string('type');
            $table->string('source')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('date')->nullable();

            $table->json('raw')->nullable();

            $table->timestamps();

            $table->foreign('reclamo_id')
                ->references('id')
                ->on('ml_reclamos')
                ->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('ml_reclamo_historial');
    }
};

