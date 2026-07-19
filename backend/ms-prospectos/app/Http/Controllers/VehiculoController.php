<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index()
    {
        $vehiculos = Vehiculo::orderBy('created_at', 'desc')->get();
        return response()->json($vehiculos, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'marca' => 'required|string',
            'modelo' => 'required|string',
            'anio' => 'required|integer',
            'precio' => 'required|numeric',
            'stock' => 'required|integer'
        ]);

        $vehiculo = Vehiculo::create($request->all());
        return response()->json(['mensaje' => 'Vehículo registrado', 'vehiculo' => $vehiculo], 201);
    }

    public function update(Request $request, $id)
    {
        $vehiculo = Vehiculo::find($id);
        if (!$vehiculo) return response()->json(['mensaje' => 'No encontrado'], 404);

        $vehiculo->update($request->all());
        return response()->json(['mensaje' => 'Vehículo actualizado', 'vehiculo' => $vehiculo], 200);
    }

    public function destroy($id)
    {
        $vehiculo = Vehiculo::find($id);
        if (!$vehiculo) return response()->json(['mensaje' => 'No encontrado'], 404);

        $vehiculo->delete();
        return response()->json(['mensaje' => 'Vehículo eliminado'], 200);
    }
}
