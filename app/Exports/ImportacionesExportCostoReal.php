<?php

namespace App\Exports;

use App\Models\Importacion;
use App\Models\ImportacionDetalle;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ImportacionesExportCostoReal implements FromView, ShouldAutoSize
{
	private $id;
	public function __construct($id)
	{
		$this->id = $id;
	}
	public function view(): View
	{

		//id	import_id	sku		costo_real
		return view('excel.importaciones-costo-real', [
			'invoices' => ImportacionDetalle::select('id', 'sku', 'costo_real', 'importacion_id')->where('importacion_id', $this->id)->get()
		]);
	}
}
