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
         Schema::table('configuraciones', function (Blueprint $table) {
            // Cambia la columna 'value' de string a text
            $table->text('value')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuraciones', function (Blueprint $table) {
            // Opcional: Revierte el cambio a string en el método down
            $table->string('value', 255)->nullable()->change();
        });
    }
};
