<?php

namespace App\Http\Controllers;

use App\Models\Habitacion;
use App\Models\Pasajero;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PasajeroController extends Controller
{
    public function getByHabitacion(string $habitacion): JsonResponse
    {
        if (!Schema::hasTable('pasajeros') || !Schema::hasTable('habitaciones')) {
            return response()->json([]);
        }

        if (!Schema::hasColumn('pasajeros', 'habitacion_id')) {
            return response()->json([]);
        }

        $habitacionModel = Habitacion::where('numero', $habitacion)->first();

        if (!$habitacionModel) {
            return response()->json([]);
        }

        $query = Pasajero::query()->where('habitacion_id', $habitacionModel->id);

        if (Schema::hasColumn('pasajeros', 'apelynombre')) {
            $query->orderBy('apelynombre');
        }

        $pasajeros = $query->get();

        $data = $pasajeros->map(function ($pasajero) {
            $nombre = '';

            if (isset($pasajero->apelynombre) && trim((string) $pasajero->apelynombre) !== '') {
                $nombre = (string) $pasajero->apelynombre;
            } elseif (isset($pasajero->nombre) && trim((string) $pasajero->nombre) !== '') {
                $nombre = (string) $pasajero->nombre;
            } elseif (isset($pasajero->apellido) || isset($pasajero->nombres)) {
                $nombre = trim(((string) ($pasajero->apellido ?? '')) . ' ' . ((string) ($pasajero->nombres ?? '')));
            }

            if ($nombre === '') {
                $nombre = 'Pasajero #' . $pasajero->id;
            }

            return [
                'id' => $pasajero->id,
                'nombre' => $nombre,
            ];
        })->values();

        return response()->json($data);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pasajeros = Pasajero::all();
        return view('admin.pasajeros.index', compact('pasajeros'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.pasajeros.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_documento' => 'required|in:DNI,CI,PAS,LE',
            'documento' => 'required|string|max:8',
            'apelynombre' => 'required|string|max:100',
            'telefono' => 'required|string|max:50',
            'domicilio' => 'required|string|max:100',
            'cod_postal_id' => 'required|exists:localidades,id',
            'email' => 'required|email|max:100',
        ]);

        Pasajero::create([
            'tipo_documento' => $validated['tipo_documento'],
            'documento' => $validated['documento'],
            'apelynombre' => $validated['apelynombre'],
            'telefono' => $validated['telefono'],
            'email' => $validated['email'],
            'direccion' => $validated['domicilio'],
            'cod_postal_id' => $validated['cod_postal_id'],
            'fecha_creacion' => now(),
            'fecha_actualizacion' => now(),
        ]);

        return redirect()->route('pasajeros.index')
            ->with('mensaje', 'Pasajero creado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $pasajero = Pasajero::with('localidad')->findOrFail($id);
        return view('admin.pasajeros.show', compact('pasajero'));

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pasajero $pasajero)
    {
        $pasajero = Pasajero::with('localidad')->findOrFail($pasajero->id);
        return view('admin.pasajeros.edit', compact('pasajero'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pasajero $pasajero)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pasajero $pasajero)
    {
        //
    }
}
