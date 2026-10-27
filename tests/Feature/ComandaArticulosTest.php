<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComandaArticulosTest extends TestCase
{
    use RefreshDatabase;

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
