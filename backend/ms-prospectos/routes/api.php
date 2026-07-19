<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProspectoController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SeguroController;
use App\Http\Controllers\VentaController;

// Ruta pública para iniciar sesión
Route::post('/login', [AuthController::class, 'login']);


// Rutas protegidas (Requerirán el token de acceso)
Route::middleware('auth:sanctum')->group(function () {
    // Aquí pondremos más adelante las rutas del dashboard y prospectos
    Route::get('/perfil', function (Illuminate\Http\Request $request) {
        return $request->user();
    });
    // Rutas para los prospectos
    Route::get('/prospectos', [ProspectoController::class, 'index']); // Listar
    Route::post('/prospectos', [ProspectoController::class, 'store']); // Crear
    Route::get('/prospectos/{id}', [ProspectoController::class, 'show']); // Ver uno
    Route::put('/prospectos/{id}', [ProspectoController::class, 'update']); // Actualizar
    Route::delete('/prospectos/{id}', [ProspectoController::class, 'destroy']); // Borrar

    // CRUD de Vehículos
    Route::get('/vehiculos', [VehiculoController::class, 'index']);
    Route::post('/vehiculos', [VehiculoController::class, 'store']);
    Route::put('/vehiculos/{id}', [VehiculoController::class, 'update']);
    Route::delete('/vehiculos/{id}', [VehiculoController::class, 'destroy']);

    // CRUD de Cotizaciones
    Route::get('/cotizaciones', [CotizacionController::class, 'index']);
    Route::post('/cotizaciones', [CotizacionController::class, 'store']);
    Route::put('/cotizaciones/{id}', [CotizacionController::class, 'update']);
    Route::delete('/cotizaciones/{id}', [CotizacionController::class, 'destroy']);

    // Endpoint para las métricas del Dashboard
    Route::get('/dashboard-kpis', [DashboardController::class, 'obtenerKPIs']);

    // CRUD de Seguros
    Route::get('/seguros', [SeguroController::class, 'index']);
    Route::post('/seguros', [SeguroController::class, 'store']);
    Route::put('/seguros/{id}', [SeguroController::class, 'update']);
    Route::delete('/seguros/{id}', [SeguroController::class, 'destroy']);

    // CRUD de Ventas
    Route::get('/ventas', [VentaController::class, 'index']);
    Route::post('/ventas', [VentaController::class, 'store']);
    Route::put('/ventas/{id}', [VentaController::class, 'update']);
    Route::delete('/ventas/{id}', [VentaController::class, 'destroy']);
});
