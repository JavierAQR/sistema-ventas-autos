<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Vendedor extends Authenticatable
{
    use HasApiTokens, Notifiable;

    // Indicamos el nombre exacto de la tabla en Oracle
    protected $table = 'vendedores';

    // Indicamos nuestra clave primaria personalizada
    protected $primaryKey = 'id';

    // Los campos que se pueden llenar masivamente
    protected $fillable = [
        'nombre',
        'email',
        'password',
    ];

    // Ocultamos la contraseña cuando retornemos el usuario en un JSON
    protected $hidden = [
        'password',
    ];

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'vendedor_id');
    }
}