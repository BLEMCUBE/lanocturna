<?php

namespace App\Exports;

use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ProductosExport implements FromView, ShouldAutoSize
{

    public function view(): View
    {


        return view('excel.productos', [
            'productos' => Producto::get()
        ]);
    }
}
