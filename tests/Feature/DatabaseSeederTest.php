<?php

namespace Tests\Feature;

use App\Models\Habitacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeders_create_required_data()
    {
        $this->simularArchivos();
        $this->artisan('db:seed')->assertOk();

        $this->assertGreaterThan(0, User::count());
        $this->assertGreaterThan(0, Habitacion::count());
    }

    public function test_database_seeder_importa_y_actualiza_los_datos_en_orden(): void
    {
        $this->simularArchivos();
        $this->artisan('db:seed')->assertOk();

        $this->assertDatabaseHas('rubros', ['id' => 1, 'nombre' => 'Rubro']);
        $this->assertDatabaseHas('subrubros', ['id' => 2, 'rubro_id' => 1, 'nombre' => 'Subrubro']);
        $this->assertDatabaseHas('articulos', ['id' => 3, 'subrubro_id' => 2, 'nombre' => 'Artículo']);
        $this->assertDatabaseHas('proveedores', ['id' => 4, 'cod_postal_id' => 5, 'nombre' => 'Proveedor']);

        foreach (['rubros', 'subrubros', 'articulos', 'proveedores'] as $tabla) {
            DB::table($tabla)->update(['nombre' => 'Modificado']);
        }

        $this->artisan('db:seed')->assertOk();

        foreach (['rubros' => 'Rubro', 'subrubros' => 'Subrubro', 'articulos' => 'Artículo', 'proveedores' => 'Proveedor'] as $tabla => $nombre) {
            $this->assertSame(1, DB::table($tabla)->count());
            $this->assertDatabaseHas($tabla, ['nombre' => $nombre]);
        }
        $this->assertSame(10, DB::table('roles')->count());
    }

    private function simularArchivos(): void
    {
        $fecha = '2026-01-01 00:00:00';
        $fechas = ['fecha_creacion' => $fecha, 'fecha_actualizacion' => $fecha];
        $archivos = [
            'localidades' => [[
                'id' => 5, 'id_prov' => '01', 'provincia' => 'Provincia',
                'id_local' => '1', 'localidad' => 'Localidad', 'cod_postal' => '1000',
            ]],
            'rubros' => [[
                'id' => 1, 'nombre' => 'Rubro', 'tipo' => 'Gasto', 'observacion' => null,
            ] + $fechas],
            'subrubros' => [[
                'id' => 2, 'rubro_id' => 1, 'nombre' => 'Subrubro', 'activo' => true, 'observacion' => null,
            ] + $fechas],
            'articulos' => [[
                'id' => 3, 'subrubro_id' => 2, 'nombre' => 'Artículo', 'activo' => true,
                'comanda' => true, 'vencimiento' => false, 'observacion' => null,
            ] + $fechas],
            'proveedores' => [[
                'id' => 4, 'nombre' => 'Proveedor', 'razonSocial' => 'Proveedor SA',
                'cuit' => '12345678901', 'tipoIva' => 'Responsable', 'domicilio' => null,
                'cod_postal_id' => 5, 'telefono' => null, 'email' => null, 'echeck' => false,
                'cbu' => null, 'cvu' => null, 'alias' => null, 'dias' => null,
                'activo' => true, 'observacion' => null,
            ] + $fechas],
        ];

        File::partialMock();
        foreach ($archivos as $nombre => $registros) {
            $path = storage_path($nombre.'.json');
            File::shouldReceive('exists')->with($path)->andReturn(true);
            File::shouldReceive('get')->with($path)->andReturn(json_encode($registros, JSON_THROW_ON_ERROR));
        }
    }
}
