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
        $this->assertDatabaseCount('calendarios', 3);
        $this->assertDatabaseHas('calendarios', [
            'habitacion_id' => $habitacionId,
            'pasajero_id' => null,
            'estado' => 'Disponible',
            'start' => '2026-11-01 00:00:00',
            'end' => '2026-11-01 23:59:59',
        ]);
        $this->assertDatabaseHas('calendarios', [
            'habitacion_id' => $habitacionId,
            'estado' => 'Disponible',
            'start' => '2026-11-02 00:00:00',
            'end' => '2026-11-02 23:59:59',
        ]);
        $this->assertDatabaseHas('calendarios', [
            'habitacion_id' => $habitacionId,
            'estado' => 'Disponible',
            'start' => '2026-11-03 00:00:00',
            'end' => '2026-11-03 23:59:59',
        ]);
    }

    public function test_calendar_opens_occupancy_form_for_rooms_and_saves_selected_dates(): void
    {
        $user = User::factory()->create();
        $habitacionId = DB::table('habitaciones')->insertGetId([
            'numero' => '98',
            'tipo' => 'Habitación',
            'estado' => 'Disponible',
            'plazas' => 2,
            'matri' => 1,
            'simple' => 0,
            'venta' => true,
        ]);

        $this->actingAs($user)
            ->get(route('calendarios.index'))
            ->assertOk()
            ->assertSee('Cargar fechas de ocupación')
            ->assertSee('ocupacion_fecha_inicio')
            ->assertSee('<option value="">Seleccione una habitación</option>', false)
            ->assertSee('habitacionesFiltradasActuales.forEach(habitacion => {', false)
            ->assertSee('const idsHabitacionesFiltradas = new Set(eventosFiltrados.map(evento => (', false)
            ->assertSee('idsHabitacionesFiltradas.has(String(habitacion.id))', false)
            ->assertDontSee("document.querySelectorAll('#habitacionesResultadosBody tr')", false)
            ->assertDontSee('events: eventosCalendario', false);

        $this->actingAs($user)
            ->post(route('calendarios.store'), [
                'origen' => 'calendario',
                'habitacion_id' => $habitacionId,
                'estado' => 'Ocupada',
                'fecha_inicio' => '2026-11-05',
                'fecha_fin' => '2026-11-08',
            ])
            ->assertRedirect(route('calendarios.index'));

        $this->assertDatabaseCount('calendarios', 4);
        $this->assertDatabaseHas('calendarios', [
            'habitacion_id' => $habitacionId,
            'estado' => 'Ocupada',
            'start' => '2026-11-05 00:00:00',
            'end' => '2026-11-05 23:59:59',
        ]);
        $this->assertDatabaseHas('calendarios', [
            'habitacion_id' => $habitacionId,
            'estado' => 'Ocupada',
            'start' => '2026-11-08 00:00:00',
            'end' => '2026-11-08 23:59:59',
        ]);
    }
}
