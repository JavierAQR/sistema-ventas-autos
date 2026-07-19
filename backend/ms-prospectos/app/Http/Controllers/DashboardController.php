<?php
namespace App\Http\Controllers;

use App\Models\Prospecto;
use App\Models\Venta;
use App\Models\Seguro;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function obtenerKPIs()
    {
        $totalProspectos = Prospecto::count();

        $ventasRealizadas = Venta::where('estado', 'realizada')->count();

        $ventasFallidas = Venta::where('estado', 'fallida')->count();

        $tasaConversion = $totalProspectos > 0
            ? round(($ventasRealizadas / $totalProspectos) * 100, 2)
            : 0;

        $segurosVinculados = Seguro::count();

        $segurosProspectados = Seguro::where('estado', 'prospectado')->count();

        $segurosVendidos = Seguro::where('estado', 'vendido')->count();

        return response()->json([

            'total_prospectos' => $totalProspectos,

            'ventas_realizadas' => $ventasRealizadas,

            'ventas_fallidas' => $ventasFallidas,

            'tasa_conversion' => $tasaConversion,

            'seguros_vinculados' => $segurosVinculados,

            'seguros_prospectados' => $segurosProspectados,

            'seguros_vendidos' => $segurosVendidos,

            'embudo' => [

                'prospeccion' => Prospecto::where('estado', 'prospeccion')->count(),

                'calificacion' => Prospecto::where('estado', 'calificacion')->count(),

                'negociacion' => Prospecto::where('estado', 'negociacion')->count(),

                'cierre' => Prospecto::where('estado', 'cierre')->count(),

            ]

        ]);
    }
}