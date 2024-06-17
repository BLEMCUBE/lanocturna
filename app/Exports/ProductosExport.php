<?php

namespace App\Exports;

use App\Models\Producto;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Facades\DB;

class ProductosExport implements FromView, ShouldAutoSize
{

    public function view(): View
    {


        return view('excel.productos', [
            'productos' => Producto::with(['categorias' => function ($query) {
                $query->select(DB::raw("id,name"))->orderBy('name', 'ASC');
            }])->get()
        ]);
    }
}
