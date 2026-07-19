<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prospecto extends Model
{
    use HasFactory;

    // Especificamos la tabla por si Oracle es estricto con los nombres
    protected $table = 'prospectos';

    // Los campos que podemos llenar desde el formulario de Vue
    protected $fillable = [
        'nombre', 
        'apellido', 
        'email', 
        'telefono', 
        'vehiculo_interes', 
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