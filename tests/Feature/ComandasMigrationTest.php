<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ComandasMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_agrega_estado_a_comandas_existentes_sin_perder_datos(): void
    {
        $migration = require database_path('migrations/2026_11_11_000001_add_estado_to_comandas_table.php');
        Schema::table('comandas', function (Blueprint $table) {
            $table->dropColumn('estado');
        });

        $now = now();
        $rubro = DB::table('rubros')->insertGetId([
            'nombre' => 'Alimentos', 'tipo' => 'Gasto',
            'fecha_creacion' => $now, 'fecha_actualizacion' => $now,
        ]);
        $subrubro = DB::table('subrubros')->insertGetId([
            'rubro_id' => $rubro, 'nombre' => 'Bebidas',
            'fecha_creacion' => $now, 'fecha_actualizacion' => $now,
        ]);
        $articulo = DB::table('articulos')->insertGetId([
            'subrubro_id' => $subrubro, 'nombre' => 'Agua',
            'fecha_creacion' => $now, 'fecha_actualizacion' => $now,
        ]);
        $localidad = DB::table('localidades')->insertGetId([
            'id_prov' => '06', 'provincia' => 'Buenos Aires',
            'id_local' => '06001', 'localidad' => 'La Plata', 'cod_postal' => '1900',
        ]);
        $pasajero = DB::table('pasajeros')->insertGetId([
            'tipoDocumento' => 'DNI', 'documento' => '12345678', 'apelyNombre' => 'Ana',
            'telefono' => '123', 'direccion' => 'Calle 1', 'cod_postal_id' => $localidad,
            'fecha_creacion' => $now, 'fecha_actualizacion' => $now,
        ]);
        $id = DB::table('comandas')->insertGetId([
            'articulo_id' => $articulo, 'pasajero_id' => $pasajero, 'user_id' => User::factory()->create()->id,
            'fecha' => $now, 'puntoVenta' => 'Bar', 'habitacion' => '1',
            'cantidad' => 1, 'precio' => 100, 'total' => 100,
            'fecha_creacion' => $now, 'fecha_actualizacion' => $now,
        ]);

        $migration->up();
        $migration->up();

        $this->assertTrue(Schema::hasColumn('comandas', 'estado'));
        $column = collect(Schema::getColumns('comandas'))->firstWhere('name', 'estado');
        $this->assertSame('Abierta', trim($column['default'], "'"));
        $this->assertDatabaseHas('comandas', [
            'id' => $id, 'articulo_id' => $articulo, 'pasajero_id' => $pasajero,
            'total' => 100, 'estado' => 'Abierta',
        ]);
    }

    public function test_migracion_no_altera_la_tabla_si_estado_ya_existe(): void
    {
        $columns = Schema::getColumnListing('comandas');
        $migration = require database_path('migrations/2026_11_11_000001_add_estado_to_comandas_table.php');
        $migration->up();
        $migration->down();

        $this->assertSame($columns, Schema::getColumnListing('comandas'));
    }
}
