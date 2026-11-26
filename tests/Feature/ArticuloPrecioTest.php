<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ArticuloPrecioTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\WithFullMenuAccess;

    private function crearArticulo(string $nombre): int
    {
        $now = now();
        $rubro = DB::table('rubros')->insertGetId([
            'nombre' => 'Rubro '.$nombre, 'tipo' => 'Gasto',
            'fecha_creacion' => $now, 'fecha_actualizacion' => $now,
        ]);
        $subrubro = DB::table('subrubros')->insertGetId([
            'rubro_id' => $rubro, 'nombre' => 'Subrubro '.$nombre, 'activo' => true,
            'fecha_creacion' => $now, 'fecha_actualizacion' => $now,
        ]);

        return DB::table('articulos')->insertGetId([
            'subrubro_id' => $subrubro, 'nombre' => $nombre, 'activo' => true,
            'comanda' => true, 'vencimiento' => false,
            'fecha_creacion' => $now, 'fecha_actualizacion' => $now,
        ]);
    }

    public function test_listado_de_ajustes_muestra_solo_movimientos_de_ese_tipo(): void
    {
        $articulo = $this->crearArticulo('Agua');
        $usuario = User::factory()->create(['name' => 'Usuario de ajuste']);
        $this->get(route('articulos.listar-ajustes'))->assertRedirect(route('login'));

        DB::table('movimientos')->insert([
            [
                'articulo_id' => $articulo, 'user_id' => $usuario->id, 'tipo' => 'Movimiento',
                'fecha' => '2026-11-19', 'cantidad' => 2, 'precio' => 0,
                'observacion' => 'Desde Bar hasta Comedor',
            ],
            [
                'articulo_id' => $articulo, 'user_id' => $usuario->id, 'tipo' => 'Ajuste',
                'fecha' => '2026-11-18', 'cantidad' => 1.5, 'precio' => 0,
                'observacion' => 'Entrada - Ajuste - Inventario',
            ],
        ]);

        $this->actingAs($usuario)->get(route('articulos.listar-ajustes'))
            ->assertOk()
            ->assertViewIs('admin.articulos.listajustes')
            ->assertViewHas('ajustes', fn ($ajustes) => $ajustes->count() === 1 && $ajustes->first()->articulo === 'Agua')
            ->assertSee('id="example1"', false)
            ->assertSee('<td class="text-right">1</td>', false)
            ->assertSee('<td data-order="2026-11-18">18/11/2026</td>', false)
            ->assertSee('Agua')
            ->assertSee('1.5')
            ->assertSee('Entrada - Ajuste - Inventario')
            ->assertSee('Usuario de ajuste')
            ->assertDontSee('Desde Bar hasta Comedor');
    }

    public function test_listado_muestra_la_suma_del_stock_de_cada_punto(): void
    {
        $articulo = $this->crearArticulo('Agua');
        $otro = $this->crearArticulo('Jugo');
        $this->crearArticulo('Sin stock');
        DB::table('stocks')->insert([
            ['articulo_id' => $articulo, 'punto' => 'Deposito', 'cantidad' => 1.125],
            ['articulo_id' => $articulo, 'punto' => 'Deposito', 'cantidad' => 2.25],
            ['articulo_id' => $articulo, 'punto' => 'Comedor', 'cantidad' => 0],
            ['articulo_id' => $articulo, 'punto' => 'Bar', 'cantidad' => 5],
            ['articulo_id' => $articulo, 'punto' => 'Recepción', 'cantidad' => 6],
            ['articulo_id' => $articulo, 'punto' => null, 'cantidad' => 99],
            ['articulo_id' => $otro, 'punto' => 'Bar', 'cantidad' => 8],
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('articulos.index'))
            ->assertOk();

        $this->assertMatchesRegularExpression('~<td>Agua</td>\s*<td[^>]*>3\.375</td>\s*<td[^>]*>0</td>\s*<td[^>]*>5</td>\s*<td[^>]*>6</td>~', $response->getContent());
        $this->assertMatchesRegularExpression('~<td>Jugo</td>\s*<td[^>]*>0</td>\s*<td[^>]*>0</td>\s*<td[^>]*>8</td>\s*<td[^>]*>0</td>~', $response->getContent());
        $this->assertMatchesRegularExpression('~<td>Sin stock</td>\s*(?:<td[^>]*>0</td>\s*){4}~', $response->getContent());
    }

    public function test_boton_mover_abre_el_formulario_del_articulo_con_stock_por_punto(): void
    {
        $articulo = $this->crearArticulo('Agua');
        DB::table('stocks')->insert(['articulo_id' => $articulo, 'punto' => 'Deposito', 'cantidad' => 3]);
        $this->get(route('articulos.mover', $articulo))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())
            ->get(route('articulos.index'))->assertOk()
            ->assertSee('href="'.route('articulos.mover', $articulo).'"', false);
        $this->get(route('articulos.mover', $articulo))
            ->assertOk()->assertViewIs('admin.articulos.mover')
            ->assertSee('value="Agua"', false)->assertSee('value="Subrubro Agua"', false)
            ->assertViewHas('stocksPorPunto', fn ($stocks) => $stocks['Deposito'] === 3.0 && $stocks['Bar'] === 0.0);
        $this->get(route('articulos.mover', 999999))->assertNotFound();
    }

    public function test_mueve_stock_entre_puntos_y_crea_el_destino_si_no_existe(): void
    {
        $articulo = $this->crearArticulo('Agua');
        $origen = DB::table('stocks')->insertGetId([
            'articulo_id' => $articulo, 'punto' => 'Deposito', 'cantidad' => 5.125, 'precioVta' => 25,
        ]);
        $bar = DB::table('stocks')->insertGetId([
            'articulo_id' => $articulo, 'punto' => 'Bar', 'cantidad' => 2.5, 'precioVta' => 25,
        ]);
        $usuario = User::factory()->create();
        $this->actingAs($usuario);
        $this->post(route('articulos.grabar-movimiento', $articulo), [
            'punto' => 'Deposito', 'punto_hasta' => 'Bar', 'cantidad' => 2,
        ])->assertRedirect(route('articulos.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('stocks', ['id' => $origen, 'cantidad' => 3.125]);
        $this->assertDatabaseHas('stocks', ['id' => $bar, 'cantidad' => 4.5, 'precioVta' => 25]);
        $this->assertDatabaseHas('movimientos', [
            'articulo_id' => $articulo, 'user_id' => $usuario->id, 'tipo' => 'Movimiento',
            'cantidad' => 2, 'precio' => 0, 'observacion' => 'Desde Deposito hasta Bar',
            'fecha' => now()->toDateString(),
        ]);

        $this->post(route('articulos.grabar-movimiento', $articulo), [
            'punto' => 'Bar', 'punto_hasta' => 'Recepción', 'cantidad' => 4,
        ])->assertRedirect(route('articulos.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('stocks', ['id' => $bar, 'cantidad' => 0.5]);
        $this->assertDatabaseHas('stocks', [
            'articulo_id' => $articulo, 'punto' => 'Recepción', 'cantidad' => 4, 'precioVta' => 25,
        ]);
        $this->assertDatabaseCount('movimientos', 2);
        $this->assertDatabaseHas('movimientos', [
            'articulo_id' => $articulo, 'user_id' => $usuario->id, 'tipo' => 'Movimiento',
            'cantidad' => 4, 'precio' => 0, 'observacion' => 'Desde Bar hasta Recepción',
        ]);
    }

    public function test_mover_valida_los_puntos_y_la_cantidad_sin_modificar_stock(): void
    {
        $articulo = $this->crearArticulo('Agua');
        $this->actingAs(User::factory()->create());
        DB::table('stocks')->insert(['articulo_id' => $articulo, 'punto' => 'Bar', 'cantidad' => 2]);
        $url = route('articulos.grabar-movimiento', $articulo);
        $datos = ['punto' => 'Bar', 'punto_hasta' => 'Comedor', 'cantidad' => 1];
        foreach ([
            [['punto_hasta' => 'Bar'], 'punto_hasta'],
            [['punto_hasta' => 'Inválido'], 'punto_hasta'],
            [['punto' => 'Inválido'], 'punto'],
            [['cantidad' => 0], 'cantidad'],
            [['cantidad' => '1.5'], 'cantidad'],
            [['cantidad' => 3], 'cantidad'],
            [['punto' => 'Deposito'], 'cantidad'],
        ] as [$cambio, $campo]) {
            $this->from(route('articulos.mover', $articulo))->post($url, array_replace($datos, $cambio))
                ->assertRedirect(route('articulos.mover', $articulo))->assertSessionHasErrors($campo);
        }
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulo, 'punto' => 'Bar', 'cantidad' => 2]);
        $this->assertDatabaseCount('stocks', 1);
        $this->assertDatabaseCount('movimientos', 0);
    }

    public function test_mover_revierte_el_stock_si_no_se_puede_registrar_el_movimiento(): void
    {
        $articulo = $this->crearArticulo('Agua');
        DB::table('stocks')->insert(['articulo_id' => $articulo, 'punto' => 'Bar', 'cantidad' => 3]);
        DB::statement("CREATE TRIGGER fallar_movimiento BEFORE INSERT ON movimientos BEGIN SELECT RAISE(ABORT, 'Error de prueba'); END");
        $this->actingAs(User::factory()->create())->post(route('articulos.grabar-movimiento', $articulo), [
            'punto' => 'Bar', 'punto_hasta' => 'Comedor', 'cantidad' => 1,
        ])->assertInternalServerError();
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulo, 'punto' => 'Bar', 'cantidad' => 3]);
        $this->assertDatabaseMissing('stocks', ['articulo_id' => $articulo, 'punto' => 'Comedor']);
        $this->assertDatabaseCount('movimientos', 0);
    }

    public function test_mover_rechaza_desbordamiento_y_modifica_solo_el_primer_stock_por_punto(): void
    {
        $articulo = $this->crearArticulo('Agua');
        $origen = DB::table('stocks')->insertGetId(['articulo_id' => $articulo, 'punto' => 'Bar', 'cantidad' => 3]);
        $duplicado = DB::table('stocks')->insertGetId(['articulo_id' => $articulo, 'punto' => 'Bar', 'cantidad' => 6]);
        $destino = DB::table('stocks')->insertGetId(['articulo_id' => $articulo, 'punto' => 'Comedor', 'cantidad' => '999999999.999']);
        $this->actingAs(User::factory()->create());
        $datos = ['punto' => 'Bar', 'punto_hasta' => 'Comedor', 'cantidad' => 1];
        $this->post(route('articulos.grabar-movimiento', $articulo), $datos)->assertSessionHasErrors('cantidad');
        $this->assertDatabaseHas('stocks', ['id' => $origen, 'cantidad' => 3]);
        $this->assertDatabaseHas('stocks', ['id' => $destino, 'cantidad' => 999999999.999]);
        DB::table('stocks')->where('id', $destino)->update(['cantidad' => 2]);
        $this->post(route('articulos.grabar-movimiento', $articulo), $datos)
            ->assertRedirect(route('articulos.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('stocks', ['id' => $origen, 'cantidad' => 2]);
        $this->assertDatabaseHas('stocks', ['id' => $duplicado, 'cantidad' => 6]);
        $this->assertDatabaseHas('stocks', ['id' => $destino, 'cantidad' => 3]);
    }

    public function test_mover_requiere_autenticacion_y_articulo_existente(): void
    {
        $articulo = $this->crearArticulo('Agua');
        $datos = ['punto' => 'Bar', 'punto_hasta' => 'Comedor', 'cantidad' => 1];
        $this->post(route('articulos.grabar-movimiento', $articulo), $datos)->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->post(route('articulos.grabar-movimiento', 999999), $datos)->assertNotFound();
        $this->assertDatabaseCount('stocks', 0);
    }

    public function test_menu_de_precios_abre_el_listado_para_seleccionar_un_articulo(): void
    {
        $articulo = $this->crearArticulo('Agua');

        $response = $this->actingAs(User::factory()->create())
            ->get(route('articulos.index'))
            ->assertOk()
            ->assertSee('href="'.route('articulos.precio', $articulo).'"', false);

        $this->assertMatchesRegularExpression(
            '~<a\b[^>]*href="'.preg_quote(route('articulos.index'), '~').'"[^>]*>(?:(?!</a>).)*Modificar Precios(?:(?!</a>).)*</a>~s',
            $response->getContent()
        );
        $this->get(route('articulos.precio', $articulo))->assertOk()->assertSee('value="Agua"', false);
    }

    public function test_actualiza_todos_los_precios_sin_modificar_los_demas_datos(): void
    {
        $articulo = $this->crearArticulo('Agua');
        $otro = $this->crearArticulo('Jugo');
        $primero = DB::table('stocks')->insertGetId([
            'articulo_id' => $articulo, 'cantidad' => 3.5, 'precioCto' => 10,
            'precioVta' => 20, 'punto' => 'Bar', 'vence' => '2027-01-01', 'observacion' => 'Nota 1',
        ]);
        $segundo = DB::table('stocks')->insertGetId([
            'articulo_id' => $articulo, 'cantidad' => 7, 'precioCto' => 12,
            'precioVta' => 30, 'punto' => 'Cocina', 'vence' => '2027-02-01', 'observacion' => 'Nota 2',
        ]);
        $otroStock = DB::table('stocks')->insertGetId([
            'articulo_id' => $otro, 'precioVta' => 40, 'observacion' => 'Otra nota',
        ]);

        $this->actingAs(User::factory()->create())
            ->post(route('articulos.grabar-precio', $articulo), ['precio' => '99.50'])
            ->assertRedirect(route('articulos.index'));

        $this->assertDatabaseHas('stocks', [
            'id' => $primero, 'precioVta' => 99.5, 'cantidad' => 3.5, 'precioCto' => 10,
            'punto' => 'Bar', 'vence' => '2027-01-01', 'observacion' => 'Nota 1',
        ]);
        $this->assertDatabaseHas('stocks', [
            'id' => $segundo, 'precioVta' => 99.5, 'cantidad' => 7, 'precioCto' => 12,
            'punto' => 'Cocina', 'vence' => '2027-02-01', 'observacion' => 'Nota 2',
        ]);
        $this->assertDatabaseHas('stocks', ['id' => $otroStock, 'precioVta' => 40, 'observacion' => 'Otra nota']);

        $this->post(route('articulos.grabar-precio', $articulo), ['precio' => '99.50'])
            ->assertRedirect(route('articulos.index'));
        $this->assertSame(2, DB::table('stocks')->where('articulo_id', $articulo)->count());

        $this->get(route('articulos.index'))->assertOk()->assertSee('99.5');
        $this->get(route('articulos.precio', $articulo))
            ->assertOk()->assertSee('value="99.5"', false)->assertDontSee('name="observacion"', false);
    }

    public function test_crea_stock_si_no_existe(): void
    {
        $articulo = $this->crearArticulo('Agua');

        $this->actingAs(User::factory()->create())
            ->post(route('articulos.grabar-precio', $articulo), ['precio' => '25.50'])
            ->assertRedirect(route('articulos.index'));

        $this->assertDatabaseHas('stocks', [
            'articulo_id' => $articulo, 'precioVta' => 25.5,
            'cantidad' => 0, 'precioCto' => 0, 'observacion' => null,
        ]);
        $this->assertSame(1, DB::table('stocks')->where('articulo_id', $articulo)->count());

        $this->post(route('articulos.grabar', $articulo), [
            'tipo' => 'Entrada', 'cantidad' => '2', 'punto' => 'Deposito', 'motivo' => 'Ajuste', 'observacion' => 'Ingreso',
        ])->assertRedirect(route('articulos.index'));
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulo, 'punto' => 'Deposito', 'cantidad' => 2, 'precioVta' => 25.5]);
        $this->assertDatabaseHas('movimientos', [
            'articulo_id' => $articulo, 'tipo' => 'Ajuste', 'cantidad' => 2, 'precio' => 0,
            'observacion' => 'Entrada - Ajuste - Ingreso',
        ]);
        $this->getJson(route('comandas.cargar'))
            ->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.stock_deposito', 2)
            ->assertJsonPath('data.0.precio', 25.5);
    }

    public function test_ajuste_solo_modifica_el_primer_stock_del_punto_seleccionado(): void
    {
        $articulo = $this->crearArticulo('Agua');
        $primero = DB::table('stocks')->insertGetId(['articulo_id' => $articulo, 'punto' => 'Bar', 'cantidad' => 5, 'precioVta' => 25]);
        $segundo = DB::table('stocks')->insertGetId(['articulo_id' => $articulo, 'punto' => 'Bar', 'cantidad' => 7, 'precioVta' => 25]);
        $otro = DB::table('stocks')->insertGetId(['articulo_id' => $articulo, 'punto' => 'Comedor', 'cantidad' => 9, 'precioVta' => 25]);
        $sinPunto = DB::table('stocks')->insertGetId(['articulo_id' => $articulo, 'cantidad' => 3, 'precioVta' => 25]);
        $datos = ['tipo' => 'Salida', 'cantidad' => 6, 'punto' => 'Bar', 'motivo' => 'Ajuste', 'observacion' => 'Prueba'];
        $this->actingAs(User::factory()->create());

        $this->post(route('articulos.grabar', $articulo), $datos)->assertSessionHasErrors('cantidad');
        $this->assertDatabaseHas('stocks', ['id' => $primero, 'cantidad' => 5]);
        $this->assertSame(0, DB::table('movimientos')->count());

        $datos['cantidad'] = 2;
        $this->post(route('articulos.grabar', $articulo), $datos)
            ->assertRedirect(route('articulos.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('stocks', ['id' => $primero, 'punto' => 'Bar', 'cantidad' => 3]);
        $this->assertDatabaseHas('stocks', ['id' => $segundo, 'punto' => 'Bar', 'cantidad' => 7]);
        $this->assertDatabaseHas('stocks', ['id' => $otro, 'punto' => 'Comedor', 'cantidad' => 9]);
        $this->assertDatabaseHas('stocks', ['id' => $sinPunto, 'punto' => null, 'cantidad' => 3]);
        $this->assertDatabaseHas('movimientos', [
            'articulo_id' => $articulo, 'tipo' => 'Ajuste', 'cantidad' => 2, 'precio' => 0,
            'observacion' => 'Salida - Ajuste - Prueba',
        ]);
    }

    public function test_ajuste_informa_el_stock_del_primer_registro_de_cada_punto(): void
    {
        $articulo = $this->crearArticulo('Agua');
        DB::table('stocks')->insert([
            ['articulo_id' => $articulo, 'punto' => 'Deposito', 'cantidad' => 0],
            ['articulo_id' => $articulo, 'punto' => 'Deposito', 'cantidad' => 5],
            ['articulo_id' => $articulo, 'punto' => 'Bar', 'cantidad' => 2],
            ['articulo_id' => $articulo, 'punto' => 'Comedor', 'cantidad' => -1],
        ]);
        $this->actingAs(User::factory()->create());

        $respuesta = $this->get(route('articulos.ajuste', $articulo))->assertOk()
            ->assertViewHas('stocksPorPunto', [
                'Deposito' => 0.0, 'Comedor' => -1.0, 'Bar' => 2.0, 'Recepción' => 0.0, 'Producción' => 0.0,
            ]);
        $this->assertMatchesRegularExpression('~<option value="Deposito" data-sin-stock="1"[^>]*selected>~', $respuesta->getContent());
        $this->assertMatchesRegularExpression('~<option value="Bar" data-sin-stock="0"~', $respuesta->getContent());
        $this->assertMatchesRegularExpression('~<small id="stockAviso"[^>]*role="status"\\s*>~', $respuesta->getContent());

        $bar = $this->withSession(['_old_input' => ['punto' => 'Bar']])
            ->get(route('articulos.ajuste', $articulo))->assertOk();
        $this->assertMatchesRegularExpression('~<small id="stockAviso"[^>]*hidden~', $bar->getContent());
    }

    public function test_ajuste_de_produccion_muestra_y_actualiza_su_stock(): void
    {
        $articulo = $this->crearArticulo('Agua');
        $this->actingAs(User::factory()->create());

        $respuesta = $this->withSession(['_old_input' => ['punto' => 'Producción']])
            ->get(route('articulos.ajuste', $articulo))->assertOk()
            ->assertViewHas('stocksPorPunto', fn ($stocks) => $stocks['Producción'] === 0.0);
        $this->assertMatchesRegularExpression('~<option value="Producción" data-sin-stock="1"[^>]*selected>~u', $respuesta->getContent());
        $this->assertMatchesRegularExpression('~<small id="stockAviso"[^>]*role="status"\\s*>~', $respuesta->getContent());

        $datos = [
            'tipo' => 'Entrada', 'cantidad' => '2.500', 'punto' => 'Producción',
            'motivo' => 'Producción', 'observacion' => 'Ingreso',
        ];
        $this->post(route('articulos.grabar', $articulo), $datos)
            ->assertRedirect(route('articulos.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulo, 'punto' => 'Producción', 'cantidad' => 2.5]);
        $this->assertDatabaseHas('movimientos', [
            'articulo_id' => $articulo, 'tipo' => 'Ajuste', 'observacion' => 'Entrada - Producción - Ingreso',
        ]);

        $respuesta = $this->withSession(['_old_input' => ['punto' => 'Producción']])
            ->get(route('articulos.ajuste', $articulo))->assertOk()
            ->assertViewHas('stocksPorPunto', fn ($stocks) => $stocks['Producción'] === 2.5);
        $this->assertMatchesRegularExpression('~<option value="Producción" data-sin-stock="0"[^>]*selected>~u', $respuesta->getContent());
        $this->assertMatchesRegularExpression('~<small id="stockAviso"[^>]*hidden~', $respuesta->getContent());

        $datos['tipo'] = 'Salida';
        $datos['cantidad'] = '1.000';
        $this->post(route('articulos.grabar', $articulo), $datos)
            ->assertRedirect(route('articulos.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulo, 'punto' => 'Producción', 'cantidad' => 1.5]);
    }

    public function test_ajuste_valida_el_punto_y_no_crea_stock_para_una_salida_sin_existencias(): void
    {
        $articulo = $this->crearArticulo('Agua');
        $this->actingAs(User::factory()->create());
        $datos = ['tipo' => 'Entrada', 'cantidad' => 2, 'motivo' => 'Ajuste', 'observacion' => 'Prueba'];

        $this->post(route('articulos.grabar', $articulo), $datos)->assertSessionHasErrors('punto');
        $this->post(route('articulos.grabar', $articulo), $datos + ['punto' => 'Inválido'])->assertSessionHasErrors('punto');
        $datos['tipo'] = 'Salida';
        $datos['punto'] = 'Recepción';
        $this->post(route('articulos.grabar', $articulo), $datos)->assertSessionHasErrors('cantidad');
        $this->assertDatabaseMissing('stocks', ['articulo_id' => $articulo]);
        $this->assertSame(0, DB::table('movimientos')->count());

        $datos['tipo'] = 'Entrada';
        $this->post(route('articulos.grabar', $articulo), $datos)->assertRedirect(route('articulos.index'));
        $this->assertDatabaseHas('stocks', [
            'articulo_id' => $articulo, 'punto' => 'Recepción', 'cantidad' => 2, 'precioVta' => 0,
        ]);
    }

    public function test_ajuste_valida_el_limite_de_la_observacion_del_movimiento(): void
    {
        $articulo = $this->crearArticulo('Agua');
        $this->actingAs(User::factory()->create());
        $prefijo = 'Entrada - Rotura - ';
        $datos = [
            'tipo' => 'Entrada', 'cantidad' => 2, 'punto' => 'Bar', 'motivo' => 'Rotura',
            'observacion' => str_repeat('á', 101 - mb_strlen($prefijo)),
        ];

        $this->post(route('articulos.grabar', $articulo), $datos)->assertSessionHasErrors('observacion');
        $this->assertDatabaseMissing('stocks', ['articulo_id' => $articulo]);
        $this->assertSame(0, DB::table('movimientos')->count());

        $datos['observacion'] = str_repeat('á', 100 - mb_strlen($prefijo));
        $this->post(route('articulos.grabar', $articulo), $datos)
            ->assertRedirect(route('articulos.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('movimientos', [
            'articulo_id' => $articulo, 'observacion' => $prefijo.$datos['observacion'],
        ]);
    }

    public function test_ajuste_rechaza_cantidades_fuera_de_la_precision_y_capacidad_de_stock(): void
    {
        $articulo = $this->crearArticulo('Agua');
        DB::table('stocks')->insert(['articulo_id' => $articulo, 'punto' => 'Bar', 'cantidad' => 999999999.999]);
        $this->actingAs(User::factory()->create());
        $datos = ['tipo' => 'Entrada', 'cantidad' => '0.0001', 'punto' => 'Bar', 'motivo' => 'Ajuste', 'observacion' => 'Prueba'];

        $this->post(route('articulos.grabar', $articulo), $datos)->assertSessionHasErrors('cantidad');
        $datos['cantidad'] = '0.001';
        $this->post(route('articulos.grabar', $articulo), $datos)->assertSessionHasErrors('cantidad');
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulo, 'punto' => 'Bar', 'cantidad' => 999999999.999]);
        $this->assertSame(0, DB::table('movimientos')->count());
    }

    public function test_valida_el_precio_y_la_existencia_del_articulo(): void
    {
        $articulo = $this->crearArticulo('Agua');
        $this->actingAs(User::factory()->create());

        foreach (['0', '-1', '1.001', '10000000000', 'abc', ''] as $precio) {
            $this->post(route('articulos.grabar-precio', $articulo), ['precio' => $precio])
                ->assertSessionHasErrors('precio');
        }
        $this->assertDatabaseMissing('stocks', ['articulo_id' => $articulo]);
        foreach (['0.01', '1', '9999999999.99'] as $precio) {
            $this->post(route('articulos.grabar-precio', $articulo), ['precio' => $precio])
                ->assertRedirect(route('articulos.index'))
                ->assertSessionHasNoErrors();
        }
        $this->get(route('articulos.precio', 99999))->assertNotFound();
        $this->post(route('articulos.grabar-precio', 99999), ['precio' => '1.00'])->assertNotFound();
    }

    public function test_las_rutas_de_precio_requieren_autenticacion(): void
    {
        $articulo = $this->crearArticulo('Agua');

        $this->get(route('articulos.precio', $articulo))->assertRedirect(route('login'));
        $this->post(route('articulos.grabar-precio', $articulo), ['precio' => '10'])
            ->assertRedirect(route('login'));
        $this->assertDatabaseMissing('stocks', ['articulo_id' => $articulo]);
    }
}
