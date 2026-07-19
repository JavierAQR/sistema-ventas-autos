<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use Illuminate\Http\Request;

class CotizacionController extends Controller
{
    public function index()
    {
        // Magia de Eloquent: Trae la cotización JUNTO con los datos del prospecto y el vehículo
        $cotizaciones = Cotizacion::with(['prospecto', 'vehiculo'])->orderBy('created_at', 'desc')->get();
        return response()->json($cotizaciones, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'prospecto_id' => 'required|exists:prospectos,id',
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'precio_final' => 'required|numeric'
        ]);

        $data = $request->validate([
            'prospecto_id' => 'required|exists:prospectos,id',
            'vehiculo_id' => 'required|exists:vehiculos,id',
            'precio_final' => 'required|numeric|min:0',
            'estado' => 'nullable|in:pendiente,aprobada,rechazada',
            'observaciones' => 'nullable|string'
        ]);

        $cotizacion = Cotizacion::create($data);

        return response()->json(['mensaje' => 'Cotización generada', 'cotizacion' => $cotizacion], 201);
    }

    public function update(Request $request, $id)
    {
        $cotizacion = Cotizacion::find($id);
        if (!$cotizacion) return response()->json(['mensaje' => 'No encontrada'], 404);

        $cotizacion->update($request->all());$data = $request->validate([
            'prospecto_id' => 'sometimes|exists:prospectos,id',
            'vehiculo_id' => 'sometimes|exists:vehiculos,id',
            'precio_final' => 'sometimes|numeric|min:0',
            'estado' => 'sometimes|in:pendiente,aprobada,rechazada',
            'observaciones' => 'nullable|string'
        ]);

        $cotizacion->update($data);
        
        return response()->json(['mensaje' => 'Cotización actualizada', 'cotizacion' => $cotizacion], 200);
    }

    public function destroy($id)
    {
        $cotizacion = Cotizacion::find($id);
        if (!$cotizacion) return response()->json(['mensaje' => 'No encontrada'], 404);

        $cotizacion->delete();
        return response()->json(['mensaje' => 'Cotización eliminada'], 200);
    }
}
