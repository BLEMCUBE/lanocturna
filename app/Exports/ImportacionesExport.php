<?php

namespace App\Exports;

use App\Models\Importacion;
use App\Models\ImportacionDetalle;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ImportacionesExport implements FromView, ShouldAutoSize
{
    private $id;
    public function __construct($id)
    {
        $this->id=$id;

    }
    public function view(): View
    {

//        sku	producto	aduana	precio	unidad	pcs_bulto	bultos	cantidad_total	valor_total	cbm_bulto	cbm_total	codigo_barra

        return view('excel.importaciones', [
            'invoices' => ImportacionDetalle::with(['producto' => function ($query) {
                    $query->select('id', 'nombre', 'codigo_barra', 'origen','aduana');
            }])->select('*')->where('importacion_id',$this->id)->get()
        ]);
    }
}
