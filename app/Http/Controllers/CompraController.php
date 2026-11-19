<?php

namespace App\Http\Controllers;

use App\Models\Articulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CompraController extends Controller
{
    public function store(Request $request)
    {
        $datos = $request->validate([
            'proveedor_id' => ['required', 'integer', Rule::exists('proveedores', 'id')->where('activo', true)],
            'fechaCompra' => 'required|date_format:Y-m-d',
            'tipoCpbte' => 'required|in:Factura A,Factura B,Factura C,Recibo',
            'prefijo' => ['required', 'numeric', 'min:0', 'max:2147483647', 'regex:/\A\d{1,10}\z/'],
            'subfijo' => ['required', 'numeric', 'min:0', 'max:2147483647', 'regex:/\A\d{1,10}\z/'],
            'dias' => 'exclude_if:estado,Contado|required|integer|min:0|max:2147483647',
            'estado' => 'required|in:Pendiente,Contado',
            'observacion' => 'nullable|string|max:255',
            'detalles_json' => 'required|json',
        ]);

        $detalles = json_decode($datos['detalles_json'], true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($detalles) || !array_is_list($detalles)) {
            throw ValidationException::withMessages(['detalles_json' => 'La lista de artículos no es válida.']);
        }

        $detalles = Validator::make(['detalles' => $detalles], [
            'detalles' => 'required|array|min:1',
            'detalles.*' => 'required|array:articulo_id,precio,cantidad,vence',
            'detalles.*.articulo_id' => 'required|integer|exists:articulos,id',
            'detalles.*.precio' => ['required', 'numeric', 'max:99999999.99', 'regex:/\A\d{1,10}(?:\.\d{1,2})?\z/'],
            'detalles.*.cantidad' => 'required|integer|min:1|max:999999999',
            'detalles.*.vence' => 'nullable|date_format:Y-m-d',
        ], [
            'detalles.*.precio.max' => 'El precio unitario no puede superar 99.999.999,99.',
        ])->validate()['detalles'];

        $total = 0;
        foreach ($detalles as &$detalle) {
            [$entero, $decimal] = array_pad(explode('.', (string) $detalle['precio'], 2), 2, '');
            $centavos = (int) $entero * 100 + (int) str_pad($decimal, 2, '0');
            $cantidad = (int) $detalle['cantidad'];
            if ($centavos > intdiv(999999999999999 - $total, $cantidad)) {
                throw ValidationException::withMessages(['detalles_json' => 'El total de la compra supera el máximo permitido.']);
            }
            $subtotal = $centavos * $cantidad;
            $total += $subtotal;
            $detalle['subtotal_calculado'] = $this->formatearCentavos($subtotal);
        }
        unset($detalle);

        DB::transaction(function () use ($request, $datos, $detalles, $total) {
            $articulos = array_unique(array_column($detalles, 'articulo_id'));
            sort($articulos, SORT_NUMERIC);
            foreach ($articulos as $id) {
                Articulo::whereKey($id)->lockForUpdate()->firstOrFail();
            }

            $compraId = DB::table('compras')->insertGetId([
                'proveedor_id' => $datos['proveedor_id'],
                'fechaCpra' => $datos['fechaCompra'],
                'tipoCpbte' => $datos['tipoCpbte'],
                'prefijo' => $datos['prefijo'],
                'subfijo' => $datos['subfijo'],
                'dias' => $datos['estado'] === 'Contado' ? 0 : $datos['dias'],
                'total' => $this->formatearCentavos($total),
                'observacion' => $datos['observacion'] ?? null,
                'estado' => $datos['estado'],
                'usuario_id' => $request->user()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($detalles as $detalle) {
                $stock = DB::table('stocks')->where('articulo_id', $detalle['articulo_id'])
                    ->where('punto', 'Deposito')->orderBy('id')->lockForUpdate()->first();
                if ($stock) {
                    $cantidadStock = (string) $stock->cantidad;
                    $signo = str_starts_with($cantidadStock, '-') ? -1 : 1;
                    [$entero, $decimal] = array_pad(explode('.', ltrim($cantidadStock, '-'), 2), 2, '');
                    $actual = $signo * ((int) $entero * 1000 + (int) str_pad($decimal, 3, '0'));
                    $nuevaCantidad = $actual + (int) $detalle['cantidad'] * 1000;
                    if ($nuevaCantidad > 999999999999 || $nuevaCantidad < -999999999999) {
                        throw ValidationException::withMessages(['detalles_json' => 'La cantidad supera el máximo de stock permitido.']);
                    }
                    $cantidadAbsoluta = abs($nuevaCantidad);
                    DB::table('stocks')->where('id', $stock->id)->update([
                        'cantidad' => ($nuevaCantidad < 0 ? '-' : '').intdiv($cantidadAbsoluta, 1000).'.'.str_pad((string) ($cantidadAbsoluta % 1000), 3, '0', STR_PAD_LEFT),
                        'precioCto' => $detalle['precio'],
                        'updated_at' => now(),
                    ]);
                } else {
                    $primerStock = DB::table('stocks')->where('articulo_id', $detalle['articulo_id'])
                        ->orderBy('id')->first(['precioVta']);
                    DB::table('stocks')->insert([
                        'articulo_id' => $detalle['articulo_id'],
                        'punto' => 'Deposito',
                        'cantidad' => $detalle['cantidad'],
                        'precioCto' => $detalle['precio'],
                        'precioVta' => $primerStock ? $primerStock->precioVta : 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                DB::table('detallecpras')->insert([
                    'compra_id' => $compraId,
                    'articulo_id' => $detalle['articulo_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio' => $detalle['precio'],
                    'subtotal' => $detalle['subtotal_calculado'],
                    'vence' => ($detalle['vence'] ?? null) ?: null,
                    'punto' => 'Deposito',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('movimientos')->insert([
                    'articulo_id' => $detalle['articulo_id'],
                    'user_id' => $request->user()->id,
                    'tipo' => 'Compra',
                    'cantidad' => $detalle['cantidad'],
                    'precio' => $detalle['precio'],
                    'fecha' => now()->toDateString(),
                ]);
            }
        });

        return redirect()->route('compras.create')
            ->with('mensaje', 'Compra guardada correctamente.')
            ->with('icono', 'success');
    }

    private function formatearCentavos(int $centavos): string
    {
        return intdiv($centavos, 100).'.'.str_pad((string) ($centavos % 100), 2, '0', STR_PAD_LEFT);
    }
}
