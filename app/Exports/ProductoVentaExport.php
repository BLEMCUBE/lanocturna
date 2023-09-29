<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductoVentaExport implements FromView, ShouldAutoSize
{
    private $datos;
    public function __construct($datos)
    {
        $this->datos=$datos;

    }
    public function view(): View
    {

        return view('excel.productoVentas', ['invoices'=>$this->datos]);
    }
}
