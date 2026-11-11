<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class CalendarioMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function migration()
    {
        return require database_path('migrations/2026_11_10_000003_limit_calendar_fields_to_50_characters.php');
    }

    private function crearEvento(string $estado): void
    {
        $habitacion = DB::table('habitaciones')->insertGetId([
            'numero' => '1',
            'tipo' => 'Habitación',
            'estado' => 'Disponible',
            'plazas' => 2,
            'matri' => 1,
            'simple' => 0,
            'venta' => true,
        ]);
        DB::table('calendarios')->insert([
            'title' => 'Habitación 1',
            'start' => '2026-11-10 00:00:00',
            'end' => '2026-11-10 23:59:59',
            'estado' => $estado,
            'habitacion_id' => $habitacion,
        ]);
    }

    public function test_preserva_los_eventos_al_aplicar_y_revertir(): void
    {
        $this->crearEvento('Disponible');
        $migration = $this->migration();
        $migration->down();
        $migration->up();

        $this->assertDatabaseHas('calendarios', [
            'title' => 'Habitación 1',
            'estado' => 'Disponible',
            'color' => '#0CB371',
            'textColor' => '#000000',
        ]);
    }

    public function test_rechaza_valores_largos_sin_modificar_los_registros(): void
    {
        $migration = $this->migration();
        $migration->down();
        $valor = str_repeat('a', 51);
        $this->crearEvento($valor);

        try {
            $migration->up();
            $this->fail('Se esperaba un error para un valor que supera 50 caracteres.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('estado', $exception->getMessage());
        }

        $this->assertDatabaseHas('calendarios', ['estado' => $valor]);
    }
}
