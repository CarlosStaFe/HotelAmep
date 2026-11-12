<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CalendarioDisponibilidadTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_restores_required_description_after_validation_error(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['_old_input' => ['estado' => 'No Disponible', 'descripcion' => 'Mantenimiento']])
            ->get(route('calendarios.disponibilidad'))
            ->assertOk()
            ->assertSee('<div class="col-md-3" id="descripcion-grupo" >', false)
            ->assertSee('required >Mantenimiento</textarea>', false);
    }

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
            'color' => '#0CB371',
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

    public function test_no_disponible_requires_a_description_and_saves_it_for_each_day(): void
    {
        $user = User::factory()->create();
        $habitacionId = DB::table('habitaciones')->insertGetId([
            'numero' => '97',
            'tipo' => 'Habitación',
            'estado' => 'Disponible',
            'plazas' => 2,
            'matri' => 1,
            'simple' => 0,
            'venta' => true,
        ]);
        $datos = [
            'habitacion_id' => $habitacionId,
            'estado' => 'No Disponible',
            'fecha_inicio' => '2026-11-01',
            'fecha_fin' => '2026-11-03',
        ];

        foreach ([null, '', '   ', ['texto']] as $descripcion) {
            $this->actingAs($user)
                ->post(route('calendarios.store'), $datos + ['descripcion' => $descripcion])
                ->assertSessionHasErrors('descripcion');
        }
        $this->assertDatabaseCount('calendarios', 0);

        $this->actingAs($user)
            ->post(route('calendarios.store'), $datos + ['descripcion' => 'Mantenimiento'])
            ->assertRedirect(route('calendarios.disponibilidad'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('calendarios', 3);
        foreach (['01', '02', '03'] as $dia) {
            $this->assertDatabaseHas('calendarios', [
                'habitacion_id' => $habitacionId,
                'estado' => 'No Disponible',
                'description' => 'Mantenimiento',
                'color' => '#E687DA',
                'start' => "2026-11-{$dia} 00:00:00",
            ]);
        }

        DB::table('calendarios')->where('habitacion_id', $habitacionId)->update(['color' => '#0CB371']);

        $this->actingAs($user)
            ->get(route('calendarios.calendario'))
            ->assertOk()
            ->assertViewHas('calendario', function ($eventos) {
                return $eventos->count() === 3
                    && $eventos->every(fn ($evento) => $evento->color === '#E687DA');
            });
        $this->assertDatabaseHas('calendarios', [
            'habitacion_id' => $habitacionId,
            'estado' => 'No Disponible',
            'color' => '#0CB371',
        ]);
    }

    public function test_other_states_do_not_save_a_description(): void
    {
        $user = User::factory()->create();
        $habitacionId = DB::table('habitaciones')->insertGetId([
            'numero' => '96',
            'tipo' => 'Habitación',
            'estado' => 'Disponible',
            'plazas' => 2,
            'matri' => 1,
            'simple' => 0,
            'venta' => true,
        ]);

        foreach (['Disponible', 'Reservada', 'Ocupada'] as $estado) {
            $this->actingAs($user)->post(route('calendarios.store'), [
                'habitacion_id' => $habitacionId,
                'estado' => $estado,
                'fecha_inicio' => '2026-11-01',
                'fecha_fin' => '2026-11-01',
                'descripcion' => 'No debe guardarse',
            ])->assertSessionHasNoErrors();

            $this->assertDatabaseHas('calendarios', [
                'habitacion_id' => $habitacionId,
                'estado' => $estado,
                'description' => '',
            ]);
        }
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
            ->get(route('calendarios.calendario'))
            ->assertOk()
            ->assertViewIs('admin.calendarios.calendario')
            ->assertSee('<option value="Liberada">Liberada</option>', false)
            ->assertSee('background-color: #A69F9F;', false)
            ->assertSee('aria-label="Referencias de estados"', false)
            ->assertSee('background-color: #0CB371;', false)
            ->assertSee('background-color: #E687DA;', false)
            ->assertSee('No Disponible')
            ->assertSee('background-color: #D2E014;', false)
            ->assertSee('background-color: #F29727;', false)
            ->assertSee('background-color: #B82E27;', false)
            ->assertSee('Cargar fechas de ocupación')
            ->assertSee('ocupacion_fecha_inicio')
            ->assertSee("new Option('Seleccione una habitación', '')", false)
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
            ->assertRedirect(route('calendarios.calendario'));

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
