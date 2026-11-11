<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ReservasMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function migration()
    {
        return require database_path('migrations/2026_11_10_000001_add_missing_columns_to_reservas_table.php');
    }

    private function crearTablaAnterior(): void
    {
        Schema::drop('reservas');
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    public function test_agrega_los_campos_a_la_tabla_anterior_y_no_los_elimina_al_revertir(): void
    {
        $this->crearTablaAnterior();
        $migration = $this->migration();
        $migration->up();
        $migration->up();
        $migration->down();

        foreach (['habitacion_id', 'pasajero_id', 'fecha_inicio', 'fecha_fin', 'detalle', 'estado'] as $column) {
            $this->assertTrue(Schema::hasColumn('reservas', $column));
        }
    }

    public function test_no_modifica_la_tabla_nueva_que_ya_tiene_todos_los_campos(): void
    {
        $columns = Schema::getColumnListing('reservas');
        $this->migration()->up();
        $this->assertSame($columns, Schema::getColumnListing('reservas'));
    }

    public function test_rechaza_tabla_anterior_con_registros(): void
    {
        $this->crearTablaAnterior();
        DB::table('reservas')->insert(['created_at' => now(), 'updated_at' => now()]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('mientras existan registros');
        $this->migration()->up();
    }
}
