<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seguro extends Model
{
    use HasFactory;
    protected $fillable = [
        'venta_id',
        'aseguradora',
        'tipo_seguro',
        'prima_esperada',
        'prima_real',
        'estado',
        'observaciones'
    ];

    // Relación: Un seguro pertenece a una venta (cotización)
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}