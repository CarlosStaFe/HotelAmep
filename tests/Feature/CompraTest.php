<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CompraTest extends TestCase
{
    use RefreshDatabase;

    private function datos(): array
    {
        $now = now();
        $proveedor = DB::table('proveedores')->insertGetId([
            'nombre' => 'Proveedor', 'razonSocial' => 'Proveedor SA', 'cuit' => '20123456789',
            'tipoIva' => 'Exento', 'activo' => true, 'fecha_creacion' => $now, 'fecha_actualizacion' => $now,
        ]);
        $rubro = DB::table('rubros')->insertGetId([
            'nombre' => 'Rubro', 'tipo' => 'Gasto', 'fecha_creacion' => $now, 'fecha_actualizacion' => $now,
        ]);
        $subrubro = DB::table('subrubros')->insertGetId([
            'rubro_id' => $rubro, 'nombre' => 'Subrubro', 'fecha_creacion' => $now, 'fecha_actualizacion' => $now,
        ]);
        $articulos = [];
        foreach (['Agua', 'Jugo'] as $nombre) {
            $articulos[] = DB::table('articulos')->insertGetId([
                'subrubro_id' => $subrubro, 'nombre' => $nombre, 'fecha_creacion' => $now, 'fecha_actualizacion' => $now,
            ]);
        }

        return [$proveedor, $articulos];
    }

    private function encabezado(int $proveedor, array $detalles): array
    {
        return [
            'proveedor_id' => $proveedor, 'fechaCompra' => '2026-11-19', 'tipoCpbte' => 'Factura A',
            'prefijo' => '0001', 'subfijo' => '0000023', 'dias' => '30',
            'estado' => 'Pendiente', 'observacion' => 'Prueba',
            'detalles_json' => json_encode($detalles, JSON_THROW_ON_ERROR),
        ];
    }

    public function test_muestra_compras_en_el_listado_segun_sus_encabezados(): void
    {
        [$proveedor] = $this->datos();
        DB::table('compras')->insert([
            'proveedor_id' => $proveedor,
            'fechaCpra' => '2026-11-19',
            'tipoCpbte' => 'Factura A',
            'prefijo' => 1,
            'subfijo' => 23,
            'dias' => 30,
            'total' => 9.36,
            'estado' => 'Pendiente',
            'usuario_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs(User::factory()->create())
            ->get(route('compras.index'))
            ->assertOk()
            ->assertSeeText('Fecha')
            ->assertSee('<td style="text-align: right;">19/11/2026</td>', false)
            ->assertSeeText('Nombre')
            ->assertSeeText('Razón Social')
            ->assertSeeText('Factura A')
            ->assertSeeText('PreFijo')
            ->assertSeeText('SubFijo')
            ->assertSee('<td style="text-align: right;">1</td>', false)
            ->assertSee('<td style="text-align: right;">23</td>', false)
            ->assertSeeText('Pendiente')
            ->assertSeeText('Proveedor SA');
    }

    public function test_resalta_compras_solo_cuando_la_fecha_de_vencimiento_ya_paso(): void
    {
        $this->travelTo(Carbon::parse('2026-11-23 15:00:00'));
        [$proveedor] = $this->datos();
        $base = [
            'proveedor_id' => $proveedor,
            'tipoCpbte' => 'Factura A',
            'prefijo' => 1,
            'dias' => 3,
            'total' => 10,
            'estado' => 'Pendiente',
            'usuario_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $esperadas = [];
        foreach ([
            [['fechaCpra' => '2026-11-19', 'subfijo' => 1], true],
            [['fechaCpra' => '2026-11-20', 'subfijo' => 2], false],
            [['fechaCpra' => '2026-11-21', 'subfijo' => 3], false],
            [['fechaCpra' => '2026-11-22', 'subfijo' => 4, 'dias' => 0, 'estado' => 'Contado'], false],
            [['fechaCpra' => '2026-11-23', 'subfijo' => 5, 'dias' => 0, 'estado' => 'Contado'], false],
        ] as [$datos, $vencida]) {
            $id = DB::table('compras')->insertGetId(array_merge($base, $datos));
            $esperadas[$id] = $vencida;
        }

        $response = $this->actingAs(User::factory()->create())->get(route('compras.index'))->assertOk();
        preg_match_all('/<tr(?: class="([^"]*)")?\s*>\s*<td style="text-align: right;">(\d+)<\/td>/', $response->getContent(), $filas, PREG_SET_ORDER);
        $this->assertCount(5, $filas);
        foreach ($filas as $fila) {
            $this->assertSame($esperadas[(int) $fila[2]], $fila[1] === 'table-danger');
        }
    }

    public function test_guarda_compra_y_detalles_y_actualiza_solo_el_primer_stock_en_deposito(): void
    {
        [$proveedor, $articulos] = $this->datos();
        $primero = DB::table('stocks')->insertGetId([
            'articulo_id' => $articulos[0], 'punto' => 'Deposito', 'cantidad' => 5.125,
            'precioCto' => 1, 'precioVta' => 20, 'vence' => '2027-01-01', 'observacion' => 'Conservar',
        ]);
        $segundo = DB::table('stocks')->insertGetId([
            'articulo_id' => $articulos[0], 'punto' => 'Deposito', 'cantidad' => 7, 'precioVta' => 20,
        ]);
        $bar = DB::table('stocks')->insertGetId([
            'articulo_id' => $articulos[1], 'punto' => 'Bar', 'cantidad' => 8, 'precioVta' => 30,
        ]);
        $detalles = [
            ['articulo_id' => $articulos[0], 'precio' => '2.55', 'cantidad' => '2', 'vence' => '2027-03-01'],
            ['articulo_id' => $articulos[0], 'precio' => '3.10', 'cantidad' => '1', 'vence' => '2027-04-01'],
            ['articulo_id' => $articulos[1], 'precio' => '0.29', 'cantidad' => '4', 'vence' => '2027-05-01'],
        ];
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->post(route('compras.store'), $this->encabezado($proveedor, $detalles) + ['total' => '0.01'])
            ->assertRedirect(route('compras.create'))->assertSessionHasNoErrors();

        $compra = DB::table('compras')->first();
        $this->assertDatabaseHas('compras', [
            'id' => $compra->id, 'proveedor_id' => $proveedor, 'usuario_id' => $usuario->id,
            'fechaCpra' => '2026-11-19', 'prefijo' => 1, 'subfijo' => 23, 'total' => 9.36,
        ]);
        $this->assertSame(3, DB::table('detallecpras')->where('compra_id', $compra->id)->count());
        $this->assertDatabaseHas('detallecpras', [
            'compra_id' => $compra->id, 'articulo_id' => $articulos[0], 'precio' => 2.55,
            'cantidad' => 2, 'subtotal' => 5.10, 'vence' => '2027-03-01', 'punto' => 'Deposito',
        ]);
        $this->assertDatabaseHas('detallecpras', [
            'compra_id' => $compra->id, 'articulo_id' => $articulos[1], 'precio' => 0.29,
            'cantidad' => 4, 'subtotal' => 1.16, 'vence' => '2027-05-01', 'punto' => 'Deposito',
        ]);
        $this->assertDatabaseHas('stocks', [
            'id' => $primero, 'cantidad' => 8.125, 'precioCto' => 3.10, 'precioVta' => 20,
            'vence' => '2027-01-01', 'observacion' => 'Conservar',
        ]);
        $this->assertDatabaseHas('stocks', ['id' => $segundo, 'cantidad' => 7, 'precioVta' => 20]);
        $this->assertDatabaseHas('stocks', ['id' => $bar, 'cantidad' => 8, 'precioVta' => 30]);
        $this->assertDatabaseHas('stocks', [
            'articulo_id' => $articulos[1], 'punto' => 'Deposito',
            'cantidad' => 4, 'precioCto' => 0.29, 'precioVta' => 30, 'vence' => null,
        ]);
        $this->assertSame(3, DB::table('movimientos')->where('tipo', 'Compra')->count());
        foreach ([
            [$articulos[0], 2, 2.55],
            [$articulos[0], 1, 3.10],
            [$articulos[1], 4, 0.29],
        ] as [$articulo, $cantidad, $precio]) {
            $this->assertDatabaseHas('movimientos', [
                'articulo_id' => $articulo, 'user_id' => $usuario->id,
                'tipo' => 'Compra', 'precio' => $precio, 'cantidad' => $cantidad, 'fecha' => now()->toDateString(),
            ]);
        }
    }

    public function test_rechaza_datos_invalidos_sin_guardar_ni_modificar_stock(): void
    {
        [$proveedor, $articulos] = $this->datos();
        $datos = $this->encabezado($proveedor, []);
        $this->post(route('compras.store'), $datos)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create());

        $this->post(route('compras.store'), $datos)->assertSessionHasErrors('detalles');
        $this->post(route('compras.store'), array_merge($datos, ['detalles_json' => '{}']))
            ->assertSessionHasErrors('detalles');
        $this->post(route('compras.store'), array_merge($datos, ['detalles_json' => 'no json']))
            ->assertSessionHasErrors('detalles_json');
        $invalido = [['articulo_id' => 99999, 'precio' => '-1', 'cantidad' => 0, 'vence' => '2026-99-99']];
        $this->post(route('compras.store'), $this->encabezado($proveedor, $invalido))
            ->assertSessionHasErrors(['detalles.0.articulo_id', 'detalles.0.precio', 'detalles.0.cantidad', 'detalles.0.vence']);
        $excedido = [['articulo_id' => $articulos[0], 'precio' => '99999999.99', 'cantidad' => 100001, 'vence' => '2027-01-01']];
        $this->post(route('compras.store'), $this->encabezado($proveedor, $excedido))
            ->assertSessionHasErrors('detalles_json');
        DB::table('proveedores')->where('id', $proveedor)->update(['activo' => false]);
        $detalle = [['articulo_id' => $articulos[0], 'precio' => '1', 'cantidad' => 1, 'vence' => '2027-01-01']];
        $this->post(route('compras.store'), $this->encabezado($proveedor, $detalle))
            ->assertSessionHasErrors('proveedor_id');
        $this->assertSame(0, DB::table('compras')->count());
        $this->assertSame(0, DB::table('detallecpras')->count());
        $this->assertSame(0, DB::table('stocks')->count());
        $this->assertSame(0, DB::table('movimientos')->count());
    }

    public function test_crea_stock_si_no_existe_y_acepta_precio_cero(): void
    {
        [$proveedor, $articulos] = $this->datos();
        $detalles = [['articulo_id' => $articulos[0], 'precio' => '0.00', 'cantidad' => 1, 'vence' => '2027-01-01']];

        $this->actingAs(User::factory()->create())
            ->post(route('compras.store'), $this->encabezado($proveedor, $detalles))
            ->assertRedirect(route('compras.create'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stocks', [
            'articulo_id' => $articulos[0], 'punto' => 'Deposito', 'cantidad' => 1, 'precioCto' => 0, 'precioVta' => 0,
        ]);
        $this->assertDatabaseHas('compras', ['proveedor_id' => $proveedor, 'total' => 0]);
        $this->assertDatabaseHas('detallecpras', ['articulo_id' => $articulos[0], 'subtotal' => 0]);
    }

    public function test_registra_movimiento_con_la_cantidad_maxima_permitida(): void
    {
        [$proveedor, $articulos] = $this->datos();
        $detalles = [['articulo_id' => $articulos[0], 'precio' => '0.00', 'cantidad' => 999999999]];

        $this->actingAs(User::factory()->create())
            ->post(route('compras.store'), $this->encabezado($proveedor, $detalles))
            ->assertRedirect(route('compras.create'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('movimientos', [
            'articulo_id' => $articulos[0], 'tipo' => 'Compra', 'cantidad' => 999999999,
        ]);
    }

    public function test_migracion_de_movimientos_puede_revertirse_sin_relacion_de_compra(): void
    {
        $migracion = require database_path('migrations'.DIRECTORY_SEPARATOR.'2026_11_19_000002_add_compra_to_movimientos_table.php');

        $migracion->down();
        $migracion->up();

        $this->assertFalse(\Illuminate\Support\Facades\Schema::hasColumn('movimientos', 'compra_id'));
    }

    public function test_precio_de_compra_no_puede_superar_el_limite_de_movimientos(): void
    {
        [$proveedor, $articulos] = $this->datos();
        $detalle = ['articulo_id' => $articulos[0], 'precio' => '100000000.00', 'cantidad' => 1];
        $this->actingAs(User::factory()->create());

        $this->post(route('compras.store'), $this->encabezado($proveedor, [$detalle]))
            ->assertSessionHasErrors('detalles.0.precio');
        $this->assertSame(0, DB::table('compras')->count());
        $this->assertSame(0, DB::table('movimientos')->count());

        $detalle['precio'] = '99999999.99';
        $this->post(route('compras.store'), $this->encabezado($proveedor, [$detalle]))
            ->assertRedirect(route('compras.create'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('movimientos', [
            'articulo_id' => $articulos[0], 'tipo' => 'Compra', 'precio' => 99999999.99, 'cantidad' => 1,
        ]);
    }

    public function test_dias_son_obligatorios_para_pendiente_y_cero_para_contado(): void
    {
        [$proveedor, $articulos] = $this->datos();
        $detalles = [['articulo_id' => $articulos[0], 'precio' => '2.50', 'cantidad' => 1]];
        $datos = $this->encabezado($proveedor, $detalles);
        $this->actingAs(User::factory()->create());

        unset($datos['dias']);
        $this->post(route('compras.store'), $datos)->assertSessionHasErrors('dias');
        $this->assertSame(0, DB::table('compras')->count());

        $datos['estado'] = 'Contado';
        $this->post(route('compras.store'), $datos)
            ->assertRedirect(route('compras.create'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('compras', ['proveedor_id' => $proveedor, 'estado' => 'Contado', 'dias' => 0]);

        $datos['dias'] = '30';
        $this->post(route('compras.store'), $datos)
            ->assertRedirect(route('compras.create'))->assertSessionHasNoErrors();
        $this->assertSame(2, DB::table('compras')->where('estado', 'Contado')->where('dias', 0)->count());
    }

    public function test_guarda_articulos_con_y_sin_fecha_de_vencimiento(): void
    {
        [$proveedor, $articulos] = $this->datos();
        $detalles = [
            ['articulo_id' => $articulos[0], 'precio' => '2.50', 'cantidad' => 1, 'vence' => null],
            ['articulo_id' => $articulos[0], 'precio' => '2.50', 'cantidad' => 1, 'vence' => ''],
            ['articulo_id' => $articulos[0], 'precio' => '2.50', 'cantidad' => 1],
            ['articulo_id' => $articulos[1], 'precio' => '3.00', 'cantidad' => 1, 'vence' => '2027-01-01'],
        ];

        $this->actingAs(User::factory()->create())
            ->post(route('compras.store'), $this->encabezado($proveedor, $detalles))
            ->assertRedirect(route('compras.create'))->assertSessionHasNoErrors();

        $this->assertSame(3, DB::table('detallecpras')->where('articulo_id', $articulos[0])->whereNull('vence')->count());
        $this->assertDatabaseHas('detallecpras', ['articulo_id' => $articulos[1], 'vence' => '2027-01-01']);
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[0], 'punto' => 'Deposito', 'cantidad' => 3]);
    }

    public function test_conserva_los_articulos_temporales_si_falla_la_cabecera(): void
    {
        [$proveedor, $articulos] = $this->datos();
        $detalles = [['articulo_id' => $articulos[0], 'precio' => '2.50', 'cantidad' => 2, 'vence' => '2027-01-01']];
        $datos = $this->encabezado($proveedor, $detalles);
        $datos['fechaCompra'] = '';

        $this->actingAs(User::factory()->create())->from(route('compras.create'))
            ->post(route('compras.store'), $datos)
            ->assertRedirect(route('compras.create'))
            ->assertSessionHasErrors('fechaCompra')
            ->assertSessionHasInput('detalles_json', $datos['detalles_json']);

        $this->get(route('compras.create'))->assertOk()
            ->assertSee('value="'.e($datos['detalles_json']).'"', false);
        $this->assertSame(0, DB::table('compras')->count());
    }

    public function test_reversiona_todo_si_el_stock_supera_el_limite(): void
    {
        [$proveedor, $articulos] = $this->datos();
        $primero = DB::table('stocks')->insertGetId([
            'articulo_id' => $articulos[0], 'punto' => 'Deposito', 'cantidad' => 2, 'precioCto' => 5,
        ]);
        $segundo = DB::table('stocks')->insertGetId([
            'articulo_id' => $articulos[1], 'punto' => 'Deposito', 'cantidad' => 999999999.5, 'precioCto' => 6,
        ]);
        $detalles = [
            ['articulo_id' => $articulos[0], 'precio' => '3', 'cantidad' => 1, 'vence' => '2027-01-01'],
            ['articulo_id' => $articulos[1], 'precio' => '3', 'cantidad' => 1, 'vence' => '2027-01-01'],
        ];

        $this->actingAs(User::factory()->create())
            ->post(route('compras.store'), $this->encabezado($proveedor, $detalles))
            ->assertSessionHasErrors('detalles_json');
        $this->assertSame(0, DB::table('compras')->count());
        $this->assertSame(0, DB::table('detallecpras')->count());
        $this->assertSame(0, DB::table('movimientos')->count());
        $this->assertDatabaseHas('stocks', ['id' => $primero, 'cantidad' => 2, 'precioCto' => 5]);
        $this->assertDatabaseHas('stocks', ['id' => $segundo, 'cantidad' => 999999999.5, 'precioCto' => 6]);
    }
}
