<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ArticuloPrecioTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_listado_muestra_la_suma_del_stock_de_cada_punto(): void
    {
        $articulo = $this->crearArticulo('Agua');
        $otro = $this->crearArticulo('Jugo');
        $this->crearArticulo('Sin stock');
        DB::table('stocks')->insert([
            ['articulo_id' => $articulo, 'punto' => 'Depósito', 'cantidad' => 1.125],
            ['articulo_id' => $articulo, 'punto' => 'Depósito', 'cantidad' => 2.25],
            ['articulo_id' => $articulo, 'punto' => 'Comedor', 'cantidad' => 0],
            ['articulo_id' => $articulo, 'punto' => 'Bar', 'cantidad' => 5],
            ['articulo_id' => $articulo, 'punto' => 'Recepción', 'cantidad' => 6],
            ['articulo_id' => $articulo, 'punto' => null, 'cantidad' => 99],
            ['articulo_id' => $otro, 'punto' => 'Bar', 'cantidad' => 8],
        ]);

        $response = $this->actingAs(User::factory()->create())
            ->get(route('articulos.index'))
            ->assertOk();

        $this->assertMatchesRegularExpression('~<td>Agua</td>\s*<td>3\.375</td>\s*<td>0</td>\s*<td>5</td>\s*<td>6</td>~', $response->getContent());
        $this->assertMatchesRegularExpression('~<td>Jugo</td>\s*<td>0</td>\s*<td>0</td>\s*<td>8</td>\s*<td>0</td>~', $response->getContent());
        $this->assertMatchesRegularExpression('~<td>Sin stock</td>\s*(?:<td>0</td>\s*){4}~', $response->getContent());
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
            'tipo' => 'Entrada', 'cantidad' => '2', 'punto' => 'Depósito', 'motivo' => 'Ajuste', 'observacion' => 'Ingreso',
        ])->assertRedirect(route('articulos.index'));
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulo, 'punto' => 'Depósito', 'cantidad' => 2, 'precioVta' => 25.5]);
        $this->getJson(route('comandas.cargar'))
            ->assertOk()->assertJsonFragment(['nombre' => 'Agua', 'stock' => 2, 'precio' => 25.5]);
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
        $this->assertDatabaseHas('movimientos', ['articulo_id' => $articulo, 'tipo' => 'Salida', 'cantidad' => 2]);
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
