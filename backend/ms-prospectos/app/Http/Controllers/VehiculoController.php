<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index()
    {
        $vehiculos = Vehiculo::withCount([
            'ventas as ventas_realizadas_count' => function ($query) {
                $query->where('estado', 'realizada');
            }
        ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($vehiculos, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'marca'  => 'required|string|max:255',
            'modelo' => 'required|string|max:255',
            'anio'   => 'required|integer',
            'precio' => 'required|numeric|min:0',
            'stock'  => 'required|integer|min:0',
            'estado' => 'nullable|string',
        ]);

        $vehiculo = Vehiculo::create($data);

        return response()->json([
            'mensaje' => 'Vehículo registrado',
            'vehiculo' => $vehiculo
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $vehiculo = Vehiculo::find($id);

        if (!$vehiculo) {
            return response()->json(['mensaje' => 'No encontrado'], 404);
        }

        $data = $request->validate([
            'marca'  => 'sometimes|string|max:255',
            'modelo' => 'sometimes|string|max:255',
            'anio'   => 'sometimes|integer',
            'precio' => 'sometimes|numeric|min:0',
            'stock'  => 'sometimes|integer|min:0',
            'estado' => 'nullable|string',
        ]);

        if (isset($data['stock'])) {
            $ventasRealizadas = $vehiculo->ventasRealizadasCount();

            if ($data['stock'] < $ventasRealizadas) {
                return response()->json([
                    'mensaje' => "No se puede establecer un stock de {$data['stock']}: ya existen {$ventasRealizadas} venta(s) realizada(s) para este vehículo. El stock mínimo permitido es {$ventasRealizadas}."
                ], 422);
            }
        }

        $vehiculo->update($data);

        return response()->json([
            'mensaje' => 'Vehículo actualizado',
            'vehiculo' => $vehiculo->fresh()
        ], 200);
    }

    public function destroy($id)
    {
        $vehiculo = Vehiculo::find($id);

        if (!$vehiculo) {
            return response()->json(['mensaje' => 'No encontrado'], 404);
        }

        $vehiculo->delete();

        return response()->json(['mensaje' => 'Vehículo eliminado'], 200);
    }
}
