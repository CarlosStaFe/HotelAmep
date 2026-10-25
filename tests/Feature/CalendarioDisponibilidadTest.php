<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CalendarioDisponibilidadTest extends TestCase
{
    use RefreshDatabase;

    public function test_disponibilidad_is_saved_without_a_passenger(): void
    {
        $user = User::factory()->create();
        $habitacionId = DB::table('habitaciones')->insertGetId([
            'numero' => '99',
            'tipo' => 'Habitación',
            'estado' => 'Disponible',
            'plazas' => 2,
            'matri' => 1,
            'simple' => 0,
            'venta' => true,
        ]);

        $response = $this->actingAs($user)->post(route('calendarios.store'), [
            'habitacion_id' => $habitacionId,
            'estado' => 'Disponible',
            'fecha_inicio' => '2026-11-01',
            'fecha_fin' => '2026-11-03',
        ]);

        $response->assertRedirect(route('calendarios.disponibilidad'));
        $this->assertDatabaseHas('calendarios', [
            'habitacion_id' => $habitacionId,
            'pasajero_id' => null,
            'estado' => 'Disponible',
            'start' => '2026-11-01 00:00:00',
            'end' => '2026-11-03 23:59:59',
        ]);
    }
}
