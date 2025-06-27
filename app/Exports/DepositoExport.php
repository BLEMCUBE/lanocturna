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
		$this->id = $id;
	}
	public function view(): View
	{

		//        sku	producto	aduana	precio	unidad	pcs_bulto	bultos	cantidad_total	valor_total	cbm_bulto	cbm_total	codigo_barra

		$invoices = DepositoProducto::with(['producto' => function ($query) {
			$query
				/*->with(['costo_real' => function ($query) {
					$query->whereNot('monto', '=', 0)
						->select('monto', 'producto_id','fecha')->latest('fecha')->get();
				}])*/
				->select('id', 'nombre',  'origen');
		}])
		->with(['deposito_lista' => function ($query) {
			$query->select('id', 'nombre');
		}])
		->with(['costos_reales' => function ($query) {
				$query->whereNot('monto', '=', 0)
						->select('monto', 'producto_id','sku','fecha')->latest('fecha')->get();
		}])
		->select('*')->where('deposito_lista_id', $this->id)
			->where('bultos', '>', 0)->get();
			//dd($invoices);

		return view('excel.deposito', [
			'invoices' => $invoices
		]);
	}
}
