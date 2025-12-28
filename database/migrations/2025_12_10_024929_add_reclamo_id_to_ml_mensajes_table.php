<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration {
   public function up()
{
    Schema::table('ml_mensajes', function (Blueprint $table) {
        $table->unsignedBigInteger('reclamo_id')->nullable()->after('conversation_id')->index();
    });
}

public function down()
{
    Schema::table('ml_mensajes', function (Blueprint $table) {
        $table->dropColumn('reclamo_id');
    });
}
};
