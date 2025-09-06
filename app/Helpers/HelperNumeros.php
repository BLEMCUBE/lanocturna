<?php

//Rellenar zero izquierda
if (! function_exists('zero_fill')) {
	function zero_fill($valor, $long = 0)
	{
		return str_pad($valor, $long, '0', STR_PAD_LEFT);
	}
}

//redondear al mas cercano con decimal
if (! function_exists('roundHalfNearest')) {
	function roundHalfNearest(float $num,int $decimal=0,string $decimal_separation='.',string $thousands_separation='')
	{
		 return number_format(round($num * 2) / 2, $decimal, $decimal_separation,$thousands_separation);
	}
}
