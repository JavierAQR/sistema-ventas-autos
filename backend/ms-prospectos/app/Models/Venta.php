<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'prospecto_id',
        'vehiculo_id',
        'monto_venta',
        'estado',
        'motivo_perdida',
        'vendedor_id',
    ];

    public function prospecto()
    {
        return $this->belongsTo(Prospecto::class);
    }

    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }

    public function seguro()
    {
        return $this->hasOne(Seguro::class);
    }

   public function vendedor()
    {
        return $this->belongsTo(Vendedor::class);
    }
}