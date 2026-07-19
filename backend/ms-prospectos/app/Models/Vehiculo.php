<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';

    protected $fillable = [
        'marca', 
        'modelo', 
        'anio', 
        'precio', 
        'stock', 
        'estado'
    ];

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}
