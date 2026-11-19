<?php

namespace Tests\Feature;

use App\Models\User;
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

    public function test_guarda_compra_y_detalles_y_actualiza_solo_el_primer_stock_en_deposito(): void
    {
        [$proveedor, $articulos] = $this->datos();
        $primero = DB::table('stocks')->insertGetId([
            'articulo_id' => $articulos[0], 'punto' => 'Depósito', 'cantidad' => 5.125,
            'precioCto' => 1, 'precioVta' => 20, 'vence' => '2027-01-01', 'observacion' => 'Conservar',
        ]);
        $segundo = DB::table('stocks')->insertGetId([
            'articulo_id' => $articulos[0], 'punto' => 'Depósito', 'cantidad' => 7, 'precioVta' => 20,
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
            'cantidad' => 2, 'subtotal' => 5.10, 'vence' => '2027-03-01', 'punto' => 'Depósito',
        ]);
        $this->assertDatabaseHas('detallecpras', [
            'compra_id' => $compra->id, 'articulo_id' => $articulos[1], 'precio' => 0.29,
            'cantidad' => 4, 'subtotal' => 1.16, 'vence' => '2027-05-01', 'punto' => 'Depósito',
        ]);
        $this->assertDatabaseHas('stocks', [
            'id' => $primero, 'cantidad' => 8.125, 'precioCto' => 3.10, 'precioVta' => 20,
            'vence' => '2027-01-01', 'observacion' => 'Conservar',
        ]);
        $this->assertDatabaseHas('stocks', ['id' => $segundo, 'cantidad' => 7, 'precioVta' => 20]);
        $this->assertDatabaseHas('stocks', ['id' => $bar, 'cantidad' => 8, 'precioVta' => 30]);
        $this->assertDatabaseHas('stocks', [
            'articulo_id' => $articulos[1], 'punto' => 'Depósito',
            'cantidad' => 4, 'precioCto' => 0.29, 'precioVta' => 30, 'vence' => null,
        ]);
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
        $excedido = [['articulo_id' => $articulos[0], 'precio' => '9999999999.99', 'cantidad' => 1001, 'vence' => '2027-01-01']];
        $this->post(route('compras.store'), $this->encabezado($proveedor, $excedido))
            ->assertSessionHasErrors('detalles_json');
        DB::table('proveedores')->where('id', $proveedor)->update(['activo' => false]);
        $detalle = [['articulo_id' => $articulos[0], 'precio' => '1', 'cantidad' => 1, 'vence' => '2027-01-01']];
        $this->post(route('compras.store'), $this->encabezado($proveedor, $detalle))
            ->assertSessionHasErrors('proveedor_id');
        $this->assertSame(0, DB::table('compras')->count());
        $this->assertSame(0, DB::table('detallecpras')->count());
        $this->assertSame(0, DB::table('stocks')->count());
    }

    public function test_crea_stock_si_no_existe_y_acepta_precio_cero(): void
    {
        [$proveedor, $articulos] = $this->datos();
        $detalles = [['articulo_id' => $articulos[0], 'precio' => '0.00', 'cantidad' => 1, 'vence' => '2027-01-01']];

        $this->actingAs(User::factory()->create())
            ->post(route('compras.store'), $this->encabezado($proveedor, $detalles))
            ->assertRedirect(route('compras.create'))->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stocks', [
            'articulo_id' => $articulos[0], 'punto' => 'Depósito', 'cantidad' => 1, 'precioCto' => 0, 'precioVta' => 0,
        ]);
        $this->assertDatabaseHas('compras', ['proveedor_id' => $proveedor, 'total' => 0]);
        $this->assertDatabaseHas('detallecpras', ['articulo_id' => $articulos[0], 'subtotal' => 0]);
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
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[0], 'punto' => 'Depósito', 'cantidad' => 3]);
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
            'articulo_id' => $articulos[0], 'punto' => 'Depósito', 'cantidad' => 2, 'precioCto' => 5,
        ]);
        $segundo = DB::table('stocks')->insertGetId([
            'articulo_id' => $articulos[1], 'punto' => 'Depósito', 'cantidad' => 999999999.5, 'precioCto' => 6,
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
        $this->assertDatabaseHas('stocks', ['id' => $primero, 'cantidad' => 2, 'precioCto' => 5]);
        $this->assertDatabaseHas('stocks', ['id' => $segundo, 'cantidad' => 999999999.5, 'precioCto' => 6]);
    }
}
