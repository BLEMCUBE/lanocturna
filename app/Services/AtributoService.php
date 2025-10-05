<?php

namespace App\Services;

use App\Http\Resources\AtributoListaCollection;
use App\Models\Atributo;
use Illuminate\Support\Facades\DB;

class AtributoService
{
	public function getAtributos()
	{
		$query_item = Atributo::with(['valores' => function ($query) {
			$query->with(['atributo' => function ($query) {
				$query->select(DB::raw("id,nombre"));
			}])->select(DB::raw("id,valor,atributo_id"));
		}])->select('id', 'nombre')->get();

		$item=new AtributoListaCollection($query_item);

		return $item;
	}
	public function getProductoAtributos($id):array
	{
		$item = DB::table('atributo_valor_producto as avp')
		->join('atributo_valores as av','av.id','avp.atributo_valor_id')
		->join('atributos as at','at.id','av.atributo_id')
		->select('av.id','av.valor','at.nombre','av.atributo_id','avp.producto_id')
		->where('avp.producto_id','=',$id)
		->get()->toArray();
		return $item??[];
	}

	public function getValores():array
	{
		$item = DB::table('atributo_valores as av')
		->join('atributos as at','at.id','av.atributo_id')
		->select('av.id','av.valor','at.nombre','av.atributo_id'
		)
		->get()->toArray();
		return $item??[];
	}
}
