<?php

//Rellenar zero izquierda
if (! function_exists('zero_fill')) {
function zero_fill ($valor, $long = 0)
{
    return str_pad($valor, $long, '0', STR_PAD_LEFT);
}
}
