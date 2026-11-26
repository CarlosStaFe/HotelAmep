<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use App\Models\Habitacion;
use App\Models\Reserva;
use App\Models\Subrubro;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ArticuloController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articulos = Articulo::with(['subrubro.rubro', 'primerStock'])
            ->conStocksPorPunto()
            ->get();
        return view('admin.articulos.index', compact('articulos'));
    }

    public function listarAjustes()
    {
        $ajustes = DB::table('movimientos')
            ->join('articulos', 'movimientos.articulo_id', '=', 'articulos.id')
            ->join('users', 'movimientos.user_id', '=', 'users.id')
            ->where('movimientos.tipo', 'Ajuste')
            ->select('movimientos.id', 'movimientos.fecha', 'movimientos.cantidad', 'movimientos.precio',
                'movimientos.observacion', 'articulos.nombre as articulo', 'users.name as usuario')
            ->orderByDesc('movimientos.fecha')
            ->orderByDesc('movimientos.id')
            ->get();

        return view('admin.articulos.listajustes', compact('ajustes'));
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

        return redirect()->route($request->user()->canAccessMenu('articulos.index') ? 'articulos.index' : 'articulos.create')
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
        $stocksPorPunto = $this->stocksPorPunto($articulo);

        return view('admin.articulos.ajuste', compact('articulo', 'stocksPorPunto'));
    }

    public function mover($id)
    {
        $articulo = Articulo::with('subrubro.rubro')->findOrFail($id);
        $stocksPorPunto = $this->stocksPorPunto($articulo);

        return view('admin.articulos.mover', compact('articulo', 'stocksPorPunto'));
    }

    public function grabarMovimiento(Request $request, $id)
    {
        $datos = $request->validate([
            'cantidad' => 'required|integer|between:1,999999999',
            'punto' => 'required|in:Deposito,Comedor,Bar,Recepción',
            'punto_hasta' => 'required|in:Deposito,Comedor,Bar,Recepción|different:punto',
        ], [
            'punto_hasta.different' => 'El punto de destino debe ser distinto del punto de origen.',
        ]);

        DB::transaction(function () use ($id, $datos, $request) {
            Articulo::whereKey($id)->lockForUpdate()->firstOrFail();
            $stocks = [];
            foreach ([$datos['punto'], $datos['punto_hasta']] as $punto) {
                $stocks[$punto] = DB::table('stocks')->where('articulo_id', $id)->where('punto', $punto)
                    ->orderBy('id')->lockForUpdate()->first();
            }
            $origen = $stocks[$datos['punto']];
            $destino = $stocks[$datos['punto_hasta']];
            $cantidad = (int) $datos['cantidad'] * 1000;
            $stockOrigen = $origen ? (int) round((float) $origen->cantidad * 1000) : 0;
            $stockDestino = $destino ? (int) round((float) $destino->cantidad * 1000) : 0;

            if ($cantidad > $stockOrigen) {
                throw ValidationException::withMessages(['cantidad' => 'No hay stock suficiente en el punto de origen.']);
            }
            if ($stockDestino + $cantidad > 999999999999) {
                throw ValidationException::withMessages(['cantidad' => 'La cantidad supera el máximo de stock permitido en el destino.']);
            }
            $formatear = function (int $valor): string {
                $absoluto = abs($valor);

                return ($valor < 0 ? '-' : '').intdiv($absoluto, 1000).'.'.str_pad((string) ($absoluto % 1000), 3, '0', STR_PAD_LEFT);
            };
            $fecha = now();
            DB::table('stocks')->where('id', $origen->id)->update([
                'cantidad' => $formatear($stockOrigen - $cantidad), 'updated_at' => $fecha,
            ]);
            if ($destino) {
                DB::table('stocks')->where('id', $destino->id)->update([
                    'cantidad' => $formatear($stockDestino + $cantidad), 'updated_at' => $fecha,
                ]);
            } else {
                $primerStock = DB::table('stocks')->where('articulo_id', $id)->orderBy('id')->first(['precioVta']);
                DB::table('stocks')->insert([
                    'articulo_id' => $id, 'punto' => $datos['punto_hasta'],
                    'cantidad' => $formatear($cantidad), 'precioVta' => $primerStock->precioVta,
                    'created_at' => $fecha, 'updated_at' => $fecha,
                ]);
            }
            DB::table('movimientos')->insert([
                'articulo_id' => $id,
                'user_id' => $request->user()->id,
                'tipo' => 'Movimiento',
                'cantidad' => $datos['cantidad'],
                'precio' => 0,
                'observacion' => 'Desde '.$datos['punto'].' hasta '.$datos['punto_hasta'],
                'fecha' => $fecha->toDateString(),
            ]);
        });

        return redirect()->route('articulos.index')
            ->with('mensaje', 'Artículo movido exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }

    private function stocksPorPunto(Articulo $articulo): array
    {
        $stocksPorPunto = [];
        foreach (['Deposito', 'Comedor', 'Bar', 'Recepción', 'Producción'] as $punto) {
            $stocksPorPunto[$punto] = (float) (DB::table('stocks')
                ->where('articulo_id', $articulo->id)
                ->where('punto', $punto)
                ->orderBy('id')
                ->value('cantidad') ?? 0);
        }

        return $stocksPorPunto;
    }

    public function precio($id)
    {
        $articulo = Articulo::with(['subrubro', 'primerStock'])->findOrFail($id);

        return view('admin.articulos.precio', compact('articulo'));
    }

    public function grabarPrecio(Request $request, $id)
    {
        $datos = $request->validate([
            'precio' => 'required|numeric|gt:0|decimal:0,2|max:9999999999.99',
        ]);

        DB::transaction(function () use ($id, $datos) {
            $articulo = Articulo::whereKey($id)->lockForUpdate()->firstOrFail();
            $stocks = DB::table('stocks')->where('articulo_id', $articulo->id);

            if ($stocks->exists()) {
                $stocks->update(['precioVta' => $datos['precio'], 'updated_at' => now()]);
            } else {
                DB::table('stocks')->insert([
                    'articulo_id' => $articulo->id,
                    'precioVta' => $datos['precio'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('articulos.index')
            ->with('mensaje', 'Precio actualizado exitosamente.')
            ->with('icono', 'success')
            ->with('showConfirmButton', false)
            ->with('textoBoton', '');
    }

    /**
     * Grabar the specified resource in storage.
     */
    public function grabar(Request $request, $id)
    {
        $datos = $request->validate([
            'tipo' => 'required|in:Entrada,Salida',
            'cantidad' => 'required|numeric|gt:0|decimal:0,3|max:999999999.999',
            'punto' => 'required|in:Deposito,Comedor,Bar,Recepción,Producción',
            'motivo' => 'required|in:Ajuste,Rotura,Perdida,Vencido,Desgaste,Baja,Producción',
            'observacion' => 'required|string|max:255',
        ]);

        $observacion = $datos['tipo'].' - '.$datos['motivo'].' - '.$datos['observacion'];
        if (mb_strlen($observacion) > 100) {
            throw ValidationException::withMessages([
                'observacion' => 'El tipo, el motivo y la observación no pueden superar 100 caracteres en total.',
            ]);
        }

        DB::transaction(function () use ($datos, $id, $observacion) {
            Articulo::whereKey($id)->lockForUpdate()->firstOrFail();
            $stock = DB::table('stocks')
                ->where('articulo_id', $id)
                ->where('punto', $datos['punto'])
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            $cantidadActual = $stock ? (float) $stock->cantidad : 0;
            $cantidad = (float) $datos['cantidad'];

            if ($datos['tipo'] === 'Salida' && $cantidad > $cantidadActual) {
                throw ValidationException::withMessages([
                    'cantidad' => 'No hay stock suficiente para registrar este egreso.',
                ]);
            }

            $nuevoStock = $datos['tipo'] === 'Entrada'
                ? $cantidadActual + $cantidad
                : $cantidadActual - $cantidad;

            if ($nuevoStock > 999999999.999 || $nuevoStock < -999999999.999) {
                throw ValidationException::withMessages([
                    'cantidad' => 'La cantidad supera el máximo de stock permitido.',
                ]);
            }

            if ($stock) {
                DB::table('stocks')->where('id', $stock->id)
                    ->update(['cantidad' => $nuevoStock, 'updated_at' => now()]);
            } else {
                $precioExistente = DB::table('stocks')
                    ->where('articulo_id', $id)
                    ->orderBy('id')
                    ->first(['precioVta']);
                $nuevoRegistro = [
                    'articulo_id' => $id,
                    'punto' => $datos['punto'],
                    'cantidad' => $nuevoStock,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                if ($precioExistente) {
                    $nuevoRegistro['precioVta'] = $precioExistente->precioVta;
                }
                DB::table('stocks')->insert($nuevoRegistro);
            }

            DB::table('movimientos')->insert([
                'articulo_id' => $id,
                'user_id' => Auth::id(),
                'tipo' => 'Ajuste',
                'cantidad' => $cantidad,
                'precio' => 0,
                'observacion' => $observacion,
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
            ->whereIn('id', Reserva::query()->select('habitacion_id')->where('estado', 'Ocupada'))
            ->orderBy('numero')
            ->get();

        return view('admin.comandas.comanda', compact('habitaciones'));
    }

    /**
     * Cargar los articulos habilitados para las comandas.
     */
    public function cargarArticulos(): JsonResponse
    {
        $articulos = Articulo::with(['subrubro', 'primerStock'])
            ->conStocksPorPunto()
            ->where('articulos.activo', true)
            ->where('articulos.comanda', true)
            ->orderBy('articulos.nombre')
            ->get()
            ->map(fn (Articulo $articulo) => [
                'id' => $articulo->id,
                'subrubro' => $articulo->subrubro?->nombre ?? 'Sin subrubro',
                'nombre' => $articulo->nombre,
                'stock_deposito' => $articulo->stock_deposito ?? 0,
                'stock_comedor' => $articulo->stock_comedor ?? 0,
                'stock_bar' => $articulo->stock_bar ?? 0,
                'stock_recepcion' => $articulo->stock_recepcion ?? 0,
                'precio' => $articulo->primerStock?->precioVta ?? 'N/A',
            ]);

        return response()->json(['data' => $articulos]);
    }

}
