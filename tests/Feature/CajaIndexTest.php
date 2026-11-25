<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CajaIndexTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\WithFullMenuAccess;

    public function test_lista_todos_los_registros_de_caja_con_fechas_importes_y_usuario(): void
    {
        $usuario = User::factory()->create(['name' => 'Operador de caja']);
        foreach ([
            ['10', '1234.56', '0.10', '10.00', '1244.66'],
            ['20', '0.00', '2000.00', '0.00', '2000.00'],
        ] as [$habitacion, $efectivo, $transferencia, $tarjeta, $total]) {
            DB::table('cajas')->insert([
                'checkin' => '2026-11-19',
                'checkout' => '2026-11-24',
                'habitacion' => $habitacion,
                'efectivo' => $efectivo,
                'transferencia' => $transferencia,
                'tarjeta' => $tarjeta,
                'total' => $total,
                'user_id' => $usuario->id,
                'created_at' => '2026-11-24 18:20:30',
                'updated_at' => '2026-11-24 18:30:45',
            ]);
        }

        $this->actingAs($usuario)->get('/admin/caja')->assertOk()
            ->assertViewIs('admin.caja.index')
            ->assertViewHas('cajas', fn ($cajas) => $cajas->count() === 2)
            ->assertSee('id="tablaCaja"', false)
            ->assertSee("order: [[1, 'asc'], [3, 'asc']]", false)
            ->assertSee('return data.columnVersion === 2;', false)
            ->assertSeeInOrder(['<th>Usuario</th>', '<th>Fecha de registro</th>', '<th>Última actualización</th>'], false)
            ->assertSee('data-order="2026-11-19"', false)
            ->assertSee('19/11/2026')
            ->assertSee('24/11/2026 18:20:30')
            ->assertSee('24/11/2026 18:30:45')
            ->assertSee('1,234.56')
            ->assertSee('1,244.66')
            ->assertSee('2,000.00')
            ->assertSee('Operador de caja');
    }

    public function test_listado_vacio_y_acceso_requiere_autenticacion(): void
    {
        $this->get(route('caja.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('caja.index'))
            ->assertOk()->assertViewHas('cajas', fn ($cajas) => $cajas->isEmpty());
    }

    public function test_menu_caja_es_un_enlace_y_no_un_formulario_post(): void
    {
        $response = $this->actingAs(User::factory()->create())->get(route('caja.index'))->assertOk();
        $url = preg_quote(route('caja.index'), '/');

        $this->assertMatchesRegularExpression('/<a\b[^>]*href="'.$url.'"[^>]*>/', $response->getContent());
        $response->assertDontSee('<form action="'.route('caja.index').'" method="POST">', false);
        $this->assertSame(1, substr_count($response->getContent(), 'id="sidebar-logout"'));
    }

    public function test_control_se_puede_marcar_y_revertir_de_forma_persistente(): void
    {
        $usuario = User::factory()->create();
        $datos = [
            'checkin' => '2026-11-19', 'checkout' => '2026-11-24', 'habitacion' => '10',
            'efectivo' => 10, 'transferencia' => 0, 'tarjeta' => 0, 'total' => 10,
            'user_id' => $usuario->id, 'created_at' => now(), 'updated_at' => now(),
        ];
        $id = DB::table('cajas')->insertGetId($datos);
        $otroId = DB::table('cajas')->insertGetId($datos);
        $this->assertDatabaseHas('cajas', ['id' => $id, 'controlado' => false]);

        $this->actingAs($usuario)->get(route('caja.index'))->assertOk()
            ->assertSee('btn btn-danger btn-sm')->assertSee('Sin Controlar');
        $this->patch(route('caja.control', $id), ['controlado' => '1'])
            ->assertRedirect(route('caja.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cajas', ['id' => $id, 'controlado' => true]);
        $this->assertDatabaseHas('cajas', ['id' => $otroId, 'controlado' => false]);
        $this->get(route('caja.index'))->assertOk()
            ->assertSee('btn btn-success btn-sm')->assertSee('Controlado');
        $this->patch(route('caja.control', $id), ['controlado' => '0'])
            ->assertRedirect(route('caja.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cajas', ['id' => $id, 'controlado' => false]);
        $this->get(route('caja.index'))->assertSee('btn btn-danger btn-sm')->assertSee('Sin Controlar');
    }

    public function test_control_requiere_autenticacion_y_rechaza_valores_invalidos(): void
    {
        $usuario = User::factory()->create();
        $id = DB::table('cajas')->insertGetId([
            'checkin' => '2026-11-19', 'checkout' => '2026-11-24', 'habitacion' => '10',
            'efectivo' => 10, 'transferencia' => 0, 'tarjeta' => 0, 'total' => 10,
            'user_id' => $usuario->id, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->patch(route('caja.control', $id), ['controlado' => '1'])->assertRedirect(route('login'));
        $this->actingAs($usuario)->patch(route('caja.control', $id), ['controlado' => 'otro'])
            ->assertSessionHasErrors('controlado');
        $this->patch(route('caja.control', 999999), ['controlado' => '1'])->assertNotFound();
        $this->assertDatabaseHas('cajas', ['id' => $id, 'controlado' => false]);
    }
}
