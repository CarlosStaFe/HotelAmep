<?php

namespace App\Http\Controllers;

use App\Models\Asignada;
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
        $calendario->each(function (Calendario $evento) {
            if ($evento->estado === 'No Disponible') {
                $evento->color = Calendario::COLOR_NO_DISPONIBLE;
            }
        });
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

    public function asignadas()
    {
        $asignadas = Asignada::with(['habitacion', 'pasajero', 'reserva'])
            ->orderBy('checkin')
            ->get();
        $reservas = Reserva::with('habitacion')
            ->whereIn('estado', ['Reservada', 'Confirmada'])
            ->orderBy('fecha_inicio')
            ->get();
        $pasajeros = Pasajero::orderBy('apelyNombre')->get();
        $plazasOcupadas = $asignadas->groupBy('reserva_id')->map(
            fn ($grupo) => $grupo->unique('pasajero_id')
                ->filter(fn ($asignada) => $this->cuentaParaPlazas($asignada->pasajero))
                ->count()
        );

        return view('admin.calendarios.asignadas', compact('asignadas', 'reservas', 'pasajeros', 'plazasOcupadas'));
    }

    public function agregarAsignadas(Request $request)
    {
        $datos = $request->validate([
            'reserva_id' => 'required|integer|exists:reservas,id',
            'pasajeros' => 'required|array|min:1',
            'pasajeros.*.id' => 'required|integer|distinct|exists:pasajeros,id',
            'pasajeros.*.comanda' => 'required|in:SI,NO',
        ], [
            'pasajeros.required' => 'Asigne al menos un pasajero.',
            'pasajeros.min' => 'Asigne al menos un pasajero.',
        ]);

        DB::transaction(function () use ($datos) {
            $reservaInicial = Reserva::findOrFail($datos['reserva_id']);
            $habitacion = Habitacion::whereKey($reservaInicial->habitacion_id)->lockForUpdate()->firstOrFail();
            $reserva = Reserva::whereKey($datos['reserva_id'])->lockForUpdate()->firstOrFail();

            if ($reserva->habitacion_id != $habitacion->id || ! in_array($reserva->estado, ['Reservada', 'Confirmada'], true)) {
                throw ValidationException::withMessages([
                    'reserva_id' => 'La reserva ya no está disponible para agregar pasajeros.',
                ]);
            }

            $existentes = Asignada::where('reserva_id', $reserva->id)
                ->lockForUpdate()
                ->get();
            $nuevosIds = collect($datos['pasajeros'])->pluck('id');
            if ($existentes->pluck('pasajero_id')->intersect($nuevosIds)->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'pasajeros' => 'Hay pasajeros que ya están asignados a esta reserva.',
                ]);
            }

            $pasajeros = Pasajero::whereIn('id', $existentes->pluck('pasajero_id')->merge($nuevosIds))
                ->lockForUpdate()
                ->get();
            $cantidad = $pasajeros->filter(fn ($pasajero) => $this->cuentaParaPlazas($pasajero))->count();
            if ($habitacion->plazas === null || $cantidad > $habitacion->plazas) {
                throw ValidationException::withMessages([
                    'pasajeros' => 'La cantidad de pasajeros supera las plazas de la habitación o su capacidad no está definida.',
                ]);
            }

            $ahora = now();
            $asignaciones = [];
            foreach ($datos['pasajeros'] as $asignacion) {
                Pasajero::whereKey($asignacion['id'])->update(['usaComanda' => $asignacion['comanda'] === 'SI']);
                $asignaciones[] = [
                    'habitacion_id' => $habitacion->id,
                    'pasajero_id' => $asignacion['id'],
                    'reserva_id' => $reserva->id,
                    'estado' => $reserva->estado,
                    'checkin' => $reserva->fecha_inicio,
                    'checkout' => $reserva->fecha_fin,
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            }
            DB::table('asignadas')->insert($asignaciones);
        });

        return redirect()->route('calendarios.asignadas')
            ->with('success', 'Pasajeros asignados correctamente.');
    }

    private function cuentaParaPlazas(Pasajero $pasajero): bool
    {
        if (! $pasajero->fechaNacimiento) {
            return true;
        }

        $nacimiento = Carbon::parse($pasajero->fechaNacimiento)->startOfDay();
        return $nacimiento->isFuture() || $nacimiento->diffInYears(today()) >= 5;
    }

    public function eliminarAsignada(Asignada $asignada)
    {
        $asignada->delete();

        return redirect()->route('calendarios.asignadas')
            ->with('success', 'Pasajero eliminado de la habitación.');
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
            $cantidad = $pasajeros->filter(fn ($pasajero) => $this->cuentaParaPlazas($pasajero))->count();

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
            $primerPasajero = $pasajeros[$primerPasajeroId];
            $detalle = $primerPasajero->apelyNombre . ' - ' . $primerPasajero->documento;

            Calendario::whereKey($eventos->modelKeys())->update([
                'description' => $descripcion,
                'estado' => 'Reservada',
                'color' => '#D2E014',
                'habitacion_id' => $habitacion->id,
                'pasajero_id' => $primerPasajeroId,
            ]);

            $reserva = Reserva::create([
                'habitacion_id' => $habitacion->id,
                'pasajero_id' => $primerPasajeroId,
                'fecha_inicio' => $datos['fecha_inicio'],
                'fecha_fin' => $datos['fecha_fin'],
                'detalle' => $detalle,
                'estado' => 'Reservada',
            ]);

            $ahora = now();
            $asignaciones = collect($datos['pasajeros'])->map(function ($asignacion) use ($datos, $habitacion, $reserva, $ahora) {
                return [
                    'habitacion_id' => $habitacion->id,
                    'pasajero_id' => $asignacion['id'],
                    'reserva_id' => $reserva->id,
                    'estado' => $reserva->estado,
                    'checkin' => $datos['fecha_inicio'],
                    'checkout' => $datos['fecha_fin'],
                    'created_at' => $ahora,
                    'updated_at' => $ahora,
                ];
            })->all();

            DB::table('asignadas')->insert($asignaciones);
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
            'descripcion' => 'required_if:estado,No Disponible|nullable|string',
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
                    'description' => $datos['estado'] === 'No Disponible' ? $datos['descripcion'] : '',
                    'start' => $fecha->format('Y-m-d 00:00:00'),
                    'end' => $fecha->format('Y-m-d 23:59:59'),
                    'color' => $datos['estado'] === 'No Disponible' ? Calendario::COLOR_NO_DISPONIBLE : '#0CB371',
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
