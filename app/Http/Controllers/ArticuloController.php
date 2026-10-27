<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Habitacion;
use App\Models\Subrubro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ArticuloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articulos = Articulo::with('subrubro.rubro')->get();
        return view('admin.articulos.index', compact('articulos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subrubros = Subrubro::orderBy('nombre')->get();
        return view('admin.articulos.create', compact('subrubros'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'subrubro_id' => 'required|exists:subrubros,id',
            'nombre' => 'required|string|max:255',
            'comanda' => 'required|boolean',
            'activo' => 'required|boolean',
            'vencimiento' => 'required|boolean',
            'observacion' => ['nullable', 'string', 'max:100', 'sometimes'],
        ]);

        $articulo = new Articulo();
        $articulo->subrubro_id = $request->input('subrubro_id');
        $articulo->nombre = $request->input('nombre');
        $articulo->comanda = $request->boolean('comanda');
        $articulo->activo = $request->boolean('activo');
        $articulo->vencimiento = $request->boolean('vencimiento');
        $articulo->observacion = $request->input('observacion');
        $articulo->fecha_creacion = now();
        $articulo->fecha_actualizacion = now();
        $articulo->save();

        return redirect()->route('articulos.index')
            ->with('mensaje', 'Artículo creado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $articulo = Articulo::with('subrubro.rubro')->findOrFail($id);
        return view('admin.articulos.show', compact('articulo'));
    }
     
    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $articulo = Articulo::findOrFail($id);
        $subrubros = Subrubro::orderBy('nombre')->get();

        return view('admin.articulos.edit', compact('articulo', 'subrubros'));
    }

    /**
     * Show delete confirmation form for the specified resource.
     */
    public function delete($id)
    {
        $articulo = Articulo::with('subrubro.rubro')->findOrFail($id);
        return view('admin.articulos.delete', compact('articulo'));
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'subrubro_id' => 'required|exists:subrubros,id',
            'nombre' => 'required|string|max:255',
            'comanda' => 'required|boolean',
            'activo' => 'required|boolean',
            'vencimiento' => 'required|boolean',
            'observacion' => ['nullable', 'string', 'max:100', 'sometimes'],
        ]);

        $articulo = Articulo::findOrFail($id);
        $articulo->subrubro_id = $request->input('subrubro_id');
        $articulo->nombre = $request->input('nombre');
        $articulo->comanda = $request->boolean('comanda');
        $articulo->activo = $request->boolean('activo');
        $articulo->vencimiento = $request->boolean('vencimiento');
        $articulo->observacion = $request->input('observacion');
        $articulo->fecha_actualizacion = now();
        $articulo->save();

        return redirect()->route('articulos.index')
            ->with('mensaje', 'Artículo actualizado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $articulo = Articulo::findOrFail($id);
        $articulo->delete();

        return redirect()->route('articulos.index')
            ->with('mensaje', 'Artículo eliminado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }

    /**
     * Ajuste the specified resource in storage.
     */
    public function ajuste($id)
    {
        $articulo = Articulo::with('subrubro.rubro')->findOrFail($id);
        return view('admin.articulos.ajuste', compact('articulo'));
    }

    /**
     * Grabar the specified resource in storage.
     */
    public function grabar(Request $request, $id)
    {
        $datos = $request->validate([
            'tipo' => 'required|in:Entrada,Salida',
            'cantidad' => 'required|numeric|gt:0',
            'motivo' => 'required|in:Ajuste,Rotura,Perdida,Vencido,Desgaste,Baja',
            'observacion' => 'required|string|max:255',
        ]);

        Articulo::findOrFail($id);

        DB::transaction(function () use ($datos, $id) {
            $stock = DB::table('stocks')
                ->where('articulo_id', $id)
                ->lockForUpdate()
                ->first();

            if (!$stock) {
                DB::table('stocks')->insert([
                    'articulo_id' => $id,
                    'cantidad' => 0,
                ]);
                $cantidadActual = 0;
            } else {
                $cantidadActual = (float) $stock->cantidad;
            }

            $cantidad = (float) $datos['cantidad'];

            if ($datos['tipo'] === 'Salida' && $cantidad > $cantidadActual) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'cantidad' => 'No hay stock suficiente para registrar este egreso.',
                ]);
            }

            $nuevoStock = $datos['tipo'] === 'Entrada'
                ? $cantidadActual + $cantidad
                : $cantidadActual - $cantidad;

            DB::table('stocks')
                ->where('articulo_id', $id)
                ->update(['cantidad' => $nuevoStock]);

            DB::table('movimientos')->insert([
                'articulo_id' => $id,
                'user_id' => Auth::id(),
                'tipo' => $datos['tipo'],
                'cantidad' => $cantidad,
                'motivo' => $datos['motivo'],
                'observacion' => $datos['observacion'],
                'fecha' => now(),
            ]);
        });

        return redirect()->route('articulos.index')
            ->with('mensaje', 'Artículo ajustado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }

    /**
     * LLama a la función de carga de comandas.
     */
    public function comanda()
    {
        $habitaciones = Habitacion::query()
            ->where('estado', 'Ocupada')
            ->orderBy('numero')
            ->get();

        return view('admin.comandas.comanda', compact('habitaciones'));
    }

    /**
     * Cargar los articulos habilitados para las comandas.
     */
    public function cargarArticulos(): JsonResponse
    {
        $articulos = Articulo::with('subrubro')
            ->where('articulos.activo', true)
            ->where('articulos.comanda', true)
            ->leftJoin('stocks', 'articulos.id', '=', 'stocks.articulo_id')
            ->select(
                'articulos.*',
                'stocks.cantidad as stock',
                'stocks.precioVta as precio'
            )
            ->orderBy('articulos.nombre')
            ->get()
            ->map(fn (Articulo $articulo) => [
                'id' => $articulo->id,
                'subrubro' => $articulo->subrubro?->nombre ?? 'Sin subrubro',
                'nombre' => $articulo->nombre,
                'stock' => $articulo->stock ?? 'N/A',
                'precio' => $articulo->precio ?? 'N/A',
            ]);

        return response()->json(['data' => $articulos]);
    }

}
