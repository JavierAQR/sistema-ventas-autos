<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;

class VentaController extends Controller
{
    public function index()
    {
        $ventas = Venta::with(['prospecto', 'vehiculo', 'seguro'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($ventas, 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'prospecto_id'   => 'required|exists:prospectos,id',
            'vendedor_id' => 'required|exists:vendedores,id',
            'vehiculo_id'    => 'required|exists:vehiculos,id',
            'monto_venta'    => 'required|numeric|min:0',
            'estado'         => 'required|in:realizada,fallida',
            'motivo_perdida' => 'nullable|string'
        ]);

        if ($data['estado'] === 'fallida' && empty($data['motivo_perdida'])) {
            return response()->json([
                'mensaje' => 'El motivo de pérdida es obligatorio para una venta fallida.'
            ], 422);
        }

        if ($data['estado'] === 'realizada') {
            $data['motivo_perdida'] = null;
        }

        $venta = Venta::create($data);

        return response()->json([
            'mensaje' => 'Venta registrada correctamente.',
            'venta' => $venta
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            return response()->json([
                'mensaje' => 'Venta no encontrada.'
            ], 404);
        }

        $data = $request->validate([
            'prospecto_id'   => 'sometimes|exists:prospectos,id',
            'vendedor_id'    => 'sometimes|exists:vendedores,id',
            'vehiculo_id'    => 'sometimes|exists:vehiculos,id',
            'monto_venta'    => 'sometimes|numeric|min:0',
            'estado'         => 'sometimes|in:realizada,fallida',
            'motivo_perdida' => 'nullable|string'
        ]);

        // Determinar el estado final (el nuevo si viene, o el actual si no)
        $estadoFinal = $data['estado'] ?? $venta->estado;

        if ($estadoFinal === 'fallida') {
            $motivoFinal = $data['motivo_perdida'] ?? $venta->motivo_perdida;

            if (empty($motivoFinal)) {
                return response()->json([
                    'mensaje' => 'El motivo de pérdida es obligatorio para una venta fallida.'
                ], 422);
            }

            $data['motivo_perdida'] = $motivoFinal;
        }

        if ($estadoFinal === 'realizada') {
            $data['motivo_perdida'] = null;
        }

        $venta->update($data);

        return response()->json([
            'mensaje' => 'Venta actualizada correctamente.',
            'venta' => $venta->fresh(['prospecto', 'vehiculo', 'seguro'])
        ], 200);
    }

    public function destroy($id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            return response()->json([
                'mensaje' => 'Venta no encontrada.'
            ], 404);
        }

        $venta->delete();

        return response()->json([
            'mensaje' => 'Venta eliminada correctamente.'
        ], 200);
    }
}
