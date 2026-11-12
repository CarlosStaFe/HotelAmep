<?php

namespace App\Http\Controllers;

use App\Models\Calendario;
use App\Models\Habitacion;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReservaController extends Controller
{
    public function cambiarEstado(Request $request, Reserva $reserva)
    {
        $datos = $request->validate([
            'estado' => 'required|in:Confirmada,Ocupada,Liberada,Disponible',
        ]);

        DB::transaction(function () use ($reserva, $datos) {
            Habitacion::whereKey($reserva->habitacion_id)->lockForUpdate()->firstOrFail();
            $reserva = Reserva::whereKey($reserva->id)->lockForUpdate()->firstOrFail();
            $estado = $datos['estado'];
            $transiciones = [
                'Reservada' => ['Confirmada', 'Disponible'],
                'Confirmada' => ['Ocupada', 'Disponible'],
                'Ocupada' => ['Liberada', 'Disponible'],
                'Liberada' => ['Disponible'],
            ];

            if (! in_array($estado, $transiciones[$reserva->estado] ?? [], true)) {
                throw ValidationException::withMessages([
                    'estado' => 'La operación no está permitida para el estado actual de la reserva.',
                ]);
            }

            $inicio = Carbon::parse($reserva->fecha_inicio)->startOfDay();
            $fin = Carbon::parse($reserva->fecha_fin)->endOfDay();
            $solapada = Reserva::where('habitacion_id', $reserva->habitacion_id)
                ->where('id', '!=', $reserva->id)
                ->where('fecha_inicio', '<=', $reserva->fecha_fin)
                ->where('fecha_fin', '>=', $reserva->fecha_inicio)
                ->lockForUpdate()
                ->exists();
            $eventos = Calendario::where('habitacion_id', $reserva->habitacion_id)
                ->whereBetween('start', [$inicio, $fin])
                ->lockForUpdate()
                ->get();
            $fechas = $eventos->map(fn ($evento) => Carbon::parse($evento->start)->format('Y-m-d'))->unique();
            $dias = (int) $inicio->diffInDays($fin->copy()->startOfDay()) + 1;

            if ($solapada || $fechas->count() !== $dias || $eventos->contains(
                fn ($evento) => $evento->estado !== $reserva->estado
                    || (string) $evento->pasajero_id !== (string) $reserva->pasajero_id
                    || $evento->description !== $reserva->detalle
            )) {
                throw ValidationException::withMessages([
                    'estado' => 'No se pudo actualizar: las fechas del calendario no coinciden con la reserva o se superponen con otra reserva.',
                ]);
            }

            $cambios = [
                'estado' => $estado,
                'color' => match ($estado) {
                    'Confirmada' => '#F29727',
                    'Ocupada' => '#B82E27',
                    'Liberada' => '#A69F9F',
                    'Disponible' => '#0CB371',
                },
            ];
            if ($estado === 'Disponible') {
                $cambios['pasajero_id'] = null;
                $cambios['description'] = '';
            }

            Calendario::whereKey($eventos->modelKeys())->update($cambios);
            if ($estado === 'Disponible') {
                $reserva->delete();
            } else {
                $reserva->update(['estado' => $estado]);
            }
        });

        return redirect()->route('calendarios.index')
            ->with('success', $datos['estado'] === 'Disponible'
                ? 'Las fechas quedaron disponibles y la reserva fue eliminada.'
                : 'El estado de la reserva se actualizó a '.$datos['estado'].'.');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Reserva $reserva)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reserva $reserva)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reserva $reserva)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reserva $reserva)
    {
        //
    }
}
