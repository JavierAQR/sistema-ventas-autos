<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    use HasFactory;

    // Le decimos a Laravel el nombre exacto de la tabla
    protected $table = 'cotizaciones';

    protected $fillable = [
        'prospecto_id', 
        'vehiculo_id', 
        'precio_final', 
        'estado', 
        'observaciones'
    ];

    // Relación: Una cotización PERTENECE a un prospecto
    public function prospecto()
    {
        return $this->belongsTo(Prospecto::class);
    }

    // Relación: Una cotización PERTENECE a un vehículo
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class);
    }
}
