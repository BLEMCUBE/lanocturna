<?php

namespace App\Helpers;

use Illuminate\Support\Str;

class MercadoLibreClaimHelper
{
	private static $DETAILS = [
		'Llegó bien'   => 'el embalaje llegó bien',
		'Llegó dañado' => 'el embalaje llegó dañado',
	];

	private static $NAMES = [
		'not_working_item'     => 'el producto no funciona.',
		'damaged_item'         => 'el producto llegó dañado.',
		'missing_item'         => 'recibió menos unidades.',
		'respondent_unanswered'         => 'Vendedor no respondio',
		'missing_parts'        => 'faltan piezas del producto.',
		'missing_accessories'  => 'faltan partes o accesorios.',
		'wrong_item'           => 'el producto recibido no es el correcto.',
		'incomplete_item'      => 'el producto está incompleto.',
		'broken_item'      => 'el producto está dañado.',
		'repentant_buyer'      => 'el comprador no lo quiere.',
		'out_of_stock'      => 'el producto no tiene stock.',
		'different_than_published'      => 'el producto recibido es diferente a la publicación.',
		'different_item_other'      => 'el tipo de producto recibido es distinto al comprado.',
		'damaged_package_broken_item'      => 'el embalaje llegó dañado y el producto también.',
		'undelivered_repentant_buyer'      => 'el comprador pidió cancelar el envío y dijo que no autorizó la compra.',
		'different_color_or_size'      => 'el producto recibido no es el color, tamaño o modelo solicitado.',
	];
	public static function buildReason(string $name, ?string $detail = null): string
	{
		$nameText = self::$NAMES[$name] ?? Str::of($name)->replace('_', ' ')->lower();

		if (!$detail) {
			return ucfirst($nameText);
		}

		$detailText = self::$DETAILS[$detail] ?? Str::lower($detail);

		return ucfirst("El comprador dijo que {$detailText} pero {$nameText}");
	}
}
