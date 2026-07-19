<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\Venta;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CotizacionController extends Controller
{
    public function index()
    {
        $cotizaciones = Cotizacion::with(['prospecto', 'vehiculo'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($cotizaciones, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'prospecto_id' => 'required|exists:prospectos,id',
            'vehiculo_id'  => 'required|exists:vehiculos,id',
            'precio_final' => 'required|numeric|min:0',
            'estado'       => 'required|in:pendiente,aprobada,rechazada',
        ]);

        $vehiculo = Vehiculo::find($data['vehiculo_id']);

        if (!$vehiculo->tieneStockDisponible()) {
            return response()->json([
                'mensaje' => 'No se puede cotizar: el vehículo no tiene stock disponible.'
            ], 422);
        }

        $cotizacion = Cotizacion::create($data);

        return response()->json([
            'mensaje' => 'Cotización registrada correctamente.',
            'cotizacion' => $cotizacion->fresh(['prospecto', 'vehiculo'])
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $cotizacion = Cotizacion::find($id);

        if (!$cotizacion) {
            return response()->json([
                'mensaje' => 'Cotización no encontrada'
            ], 404);
        }

        $data = $request->validate([
            'prospecto_id'  => 'sometimes|exists:prospectos,id',
            'vehiculo_id'   => 'sometimes|exists:vehiculos,id',
            'precio_final'  => 'sometimes|numeric|min:0',
            'estado'        => 'sometimes|in:pendiente,aprobada,rechazada',
            'observaciones' => 'nullable|string'
        ]);

        $seAprueba = isset($data['estado']) && $data['estado'] === 'aprobada';

        // Validar stock ANTES de guardar cualquier cambio, si se está aprobando
        if ($seAprueba) {
            $vehiculoId = $data['vehiculo_id'] ?? $cotizacion->vehiculo_id;
            $vehiculo = Vehiculo::find($vehiculoId);

            if (!$vehiculo || !$vehiculo->tieneStockDisponible()) {
                return response()->json([
                    'mensaje' => 'No se puede aprobar la cotización: el vehículo no tiene stock disponible.'
                ], 422);
            }
        }

        $cotizacion->update($data);

        /*
        |--------------------------------------------------------------------------
        | CONVERSIÓN AUTOMÁTICA A VENTA
        |--------------------------------------------------------------------------
        */

        if ($seAprueba) {
            Venta::firstOrCreate(
                [
                    'prospecto_id' => $cotizacion->prospecto_id,
                    'vehiculo_id'  => $cotizacion->vehiculo_id,
                ],
                [
                    'vendedor_id' => Auth::id(),
                    'monto_venta' => $cotizacion->precio_final,
                    'estado'      => 'realizada'
                ]
            );
        }

        return response()->json([
            'mensaje' => 'Cotización actualizada correctamente',
            'cotizacion' => $cotizacion->fresh(['prospecto', 'vehiculo'])
        ], 200);
    }

    public function destroy($id)
    {
        $cotizacion = Cotizacion::find($id);

        if (!$cotizacion) {
            return response()->json(['mensaje' => 'No encontrada'], 404);
        }

        $cotizacion->delete();

        return response()->json(['mensaje' => 'Cotización eliminada'], 200);
    }
}