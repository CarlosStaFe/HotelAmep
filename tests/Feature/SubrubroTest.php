<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Rubro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SubrubroTest extends TestCase
{
    use RefreshDatabase;

    public function test_rubro_can_be_created_and_updated(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('rubros.store'), [
                'nombre' => 'Alimentos',
                'tipo' => 'Gasto',
                'observacion' => null,
            ])
            ->assertRedirect(route('rubros.index'));

        $this->assertDatabaseHas('rubros', ['nombre' => 'Alimentos']);
        $rubroId = DB::table('rubros')->value('id');

        $this->actingAs($user)
            ->put(route('rubros.update', $rubroId), [
                'nombre' => 'Alimentos y bebidas',
                'tipo' => 'Gasto',
                'observacion' => 'Compras generales',
            ])
            ->assertRedirect(route('rubros.index'));

        $this->assertDatabaseHas('rubros', [
            'id' => $rubroId,
            'nombre' => 'Alimentos y bebidas',
            'observacion' => 'Compras generales',
        ]);
    }

    public function test_subrubro_can_be_created_and_updated(): void
    {
        $user = User::factory()->create();
        $rubroId = $this->createRubro();

        $this->actingAs($user)
            ->post(route('subrubros.store'), [
                'rubro_id' => $rubroId,
                'nombre' => 'Bebidas',
                'activo' => '1',
                'observacion' => null,
            ])
            ->assertRedirect(route('subrubros.index'));

        $this->assertDatabaseHas('subrubros', [
            'rubro_id' => $rubroId,
            'nombre' => 'Bebidas',
        ]);

        $subrubroId = DB::table('subrubros')->value('id');

        $this->actingAs($user)
            ->put(route('subrubros.update', $subrubroId), [
                'rubro_id' => $rubroId,
                'nombre' => 'Bebidas frías',
                'activo' => '1',
                'observacion' => 'Sin alcohol',
            ])
            ->assertRedirect(route('subrubros.index'));

        $this->assertDatabaseHas('subrubros', [
            'id' => $subrubroId,
            'nombre' => 'Bebidas frías',
            'observacion' => 'Sin alcohol',
        ]);

        $this->actingAs($user)
            ->get(route('subrubros.edit', $subrubroId))
            ->assertOk()
            ->assertSee('Bebidas frías');

        $this->actingAs($user)
            ->get(route('subrubros.index'))
            ->assertOk()
            ->assertSee('Alimentos')
            ->assertSee('Bebidas frías');

        $this->assertSame(
            'Bebidas frías',
            Rubro::with('subrubros')->findOrFail($rubroId)->subrubros->first()->nombre
        );
    }

    public function test_rubro_rename_migration_preserves_related_records_in_both_directions(): void
    {
        $rubroId = $this->createRubro();
        $now = now();
        $subrubroId = DB::table('subrubros')->insertGetId([
            'rubro_id' => $rubroId,
            'nombre' => 'Bebidas',
            'activo' => true,
            'observacion' => null,
            'fecha_creacion' => $now,
            'fecha_actualizacion' => $now,
        ]);
        $articuloId = DB::table('articulos')->insertGetId([
            'subrubro_id' => $subrubroId,
            'nombre' => 'Agua',
            'activo' => true,
            'comanda' => true,
            'vencimiento' => false,
            'observacion' => null,
            'fecha_creacion' => $now,
            'fecha_actualizacion' => $now,
        ]);
        $migration = require database_path('migrations/2026_10_27_000002_rename_ctrocostos_to_rubros.php');

        $migration->down();

        $this->assertTrue(Schema::hasTable('ctrocostos'));
        $this->assertTrue(Schema::hasColumn('subrubros', 'ctrocosto_id'));
        $this->assertDatabaseHas('ctrocostos', ['id' => $rubroId, 'nombre' => 'Alimentos']);
        $this->assertDatabaseHas('subrubros', ['id' => $subrubroId, 'ctrocosto_id' => $rubroId]);
        $this->assertDatabaseHas('articulos', ['id' => $articuloId, 'subrubro_id' => $subrubroId]);

        $migration->up();

        $this->assertTrue(Schema::hasTable('rubros'));
        $this->assertTrue(Schema::hasColumn('subrubros', 'rubro_id'));
        $this->assertDatabaseHas('rubros', ['id' => $rubroId, 'nombre' => 'Alimentos']);
        $this->assertDatabaseHas('subrubros', ['id' => $subrubroId, 'rubro_id' => $rubroId]);
        $this->assertDatabaseHas('articulos', ['id' => $articuloId, 'subrubro_id' => $subrubroId]);
    }

    private function createRubro(): int
    {
        $now = now();

        return DB::table('rubros')->insertGetId([
            'nombre' => 'Alimentos',
            'tipo' => 'Gasto',
            'fecha_creacion' => $now,
            'fecha_actualizacion' => $now,
        ]);
    }
}
