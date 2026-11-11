<?php

namespace App\Http\Controllers;

use App\Models\Calendario;
use App\Models\Habitacion;
use App\Models\Pasajero;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CalendarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reservas = Reserva::with(['habitacion', 'pasajero'])
            ->join('habitaciones', 'reservas.habitacion_id', '=', 'habitaciones.id')
            ->select('reservas.*')
            ->orderBy('reservas.fecha_inicio')
            ->orderBy('habitaciones.numero')
            ->get();

        return view('admin.calendarios.index', compact('reservas'));
    }

    public function calendario()
    {
        $calendario = Calendario::with('habitacion')->get();
        $pasajeros = Pasajero::orderBy('apelyNombre')->get();
        $habitaciones = Habitacion::query()
            ->where('venta', true)
            ->orderBy('numero')
            ->get();

        return view('admin.calendarios.calendario', compact('calendario', 'habitaciones', 'pasajeros'));
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

    public function reservar(Request $request)
    {
        $datos = $request->validate([
            'habitacion_id' => 'required|integer|exists:habitaciones,id',
            'fecha_inicio' => 'required|date_format:Y-m-d',
            'fecha_fin' => 'required|date_format:Y-m-d|after_or_equal:fecha_inicio',
            'pasajeros' => 'required|array|min:1',
            'pasajeros.*.id' => 'required|integer|distinct|exists:pasajeros,id',
            'pasajeros.*.comanda' => 'required|in:SI,NO',
        ], [
            'pasajeros.required' => 'Asigne al menos un pasajero para reservar.',
            'pasajeros.min' => 'Asigne al menos un pasajero para reservar.',
        ]);

        DB::transaction(function () use ($datos) {
            $habitacion = Habitacion::whereKey($datos['habitacion_id'])->lockForUpdate()->firstOrFail();
            $pasajeros = Pasajero::whereIn('id', collect($datos['pasajeros'])->pluck('id'))->get()->keyBy('id');
            $cantidad = $pasajeros->filter(function ($pasajero) {
                if (! $pasajero->fechaNacimiento) {
                    return true;
                }

                $nacimiento = Carbon::parse($pasajero->fechaNacimiento)->startOfDay();
                return $nacimiento->isFuture() || $nacimiento->diffInYears(today()) >= 5;
            })->count();

            if ($habitacion->plazas === null || $cantidad > $habitacion->plazas) {
                throw ValidationException::withMessages([
                    'pasajeros' => 'La cantidad de pasajeros supera las plazas de la habitación o su capacidad no está definida.',
                ]);
            }

            $inicio = Carbon::parse($datos['fecha_inicio'])->startOfDay();
            $fin = Carbon::parse($datos['fecha_fin'])->endOfDay();
            $eventos = Calendario::where('habitacion_id', $habitacion->id)
                ->whereBetween('start', [$inicio, $fin])
                ->lockForUpdate()
                ->get();
            $fechasDisponibles = $eventos->pluck('start')->map(fn ($fecha) => Carbon::parse($fecha)->format('Y-m-d'))->unique();
            $diasSolicitados = (int) $inicio->diffInDays($fin->copy()->startOfDay()) + 1;

            if ($eventos->contains(fn ($evento) => $evento->estado !== 'Disponible')
                || $fechasDisponibles->count() !== $diasSolicitados) {
                throw ValidationException::withMessages([
                    'fecha_inicio' => 'La habitación no está disponible en todas las fechas seleccionadas.',
                ]);
            }

            $descripcion = collect($datos['pasajeros'])->map(function ($asignacion) use ($pasajeros) {
                return $pasajeros[$asignacion['id']]->apelyNombre . ' (Comanda: ' . $asignacion['comanda'] . ')';
            })->implode(', ');
            $primerPasajeroId = reset($datos['pasajeros'])['id'];

            Calendario::whereKey($eventos->modelKeys())->update([
                'description' => $descripcion,
                'estado' => 'Reservada',
                'color' => '#CCCC18',
                'habitacion_id' => $habitacion->id,
                'pasajero_id' => $primerPasajeroId,
            ]);

            Reserva::create([
                'habitacion_id' => $habitacion->id,
                'pasajero_id' => $primerPasajeroId,
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
                'detalle' => $descripcion,
                'estado' => 'Reservada',
            ]);
        });

        return redirect()->route('calendarios.calendario')
            ->with('mensaje', 'Reserva realizada exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
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
                    'description' => '',
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
            ? 'calendarios.calendario'
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
