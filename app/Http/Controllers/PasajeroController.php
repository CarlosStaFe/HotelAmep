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
    public function show(Pasajero $pasajero)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pasajero $pasajero)
    {
        //
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
