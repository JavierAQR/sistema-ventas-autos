<?php

namespace App\Http\Controllers;

use App\Models\Seguro;
use Illuminate\Http\Request;

class SeguroController extends Controller
{
    public function index()
    {
        $seguros = Seguro::with([
            'venta.prospecto',
            'venta.vehiculo'
        ])->orderBy('created_at', 'desc')->get();

        return response()->json($seguros, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            'aseguradora' => 'required|string|max:255',
            'tipo_seguro' => 'required|string|max:255',
            'prima_esperada' => 'required|numeric|min:0',
            'prima_real' => 'nullable|numeric|min:0',
            'estado' => 'required|in:prospectado,vendido',
            'observaciones' => 'nullable|string'
        ]);

        if ($data['estado'] === 'prospectado') {
            $data['prima_real'] = null;
        }

        $seguro = Seguro::create($data);

        return response()->json([
            'mensaje' => 'Seguro registrado correctamente.',
            'seguro' => $seguro
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $seguro = Seguro::find($id);

        if (!$seguro) {
            return response()->json([
                'mensaje' => 'Seguro no encontrado.'
            ], 404);
        }

        $data = $request->validate([
            'venta_id' => 'sometimes|exists:ventas,id',
            'aseguradora' => 'sometimes|string|max:255',
            'tipo_seguro' => 'sometimes|string|max:255',
            'prima_esperada' => 'sometimes|numeric|min:0',
            'prima_real' => 'nullable|numeric|min:0',
            'estado' => 'sometimes|in:prospectado,vendido',
            'observaciones' => 'nullable|string'
        ]);

        if (
            isset($data['estado']) &&
            $data['estado'] === 'prospectado'
        ) {
            $data['prima_real'] = null;
        }

        $seguro->update($data);

        return response()->json([
            'mensaje' => 'Seguro actualizado correctamente.',
            'seguro' => $seguro
        ]);
    }

    public function destroy($id)
    {
        $seguro = Seguro::find($id);

        if (!$seguro) {
            return response()->json([
                'mensaje' => 'Seguro no encontrado.'
            ], 404);
        }

        $seguro->delete();

        return response()->json([
            'mensaje' => 'Seguro eliminado correctamente.'
        ]);
    }
}