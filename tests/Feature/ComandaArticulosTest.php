<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComandaArticulosTest extends TestCase
{
    use RefreshDatabase;

    public function test_comanda_shows_rooms_with_occupied_reservations_only_once(): void
    {
        $user = User::factory()->create();
        $habitaciones = [];
        foreach ([
            ['numero' => '20', 'estado' => 'Disponible'],
            ['numero' => '10', 'estado' => 'Disponible'],
            ['numero' => '30', 'estado' => 'Ocupada'],
            ['numero' => '40', 'estado' => 'Ocupada'],
        ] as $habitacion) {
            $habitaciones[] = DB::table('habitaciones')->insertGetId($habitacion + [
                'tipo' => 'Habitación',
                'plazas' => 2,
                'matri' => 1,
                'simple' => 0,
                'venta' => true,
            ]);
        }

        $localidad = DB::table('localidades')->insertGetId([
            'id_prov' => '06', 'provincia' => 'Buenos Aires',
            'id_local' => '123', 'localidad' => 'La Plata', 'cod_postal' => '1900',
        ]);
        $pasajero = DB::table('pasajeros')->insertGetId([
            'tipoDocumento' => 'DNI', 'documento' => '123', 'apelyNombre' => 'Pasajero',
            'telefono' => '123', 'direccion' => 'Calle 1', 'cod_postal_id' => $localidad,
            'fecha_creacion' => now(), 'fecha_actualizacion' => now(),
        ]);

        foreach ([
            [$habitaciones[0], 'Ocupada'],
            [$habitaciones[0], 'Ocupada'],
            [$habitaciones[1], 'Ocupada'],
            [$habitaciones[2], 'Reservada'],
        ] as [$habitacion, $estado]) {
            DB::table('reservas')->insert([
                'habitacion_id' => $habitacion,
                'pasajero_id' => $pasajero,
                'fecha_inicio' => '2026-11-01',
                'fecha_fin' => '2026-11-03',
                'detalle' => 'Pasajero',
                'estado' => $estado,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->actingAs($user)
            ->get(route('comandas.comanda'))
            ->assertOk()
            ->assertViewHas('habitaciones', fn ($resultado) => $resultado->pluck('id')->all() === [
                $habitaciones[1], $habitaciones[0],
            ])
            ->assertSee('value="'.$habitaciones[1].'"', false)
            ->assertSee('value="'.$habitaciones[0].'"', false)
            ->assertDontSee('value="'.$habitaciones[2].'"', false)
            ->assertDontSee('value="'.$habitaciones[3].'"', false);
    }

    public function test_comanda_only_shows_active_articles_marked_for_comanda(): void
    {
        $user = User::factory()->create();
        $now = now();
        $rubroId = DB::table('rubros')->insertGetId([
            'nombre' => 'Alimentos',
            'tipo' => 'Gasto',
            'fecha_creacion' => $now,
            'fecha_actualizacion' => $now,
        ]);
        $subrubroId = DB::table('subrubros')->insertGetId([
            'rubro_id' => $rubroId,
            'nombre' => 'Bebidas',
            'activo' => true,
            'observacion' => null,
            'fecha_creacion' => $now,
            'fecha_actualizacion' => $now,
        ]);
        $articuloConComanda = DB::table('articulos')->insertGetId([
            'subrubro_id' => $subrubroId,
            'nombre' => 'Agua',
            'activo' => true,
            'comanda' => true,
            'vencimiento' => false,
            'fecha_creacion' => $now,
            'fecha_actualizacion' => $now,
        ]);
        DB::table('articulos')->insert([
            'subrubro_id' => $subrubroId,
            'nombre' => 'Artículo sin comanda',
            'activo' => true,
            'comanda' => false,
            'vencimiento' => false,
            'fecha_creacion' => $now,
            'fecha_actualizacion' => $now,
        ]);
        DB::table('articulos')->insert([
            'subrubro_id' => $subrubroId,
            'nombre' => 'Artículo inactivo',
            'activo' => false,
            'comanda' => true,
            'vencimiento' => false,
            'fecha_creacion' => $now,
            'fecha_actualizacion' => $now,
        ]);
        DB::table('stocks')->insert([
            'articulo_id' => $articuloConComanda,
            'cantidad' => 5,
            'precioVta' => 100,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $response = $this->actingAs($user)
            ->getJson(route('comandas.cargar'));

        $response->assertOk()
            ->assertJsonPath('data.0.nombre', 'Agua')
            ->assertJsonPath('data.0.subrubro', 'Bebidas')
            ->assertJsonPath('data.0.stock', 5)
            ->assertJsonPath('data.0.precio', 100)
            ->assertJsonCount(1, 'data');

        $this->actingAs($user)
            ->get(route('comandas.comanda'))
            ->assertOk()
            ->assertSee('admin\\/comandas\\/cargar', false);
    }
}
