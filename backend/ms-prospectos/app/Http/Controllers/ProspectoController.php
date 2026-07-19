<?php

namespace App\Http\Controllers;

use App\Models\Prospecto;
use Illuminate\Http\Request;

class ProspectoController extends Controller
{
    // Mostrar todos los prospectos
    public function index()
    {
        // Traemos todos los prospectos ordenados por el más reciente
        $prospectos = Prospecto::orderBy('created_at', 'desc')->get();
        return response()->json($prospectos, 200);
    }

    // Crear un nuevo prospecto
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|email',
            'telefono' => 'nullable|string|max:20',
            'vehiculo_interes' => 'required|string|max:255',

            'estado' => 'nullable|in:prospeccion,calificacion,negociacion,cierre'
        ]);

        $data['estado'] ??= 'prospeccion';

        $prospecto = Prospecto::create($data);

        return response()->json([
            'mensaje' => 'Prospecto registrado correctamente',
            'prospecto' => $prospecto
        ], 201);
    }
    
    // Actualizar un prospecto existente
    public function update(Request $request, $id)
    {
        $prospecto = Prospecto::find($id);

        if (!$prospecto) {
            return response()->json([
                'mensaje' => 'Prospecto no encontrado'
            ], 404);
        }

        $data = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'apellido' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'telefono' => 'nullable|string|max:20',
            'vehiculo_interes' => 'sometimes|string|max:255',

            'estado' => 'sometimes|in:prospeccion,calificacion,negociacion,cierre'
        ]);

        $prospecto->update($data);

        return response()->json([
            'mensaje' => 'Prospecto actualizado correctamente',
            'prospecto' => $prospecto
        ], 200);
    }

    public function show($id)
    {
        $prospecto = Prospecto::find($id);

        if (!$prospecto) {
            return response()->json([
                'mensaje' => 'Prospecto no encontrado'
            ], 404);
        }

        return response()->json($prospecto, 200);
    }

    // Eliminar un prospecto
    public function destroy($id)
    {
        $prospecto = Prospecto::find($id);

        if (!$prospecto) {
            return response()->json(['mensaje' => 'Prospecto no encontrado'], 404);
        }

        $prospecto->delete();

        return response()->json(['mensaje' => 'Prospecto eliminado correctamente'], 200);
    }
}
