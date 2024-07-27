<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		$procedure = "
		CREATE PROCEDURE `sp_prod_vendido_comprado`(
	IN `sku` INT)
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT 'extrae el ultimo producto comprado y vendido'
BEGIN
SET @sk = sku ;
SELECT pro.id, (SELECT
DATE_FORMAT(imp.fecha_arribado ,'%d/%m/%Y') AS fecha
FROM productos AS pro1
JOIN importaciones_detalles AS impd ON impd.sku=pro1.origen
JOIN importaciones AS imp ON imp.id=impd.importacion_id
WHERE imp.estado='Arribado'
and pro1.id=pro.id
ORDER BY impd.id desc
 LIMIT 1)AS fecha_compra,(SELECT DATE_FORMAT(vd.created_at ,'%d/%m/%Y') AS fecha
FROM venta_detalles vd
WHERE vd.producto_id=pro.id
AND vd.producto_validado=1
ORDER BY vd.id DESC
LIMIT 1)AS fecha_venta FROM productos  AS pro
 WHERE pro.id=@sk;
END";
		DB::unprepared($procedure);
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		$procedure = "DROP PROCEDURE IF EXISTS sp_prod_vendido_comprado";
		DB::unprepared($procedure);
	}
};
