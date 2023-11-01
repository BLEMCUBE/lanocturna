<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RmaStock extends Model
{
    use  HasFactory;

    protected $table = 'rma_stock';
    protected $fillable = [
        'id',
        'sku',
        'producto_completo',
        'eliminado',
        'cantidad_total',
        'rma_id',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class,'sku','origen');

    }

    public function rma()
    {
        return $this->belongsTo(Rma::class);

    }
}
