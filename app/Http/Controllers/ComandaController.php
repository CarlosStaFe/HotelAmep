<?php

namespace App\Http\Controllers;

use App\Mail\ComandaMail;
use App\Models\Articulo;
use App\Models\Asignada;
use App\Models\Comanda;
use App\Models\Habitacion;
use App\Models\Pasajero;
use App\Models\Reserva;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Mailer\Exception\ExceptionInterface as MailException;

class ComandaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return redirect()->route('comandas.comanda');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function comanda()
    {
        return view('admin.comandas.comanda');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'habitacion' => 'required|integer|exists:habitaciones,id',
            'punto' => 'required|in:Comedor,Bar,Recepcion',
            'detalles' => 'required|array|min:1',
            'detalles.*' => 'required|array:articulo_id,pasajero_id,cantidad,precio,moza',
            'detalles.*.articulo_id' => 'required|integer|exists:articulos,id',
            'detalles.*.pasajero_id' => 'required|integer|exists:pasajeros,id',
            'detalles.*.cantidad' => ['required', 'numeric', 'gt:0', 'regex:/\A\d{1,9}(?:\.\d{1,3})?\z/'],
            'detalles.*.precio' => ['required', 'numeric', 'regex:/\A\d{1,10}(?:\.\d{1,2})?\z/'],
            'detalles.*.moza' => ['required', 'numeric', 'gt:0', 'max:9007199254740991', 'regex:/\A\d{1,16}\z/'],
        ]);

        if (!array_is_list($datos['detalles'])) {
            throw ValidationException::withMessages(['detalles' => 'La lista de consumos no es válida.']);
        }

        $punto = $datos['punto'] === 'Recepcion' ? 'Recepción' : $datos['punto'];
        $fecha = now();
        $mensajes = DB::transaction(function () use ($request, $datos, $punto, $fecha) {
            $habitacion = Habitacion::findOrFail($datos['habitacion']);
            if (!Reserva::where('habitacion_id', $habitacion->id)->where('estado', 'Ocupada')->exists()) {
                throw ValidationException::withMessages(['habitacion' => 'La habitación ya no tiene una reserva ocupada.']);
            }

            $pasajeroIds = array_unique(array_column($datos['detalles'], 'pasajero_id'));
            $asignados = Asignada::where('habitacion_id', $habitacion->id)->where('estado', 'Ocupada')
                ->whereIn('pasajero_id', $pasajeroIds)->distinct()->pluck('pasajero_id');
            if ($asignados->count() !== count($pasajeroIds)) {
                throw ValidationException::withMessages(['detalles' => 'Hay pasajeros que ya no están asignados a esta habitación.']);
            }
            $pasajeros = Pasajero::whereIn('id', $pasajeroIds)->get()->keyBy('id');

            $articuloIds = array_unique(array_column($datos['detalles'], 'articulo_id'));
            sort($articuloIds, SORT_NUMERIC);
            $articulos = Articulo::whereIn('id', $articuloIds)->where('activo', true)->where('comanda', true)
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            if ($articulos->count() !== count($articuloIds)) {
                throw ValidationException::withMessages(['detalles' => 'Hay artículos que ya no están disponibles para comanda.']);
            }

            $necesario = [];
            foreach ($datos['detalles'] as $detalle) {
                $id = $detalle['articulo_id'];
                $cantidad = $this->escalar($detalle['cantidad'], 3);
                $necesario[$id] = ($necesario[$id] ?? 0) + $cantidad;
                if ($necesario[$id] > 999999999999) {
                    throw ValidationException::withMessages(['detalles' => 'La cantidad solicitada supera el stock permitido.']);
                }
            }

            foreach ($articuloIds as $id) {
                $stocks = DB::table('stocks')->where('articulo_id', $id)->where('punto', $punto)
                    ->orderBy('id')->lockForUpdate()->get();
                if ($stocks->count() > 1) {
                    throw ValidationException::withMessages(['detalles' => 'Hay registros de stock duplicados para '.$articulos[$id]->nombre.' en '.$punto.'.']);
                }
                $stock = $stocks->first();
                if (!$stock || $this->escalar($stock->cantidad, 3) < $necesario[$id]) {
                    throw ValidationException::withMessages(['detalles' => 'No hay stock suficiente de '.$articulos[$id]->nombre.' en '.$punto.'.']);
                }
                $primerStock = DB::table('stocks')->where('articulo_id', $id)->orderBy('id')->first(['precioVta']);
                foreach ($datos['detalles'] as $detalle) {
                    if ((int) $detalle['articulo_id'] === (int) $id
                        && $this->escalar($detalle['precio'], 2) !== $this->escalar($primerStock->precioVta, 2)) {
                        throw ValidationException::withMessages(['detalles' => 'El precio de '.$articulos[$id]->nombre.' cambió. Vuelva a cargar el consumo.']);
                    }
                }
                $restante = $this->escalar($stock->cantidad, 3) - $necesario[$id];
                DB::table('stocks')->where('id', $stock->id)->update([
                    'cantidad' => $this->formatear($restante, 3),
                    'updated_at' => $fecha,
                ]);
            }

            $mensajes = [];
            foreach ($datos['detalles'] as $detalle) {
                $centavos = $this->escalar($detalle['precio'], 2);
                $milesimas = $this->escalar($detalle['cantidad'], 3);
                if ($centavos > intdiv(999999999999499, $milesimas)) {
                    throw ValidationException::withMessages(['detalles' => 'El importe de un consumo supera el máximo permitido.']);
                }
                $total = intdiv($centavos * $milesimas + 500, 1000);
                $precio = $this->formatear($centavos, 2);
                $importe = $this->formatear($total, 2);
                DB::table('comandas')->insert([
                    'articulo_id' => $detalle['articulo_id'],
                    'pasajero_id' => $detalle['pasajero_id'],
                    'user_id' => $request->user()->id,
                    'fecha' => $fecha,
                    'puntoVenta' => $punto,
                    'habitacion' => $habitacion->numero,
                    'cantidad' => $detalle['cantidad'],
                    'precio' => $precio,
                    'total' => $importe,
                    'moza' => (string) $detalle['moza'],
                    'estado' => 'Abierta',
                    'fecha_creacion' => $fecha,
                    'fecha_actualizacion' => $fecha,
                ]);
                DB::table('movimientos')->insert([
                    'articulo_id' => $detalle['articulo_id'],
                    'user_id' => $request->user()->id,
                    'tipo' => 'Comanda',
                    'cantidad' => $detalle['cantidad'],
                    'precio' => $precio,
                    'fecha' => $fecha->toDateString(),
                ]);
                $pasajero = $pasajeros[$detalle['pasajero_id']];
                $mensajes[$pasajero->id]['pasajero'] = $pasajero;
                $mensajes[$pasajero->id]['total'] = ($mensajes[$pasajero->id]['total'] ?? 0) + $total;
                $mensajes[$pasajero->id]['consumos'][] = [
                    'articulo' => $articulos[$detalle['articulo_id']]->nombre,
                    'cantidad' => (string) $detalle['cantidad'],
                    'precio' => $precio,
                    'total' => $importe,
                    'moza' => (string) $detalle['moza'],
                ];
            }

            return $mensajes;
        });

        $advertencias = [];
        $correosEnviados = 0;
        foreach ($mensajes as $mensaje) {
            $pasajero = $mensaje['pasajero'];
            if (!$pasajero->usaComanda) {
                $advertencias[] = 'No se envió el correo a '.$pasajero->apelyNombre.': el pasajero no tiene habilitada la comanda.';
                continue;
            }
            if (Validator::make(['email' => $pasajero->email], ['email' => 'required|email'])->fails()) {
                $advertencias[] = 'No se envió el correo a '.$pasajero->apelyNombre.': dirección no válida.';
                continue;
            }
            try {
                Mail::to($pasajero->email)->send(new ComandaMail(
                    $pasajero->apelyNombre, $punto, $fecha->format('d/m/Y H:i'), $mensaje['consumos'],
                    $this->formatear($mensaje['total'], 2)
                ));
                $correosEnviados++;
            } catch (MailException $error) {
                Log::warning('No se pudo enviar el correo de comanda.', [
                    'pasajero_id' => $pasajero->id,
                    'exception' => get_class($error),
                ]);
                $advertencias[] = 'No se pudo enviar el correo a '.$pasajero->apelyNombre.'.';
            }
        }

        return response()->json([
            'mensaje' => 'Comanda guardada correctamente.',
            'correos_enviados' => $correosEnviados,
            'advertencias' => $advertencias,
        ]);
    }

    private function escalar(string|int|float $valor, int $decimales): int
    {
        $valor = (string) $valor;
        $negativo = str_starts_with($valor, '-');
        [$entero, $decimal] = array_pad(explode('.', ltrim($valor, '-'), 2), 2, '');
        $resultado = (int) $entero * (10 ** $decimales) + (int) str_pad($decimal, $decimales, '0');

        return $negativo ? -$resultado : $resultado;
    }

    private function formatear(int $valor, int $decimales): string
    {
        $factor = 10 ** $decimales;

        return intdiv($valor, $factor).'.'.str_pad((string) ($valor % $factor), $decimales, '0', STR_PAD_LEFT);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comanda $comanda)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Comanda $comanda)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Comanda $comanda)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comanda $comanda)
    {
        //
    }

}
