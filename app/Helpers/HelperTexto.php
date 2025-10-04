<?php

if (!function_exists('func_str_to_upper_utf8')) {
	function func_str_to_upper_utf8($text)
	{
		if (is_null($text)) {
			return null;
		}
		return mb_strtoupper($text, 'utf-8');
	}
}

if (!function_exists('func_str_to_lower_utf8')) {
	function func_str_to_lower_utf8($text)
	{
		if (is_null($text)) {
			return null;
		}
		return mb_strtolower($text, 'utf-8');
	}
}

if (!function_exists('func_filter_items')) {
	function func_filter_items($query, $text)
	{
		$text_array = explode(' ', $text);
		foreach ($text_array as $txt) {
			$trim_txt = trim($txt);
			$query->where('text_filter', 'like', "%$trim_txt%");
		}

		return $query;
	}
}

if (!function_exists('generateUniqueDigitCode')) {
	function generateUniqueDigitCode(int $long = 6): string
	{
		$caracter = '9'; // El carácter que quieres repetir
		$cadena_repetida = str_repeat($caracter, $long);
		// Generate a random integer between 0 and 999999 (inclusive)
		// using cryptographically secure random_int() function.
		$randomNumber = random_int(0,$cadena_repetida);

		// Pad the number with leading zeros to ensure it's always 6 digits long.
		$uniqueCode = str_pad($randomNumber, $long, '0', STR_PAD_LEFT);

		return $uniqueCode;
	}
}
