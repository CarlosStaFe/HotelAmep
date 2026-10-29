<?php

namespace App\Http\Controllers;

use App\Models\Calendario;
use App\Models\Habitacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CalendarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $calendario = Calendario::with('habitacion')->get();
        $habitaciones = Habitacion::query()
            ->where('venta', true)
            ->orderBy('numero')
            ->get();

        return view('admin.calendarios.index', compact('calendario', 'habitaciones'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function disponibilidad()
    {
        $habitaciones = Habitacion::query()
            ->where('venta', true)
            ->orderBy('numero')
            ->get();

        return view('admin.calendarios.disponibilidad', compact('habitaciones'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'habitacion_id' => 'required|exists:habitaciones,id',
            'estado' => 'required|in:Disponible,No Disponible,Reservada,Ocupada',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'origen' => 'nullable|in:calendario',
        ]);

        $habitacion = Habitacion::findOrFail($datos['habitacion_id']);

        DB::transaction(function () use ($datos, $habitacion) {
            $fecha = Carbon::parse($datos['fecha_inicio'])->startOfDay();
            $fechaFin = Carbon::parse($datos['fecha_fin'])->startOfDay();

            while ($fecha->lte($fechaFin)) {
                Calendario::create([
                    'title' => "Habitación {$habitacion->numero} - PAX {$habitacion->plazas} - Matri {$habitacion->matri} - Simple {$habitacion->simple} - Piso {$habitacion->piso} - Vista {$habitacion->vista} - Aire {$habitacion->aire} - Cuna {$habitacion->cuna} - Baño {$habitacion->toilette}",
                    'description' => 'Disponibilidad generada automáticamente',
                    'start' => $fecha->format('Y-m-d 00:00:00'),
                    'end' => $fecha->format('Y-m-d 23:59:59'),
                    'color' => $datos['estado'] === 'Disponible' ? '#0CB371' : '#0CB371',
                    'textColor' => '#000000',
                    'estado' => $datos['estado'],
                    'habitacion_id' => $habitacion->id,
                ]);

                $fecha->addDay();
            }
        });

        $ruta = ($datos['origen'] ?? null) === 'calendario'
            ? 'calendarios.index'
            : 'calendarios.disponibilidad';

        return redirect()->route($ruta)
            ->with('mensaje', 'Disponibilidad generada exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }

    /**
     * Display the specified resource.
     */
    public function show(Calendario $calendario)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Calendario $calendario)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Calendario $calendario)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Calendario $calendario)
    {
        //
    }
}
