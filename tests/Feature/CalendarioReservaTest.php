<?php

namespace Tests\Feature;

use App\Models\Reserva;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CalendarioReservaTest extends TestCase
{
    use RefreshDatabase;

    private function habitacion(int $numero, int $plazas = 2): int
    {
        return DB::table('habitaciones')->insertGetId([
            'numero' => (string) $numero,
            'tipo' => 'Habitación',
            'estado' => 'Disponible',
            'plazas' => $plazas,
            'matri' => 1,
            'simple' => 0,
            'venta' => true,
        ]);
    }

    private function pasajero(string $nombre, string $documento, ?string $nacimiento): int
    {
        $localidad = DB::table('localidades')->insertGetId([
            'id_prov' => '06', 'provincia' => 'Buenos Aires',
            'id_local' => $documento, 'localidad' => 'La Plata', 'cod_postal' => '1900',
        ]);

        return DB::table('pasajeros')->insertGetId([
            'tipoDocumento' => 'DNI', 'documento' => $documento, 'apelyNombre' => $nombre,
            'fechaNacimiento' => $nacimiento, 'telefono' => '123', 'direccion' => 'Calle 1',
            'cod_postal_id' => $localidad, 'fecha_creacion' => now(), 'fecha_actualizacion' => now(),
        ]);
    }

    private function evento(int $habitacion, string $fecha, string $estado = 'Disponible'): int
    {
        return DB::table('calendarios')->insertGetId([
            'title' => 'Habitación', 'description' => 'Original', 'start' => $fecha.' 00:00:00',
            'end' => $fecha.' 23:59:59', 'estado' => $estado, 'color' => '#0CB371',
            'habitacion_id' => $habitacion,
        ]);
    }

    private function crearReservaConEventos(): array
    {
        $habitacion = $this->habitacion(1);
        $pasajero = $this->pasajero('Ana Perez', '111', null);
        $detalle = 'Ana Perez (Comanda: SI)';
        $reserva = Reserva::create([
            'habitacion_id' => $habitacion, 'pasajero_id' => $pasajero,
            'fecha_inicio' => '2026-11-10', 'fecha_fin' => '2026-11-11',
            'detalle' => $detalle, 'estado' => 'Reservada',
        ]);
        $eventos = [
            $this->evento($habitacion, '2026-11-10', 'Reservada'),
            $this->evento($habitacion, '2026-11-11', 'Reservada'),
            $this->evento($habitacion, '2026-11-11', 'Reservada'),
        ];
        DB::table('calendarios')->whereIn('id', $eventos)->update([
            'pasajero_id' => $pasajero, 'description' => $detalle, 'color' => '#D2E014',
        ]);

        return [$reserva, $eventos, $habitacion, $pasajero, $detalle];
    }

    public function test_cambios_de_estado_de_reserva_secuenciales_y_liberacion(): void
    {
        [$reserva, $eventos, $habitacion, $pasajero, $detalle] = $this->crearReservaConEventos();
        $fuera = $this->evento($habitacion, '2026-11-12');
        $otra = $this->evento($this->habitacion(2), '2026-11-10');
        $url = route('reservas.cambiar-estado', $reserva);
        $this->patch($url, ['estado' => 'Confirmada'])->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create());

        $this->get(route('calendarios.index'))->assertOk()
            ->assertSee($url, false)->assertSee('value="Confirmada"', false)
            ->assertSee('value="Ocupada"', false)->assertSee('value="Disponible"', false)
            ->assertSee('value="Liberada"', false);
        $this->patch($url, ['estado' => 'Ocupada'])->assertSessionHasErrors('estado');
        $this->patch($url, ['estado' => 'Liberada'])->assertSessionHasErrors('estado');
        $this->assertDatabaseHas('reservas', ['id' => $reserva->id, 'estado' => 'Reservada']);

        foreach (['Confirmada' => '#F29727', 'Ocupada' => '#B82E27', 'Liberada' => '#A69F9F'] as $estado => $color) {
            $this->patch($url, ['estado' => $estado])->assertRedirect(route('calendarios.index'))
                ->assertSessionHasNoErrors();
            $this->assertDatabaseHas('reservas', ['id' => $reserva->id, 'estado' => $estado]);
            foreach ($eventos as $id) {
                $this->assertDatabaseHas('calendarios', [
                    'id' => $id, 'estado' => $estado, 'color' => $color,
                    'pasajero_id' => $pasajero, 'description' => $detalle,
                ]);
            }
        }

        $this->get(route('calendarios.index'))->assertOk()
            ->assertSee('background-color: #A69F9F;', false)
            ->assertSee('Liberada');
        $this->patch($url, ['estado' => 'Disponible'])->assertRedirect(route('calendarios.index'))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('reservas', ['id' => $reserva->id]);
        foreach ($eventos as $id) {
            $this->assertDatabaseHas('calendarios', [
                'id' => $id, 'estado' => 'Disponible', 'color' => '#0CB371',
                'pasajero_id' => null, 'description' => '',
            ]);
        }
        foreach ([$fuera, $otra] as $id) {
            $this->assertDatabaseHas('calendarios', ['id' => $id, 'estado' => 'Disponible', 'description' => 'Original']);
        }
        $this->post(route('calendarios.reservar'), [
            'habitacion_id' => $habitacion, 'fecha_inicio' => '2026-11-10', 'fecha_fin' => '2026-11-11',
            'pasajeros' => [['id' => $pasajero, 'comanda' => 'SI']],
        ])->assertSessionHasNoErrors();
        $this->patch($url, ['estado' => 'Confirmada'])->assertNotFound();
        foreach ($eventos as $id) {
            $this->assertDatabaseHas('calendarios', [
                'id' => $id, 'estado' => 'Reservada', 'pasajero_id' => $pasajero,
            ]);
        }
    }

    public function test_se_puede_liberar_una_reserva_sin_haberla_pagado(): void
    {
        [$reserva, $eventos] = $this->crearReservaConEventos();
        $this->actingAs(User::factory()->create())
            ->patch(route('reservas.cambiar-estado', $reserva), ['estado' => 'Disponible'])
            ->assertSessionHasNoErrors()->assertRedirect(route('calendarios.index'));
        $this->assertDatabaseMissing('reservas', ['id' => $reserva->id]);
        foreach ($eventos as $id) {
            $this->assertDatabaseHas('calendarios', ['id' => $id, 'estado' => 'Disponible', 'pasajero_id' => null]);
        }
    }

    public function test_cambios_de_estado_rechazan_fechas_inconsistentes_y_reservas_solapadas(): void
    {
        [$reserva, $eventos, $habitacion, $pasajero, $detalle] = $this->crearReservaConEventos();
        $url = route('reservas.cambiar-estado', $reserva);
        $this->actingAs(User::factory()->create());

        DB::table('calendarios')->where('id', $eventos[0])->update(['pasajero_id' => null]);
        $this->from(route('calendarios.index'))->patch($url, ['estado' => 'Disponible'])
            ->assertRedirect(route('calendarios.index'))->assertSessionHasErrors('estado');
        $this->assertDatabaseHas('reservas', ['id' => $reserva->id, 'estado' => 'Reservada']);
        $this->assertDatabaseHas('calendarios', ['id' => $eventos[1], 'estado' => 'Reservada']);
        DB::table('calendarios')->where('id', $eventos[0])->update(['pasajero_id' => $pasajero]);

        $solapada = Reserva::create([
            'habitacion_id' => $habitacion, 'pasajero_id' => $pasajero, 'detalle' => $detalle,
            'fecha_inicio' => '2026-11-11', 'fecha_fin' => '2026-11-12', 'estado' => 'Reservada',
        ]);
        $this->patch($url, ['estado' => 'Confirmada'])->assertSessionHasErrors('estado');
        $this->assertDatabaseHas('reservas', ['id' => $solapada->id, 'estado' => 'Reservada']);
        $this->assertDatabaseHas('calendarios', ['id' => $eventos[0], 'estado' => 'Reservada']);

        $solapada->delete();
        DB::table('calendarios')->whereIn('id', [$eventos[1], $eventos[2]])->delete();
        $this->patch($url, ['estado' => 'Confirmada'])->assertSessionHasErrors('estado');
        $this->assertDatabaseHas('reservas', ['id' => $reserva->id, 'estado' => 'Reservada']);
        $this->patch($url, ['estado' => 'Invalido'])->assertSessionHasErrors('estado');
    }

    public function test_liberar_reserva_revierte_calendario_si_falla_el_borrado(): void
    {
        [$reserva, $eventos] = $this->crearReservaConEventos();
        Reserva::deleting(function () {
            throw new \RuntimeException('Error de prueba al eliminar reserva');
        });

        try {
            $this->actingAs(User::factory()->create())
                ->patch(route('reservas.cambiar-estado', $reserva), ['estado' => 'Disponible'])
                ->assertStatus(500);
        } finally {
            Reserva::flushEventListeners();
        }

        $this->assertDatabaseHas('reservas', ['id' => $reserva->id, 'estado' => 'Reservada']);
        foreach ($eventos as $id) {
            $this->assertDatabaseHas('calendarios', ['id' => $id, 'estado' => 'Reservada']);
        }
    }

    public function test_las_rutas_muestran_el_calendario_y_el_listado_de_reservas(): void
    {
        $habitacion = $this->habitacion(98);
        $pasajero = $this->pasajero('Ana Perez', '111', null);
        DB::table('reservas')->insert([
            'habitacion_id' => $habitacion, 'pasajero_id' => $pasajero,
            'fecha_inicio' => '2026-11-10', 'fecha_fin' => '2026-11-11',
            'detalle' => 'Ana Perez (Comanda: SI)', 'estado' => 'Reservada',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->get('/admin/calendarios/calendario')->assertRedirect(route('login'));
        $this->get('/admin/calendarios/index')->assertRedirect(route('login'));

        $this->actingAs(User::factory()->create())
            ->get('/admin/calendarios/calendario')
            ->assertOk()->assertViewIs('admin.calendarios.calendario');
        $this->get('/admin/calendarios/index')
            ->assertOk()->assertViewIs('admin.calendarios.index')
            ->assertSee('2026-11-10')->assertSee('2026-11-11')
            ->assertSee('98')->assertSee('Ana Perez')
            ->assertSee('Ana Perez (Comanda: SI)')->assertSee('Reservada')
            ->assertSee(route('calendarios.calendario'), false);
    }

    public function test_reserva_actualiza_todos_los_eventos_del_rango_y_solo_la_habitacion_elegida(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 10)->startOfDay());
        $habitacion = $this->habitacion(1, 1);
        $otra = $this->habitacion(2);
        $adulto = $this->pasajero('Ana Perez', '111', '1990-01-01');
        $menor = $this->pasajero('Luis Perez', '222', '2022-11-11');
        $primero = $this->evento($habitacion, '2026-11-10');
        $segundo = $this->evento($habitacion, '2026-11-11');
        $duplicado = $this->evento($habitacion, '2026-11-11');
        $fuera = $this->evento($habitacion, '2026-11-12');
        $otraHabitacion = $this->evento($otra, '2026-11-10');

        $this->actingAs(User::factory()->create())->post(route('calendarios.reservar'), [
            'habitacion_id' => $habitacion, 'fecha_inicio' => '2026-11-10', 'fecha_fin' => '2026-11-11',
            'pasajeros' => [
                'p'.$adulto => ['id' => $adulto, 'comanda' => 'SI'],
                'p'.$menor => ['id' => $menor, 'comanda' => 'NO'],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect(route('calendarios.calendario'));

        foreach ([$primero, $segundo, $duplicado] as $id) {
            $this->assertDatabaseHas('calendarios', [
                'id' => $id, 'description' => 'Ana Perez (Comanda: SI), Luis Perez (Comanda: NO)',
                'estado' => 'Reservada', 'color' => '#E38919',
                'habitacion_id' => $habitacion, 'pasajero_id' => $adulto,
            ]);
        }
        foreach ([$fuera, $otraHabitacion] as $id) {
            $this->assertDatabaseHas('calendarios', ['id' => $id, 'estado' => 'Disponible', 'description' => 'Original']);
        }
        $this->assertDatabaseCount('calendarios', 5);
        $this->assertDatabaseCount('reservas', 1);
        $this->assertDatabaseHas('reservas', [
            'habitacion_id' => $habitacion,
            'pasajero_id' => $adulto,
            'fecha_inicio' => '2026-11-10',
            'fecha_fin' => '2026-11-11',
            'detalle' => 'Ana Perez (Comanda: SI), Luis Perez (Comanda: NO)',
            'estado' => 'Reservada',
        ]);
    }

    public function test_rechaza_reserva_sin_pasajeros_o_con_datos_invalidos(): void
    {
        $habitacion = $this->habitacion(1);
        $evento = $this->evento($habitacion, '2026-11-10');
        $datos = ['habitacion_id' => $habitacion, 'fecha_inicio' => '2026-11-10', 'fecha_fin' => '2026-11-10'];
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->post(route('calendarios.reservar'), $datos)
            ->assertSessionHasErrors('pasajeros');
        $this->actingAs($usuario)->post(route('calendarios.reservar'), $datos + [
            'pasajeros' => [['id' => 99999, 'comanda' => 'INVALIDO']],
        ])->assertSessionHasErrors(['pasajeros.0.id', 'pasajeros.0.comanda']);
        $this->assertDatabaseHas('calendarios', ['id' => $evento, 'estado' => 'Disponible']);
        $this->assertDatabaseCount('reservas', 0);
    }

    public function test_rechaza_rango_incompleto_o_ya_reservado_sin_modificar_eventos(): void
    {
        $habitacion = $this->habitacion(1);
        $pasajero = $this->pasajero('Ana', '111', null);
        $primero = $this->evento($habitacion, '2026-11-10');
        $datos = [
            'habitacion_id' => $habitacion, 'fecha_inicio' => '2026-11-10', 'fecha_fin' => '2026-11-11',
            'pasajeros' => [['id' => $pasajero, 'comanda' => 'SI']],
        ];
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->post(route('calendarios.reservar'), $datos)->assertSessionHasErrors('fecha_inicio');
        $reservada = $this->evento($habitacion, '2026-11-11', 'Reservada');
        $this->actingAs($usuario)->post(route('calendarios.reservar'), $datos)->assertSessionHasErrors('fecha_inicio');
        $this->assertDatabaseHas('calendarios', ['id' => $primero, 'estado' => 'Disponible', 'description' => 'Original']);
        $this->assertDatabaseHas('calendarios', ['id' => $reservada, 'estado' => 'Reservada', 'description' => 'Original']);
        $this->assertDatabaseCount('reservas', 0);
    }

    public function test_rechaza_exceso_de_plazas_y_cuenta_a_quien_cumple_cinco_anos(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 10)->startOfDay());
        $habitacion = $this->habitacion(1, 1);
        $adulto = $this->pasajero('Ana', '111', null);
        $cinco = $this->pasajero('Luis', '222', '2021-11-10');
        $evento = $this->evento($habitacion, '2026-11-10');
        $this->actingAs(User::factory()->create())->post(route('calendarios.reservar'), [
            'habitacion_id' => $habitacion, 'fecha_inicio' => '2026-11-10', 'fecha_fin' => '2026-11-10',
            'pasajeros' => [['id' => $adulto, 'comanda' => 'SI'], ['id' => $cinco, 'comanda' => 'NO']],
        ])->assertSessionHasErrors('pasajeros');
        $this->assertDatabaseHas('calendarios', ['id' => $evento, 'estado' => 'Disponible']);
        $this->assertDatabaseCount('reservas', 0);
    }

    public function test_descripcion_larga_se_guarda_completa(): void
    {
        $habitacion = $this->habitacion(1, 3);
        $pasajeros = [];
        $nombres = [];
        for ($i = 1; $i <= 3; $i++) {
            $nombre = str_repeat((string) $i, 95);
            $id = $this->pasajero($nombre, (string) $i, null);
            $pasajeros[] = ['id' => $id, 'comanda' => 'SI'];
            $nombres[] = $nombre.' (Comanda: SI)';
        }
        $this->evento($habitacion, '2026-11-10');
        $this->actingAs(User::factory()->create())->post(route('calendarios.reservar'), [
            'habitacion_id' => $habitacion, 'fecha_inicio' => '2026-11-10', 'fecha_fin' => '2026-11-10',
            'pasajeros' => $pasajeros,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('reservas', 1);
        $this->assertDatabaseHas('reservas', ['detalle' => implode(', ', $nombres)]);
    }

    public function test_fallo_al_guardar_reserva_revierte_cambios_del_calendario(): void
    {
        $habitacion = $this->habitacion(1);
        $pasajero = $this->pasajero('Ana', '111', null);
        $evento = $this->evento($habitacion, '2026-11-10');
        Reserva::creating(function () {
            throw new \RuntimeException('Error de prueba al guardar reserva');
        });

        try {
            $this->actingAs(User::factory()->create())->post(route('calendarios.reservar'), [
                'habitacion_id' => $habitacion, 'fecha_inicio' => '2026-11-10', 'fecha_fin' => '2026-11-10',
                'pasajeros' => [['id' => $pasajero, 'comanda' => 'SI']],
            ])->assertStatus(500);
        } finally {
            Reserva::flushEventListeners();
        }

        $this->assertDatabaseHas('calendarios', [
            'id' => $evento, 'estado' => 'Disponible', 'description' => 'Original',
            'color' => '#0CB371', 'pasajero_id' => null,
        ]);
        $this->assertDatabaseCount('reservas', 0);
    }

    public function test_la_pantalla_recupera_datos_del_modal_si_la_reserva_falla(): void
    {
        $habitacion = $this->habitacion(1);
        $pasajero = $this->pasajero('Ana', '111', null);
        $usuario = User::factory()->create();
        $datos = [
            'formulario' => 'reserva', 'habitacion_id' => $habitacion,
            'fecha_inicio' => '2026-11-10', 'fecha_fin' => '2026-11-11',
            'pasajeros' => ['p'.$pasajero => ['id' => $pasajero, 'comanda' => 'SI']],
        ];

        $this->actingAs($usuario)->from(route('calendarios.calendario'))
            ->post(route('calendarios.reservar'), $datos)
            ->assertSessionHasErrors('fecha_inicio')
            ->assertRedirect(route('calendarios.calendario'));
        $this->get(route('calendarios.calendario'))
            ->assertOk()
            ->assertSee('La habitación no está disponible en todas las fechas seleccionadas.')
            ->assertSee('Object.values({"p'.$pasajero.'":{"id":'.$pasajero.',"comanda":"SI"}})', false);
    }
}
