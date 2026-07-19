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

    // Se incluye automáticamente en las respuestas JSON
    protected $appends = ['stock_disponible'];

    public function cotizaciones()
    {
        return $this->hasMany(Cotizacion::class);
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }

    public function getStockDisponibleAttribute()
    {
        $ventasRealizadas = $this->ventas_realizadas_count
            ?? $this->ventas()->where('estado', 'realizada')->count();

        return max(0, $this->stock - $ventasRealizadas);
    }

    // Conteo real de ventas realizadas (sin depender del accessor)
    public function ventasRealizadasCount()
    {
        return $this->ventas()->where('estado', 'realizada')->count();
    }

    public function tieneStockDisponible()
    {
        return $this->stock_disponible > 0;
    }
}
