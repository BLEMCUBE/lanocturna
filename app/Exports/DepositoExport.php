<?php

namespace App\Exports;

use App\Models\DepositoProducto;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DepositoExport implements FromView, ShouldAutoSize
{
    private $id;
    public function __construct($id)
    {
        $this->id=$id;

    }
    public function view(): View
    {

//        sku	producto	aduana	precio	unidad	pcs_bulto	bultos	cantidad_total	valor_total	cbm_bulto	cbm_total	codigo_barra

        return view('excel.deposito', [
            'invoices' => DepositoProducto::with(['producto' => function ($query) {
                    $query->select('id', 'nombre',  'origen');
            }])->with(['deposito_lista' => function ($query) {
                $query->select('id', 'nombre');
            }])->select('*')->where('deposito_lista_id',$this->id)
            ->where('bultos','>',0)->get()
        ]);
    }
}
