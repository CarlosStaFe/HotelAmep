<?php

namespace Tests\Feature;

use App\Mail\ComandaMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class ComandaGuardarTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\WithFullMenuAccess;

    private function datos(): array
    {
        $fecha = now();
        $habitacion = DB::table('habitaciones')->insertGetId([
            'numero' => '10', 'tipo' => 'Habitación', 'estado' => 'Ocupada',
            'plazas' => 3, 'matri' => 1, 'simple' => 0, 'venta' => true,
        ]);
        $localidad = DB::table('localidades')->insertGetId([
            'id_prov' => '06', 'provincia' => 'Buenos Aires',
            'id_local' => '123', 'localidad' => 'La Plata', 'cod_postal' => '1900',
        ]);
        $pasajeros = [];
        foreach (['Ana', 'Luis'] as $indice => $nombre) {
            $pasajeros[] = DB::table('pasajeros')->insertGetId([
                'tipoDocumento' => 'DNI', 'documento' => (string) (100 + $indice),
                'apelyNombre' => $nombre, 'telefono' => '123', 'direccion' => 'Calle 1',
                'email' => strtolower($nombre).'@example.com', 'usaComanda' => true, 'mailComanda' => false,
                'cod_postal_id' => $localidad, 'fecha_creacion' => $fecha, 'fecha_actualizacion' => $fecha,
            ]);
        }
        $reserva = DB::table('reservas')->insertGetId([
            'habitacion_id' => $habitacion, 'pasajero_id' => $pasajeros[0], 'estado' => 'Ocupada',
            'fecha_inicio' => '2026-11-19', 'fecha_fin' => '2026-11-20',
            'detalle' => 'Reserva', 'created_at' => $fecha, 'updated_at' => $fecha,
        ]);
        foreach ($pasajeros as $pasajero) {
            DB::table('asignadas')->insert([
                'habitacion_id' => $habitacion, 'pasajero_id' => $pasajero, 'reserva_id' => $reserva,
                'estado' => 'Ocupada', 'checkin' => '2026-11-19', 'checkout' => '2026-11-20',
            ]);
        }
        $rubro = DB::table('rubros')->insertGetId([
            'nombre' => 'Alimentos', 'tipo' => 'Gasto', 'fecha_creacion' => $fecha, 'fecha_actualizacion' => $fecha,
        ]);
        $subrubro = DB::table('subrubros')->insertGetId([
            'rubro_id' => $rubro, 'nombre' => 'Bebidas', 'fecha_creacion' => $fecha, 'fecha_actualizacion' => $fecha,
        ]);
        $articulos = [];
        foreach ([['Agua <fría>', '2.35', 5], ['Café', '4.00', 9]] as [$nombre, $precio, $cantidad]) {
            $id = DB::table('articulos')->insertGetId([
                'subrubro_id' => $subrubro, 'nombre' => $nombre, 'activo' => true, 'comanda' => true,
                'fecha_creacion' => $fecha, 'fecha_actualizacion' => $fecha,
            ]);
            $articulos[] = $id;
            DB::table('stocks')->insert([
                'articulo_id' => $id, 'punto' => 'Bar', 'cantidad' => $cantidad, 'precioVta' => $precio,
            ]);
        }

        return [$habitacion, $pasajeros, $articulos];
    }

    private function detalles(array $pasajeros, array $articulos): array
    {
        return [
            ['articulo_id' => $articulos[0], 'pasajero_id' => $pasajeros[0], 'cantidad' => '0.1', 'precio' => '2.35', 'moza' => '12'],
            ['articulo_id' => $articulos[1], 'pasajero_id' => $pasajeros[0], 'cantidad' => '2', 'precio' => '4.00', 'moza' => '3'],
            ['articulo_id' => $articulos[1], 'pasajero_id' => $pasajeros[1], 'cantidad' => '1', 'precio' => '4.00', 'moza' => '4'],
        ];
    }

    public function test_listado_muestra_solo_los_consumos_y_devoluciones_abiertos(): void
    {
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        $usuario = User::factory()->create(['name' => 'Operador']);
        $fecha = '2026-11-19 17:10:00';
        foreach ([
            [$pasajeros[0], $articulos[0], 'Bar', 2, 4.70, 'Abierta', null],
            [$pasajeros[1], $articulos[1], 'Comedor', -1, -4, 'Abierta', 'Devolucion'],
            [$pasajeros[0], $articulos[0], 'Bar', 1, 2.35, 'Cerrada', 'Comanda cerrada de prueba'],
        ] as [$pasajero, $articulo, $punto, $cantidad, $total, $estado, $observacion]) {
            DB::table('comandas')->insert([
                'articulo_id' => $articulo, 'pasajero_id' => $pasajero, 'user_id' => $usuario->id,
                'fecha' => $fecha, 'habitacion' => '10', 'puntoVenta' => $punto,
                'cantidad' => $cantidad, 'precio' => $articulo === $articulos[0] ? 2.35 : 4,
                'total' => $total, 'moza' => '12', 'estado' => $estado, 'observacion' => $observacion,
                'fecha_creacion' => $fecha, 'fecha_actualizacion' => $fecha,
            ]);
        }

        $this->actingAs($usuario)->get(route('comandas.index'))
            ->assertOk()->assertViewIs('admin.comandas.index')
            ->assertViewHas('comandas', fn ($comandas) => $comandas->count() === 2)
            ->assertSee('tablaComandas')
            ->assertSee('Agua &lt;fría&gt;', false)->assertSee('Café')
            ->assertSee('Ana')->assertSee('Luis')->assertSee('Comedor')
            ->assertSee('Devolucion')->assertSee('-1')->assertSee('Operador')
            ->assertDontSee('Comanda cerrada de prueba');
    }

    public function test_selector_contiene_solo_habitaciones_con_comandas_abiertas_sin_duplicados(): void
    {
        [, $pasajeros, $articulos] = $this->datos();
        $usuario = User::factory()->create();
        $fecha = '2026-11-19 17:10:00';
        foreach ([['10', 'Abierta'], ['10', 'Abierta'], ['2', 'Abierta'], ['30', 'Cerrada']] as [$habitacion, $estado]) {
            DB::table('comandas')->insert([
                'articulo_id' => $articulos[0], 'pasajero_id' => $pasajeros[0], 'user_id' => $usuario->id,
                'fecha' => $fecha, 'habitacion' => $habitacion, 'puntoVenta' => 'Bar',
                'cantidad' => 1, 'precio' => 2.35, 'total' => 2.35, 'estado' => $estado,
                'fecha_creacion' => $fecha, 'fecha_actualizacion' => $fecha,
            ]);
        }

        $this->actingAs($usuario)->get(route('comandas.index'))
            ->assertOk()
            ->assertViewHas('habitacionesAbiertas', fn ($habitaciones) => $habitaciones->all() === ['2', '10'])
            ->assertSeeInOrder(['id="habitacionCerrar"', 'value="2"', 'value="10"', 'id="cerrarComanda"'], false)
            ->assertDontSee('<option value="30">', false);
    }

    public function test_cierra_comandas_de_la_habitacion_y_registra_los_medios_de_pago(): void
    {
        [, $pasajeros, $articulos] = $this->datos();
        $usuario = User::factory()->create();
        foreach ([
            ['10', 'Abierta', '12.34'],
            ['10', 'Abierta', '-2.04'],
            ['10', 'Cerrada', '50.00'],
            ['20', 'Abierta', '100.00'],
        ] as [$habitacion, $estado, $total]) {
            DB::table('comandas')->insert([
                'articulo_id' => $articulos[0], 'pasajero_id' => $pasajeros[0], 'user_id' => $usuario->id,
                'fecha' => now(), 'habitacion' => $habitacion, 'puntoVenta' => 'Bar',
                'cantidad' => 1, 'precio' => 1, 'total' => $total, 'estado' => $estado,
                'fecha_creacion' => now(), 'fecha_actualizacion' => now(),
            ]);
        }

        $this->actingAs($usuario)->post(route('comandas.cerrar'), [
            'habitacion' => '10', 'efectivo' => '0.10', 'transferencia' => '0.20', 'tarjeta' => '10.00',
        ])->assertRedirect(route('comandas.index'))->assertSessionHasNoErrors()->assertSessionHas('comanda_cerrada');
        $this->assertDatabaseHas('cajas', [
            'habitacion' => '10', 'checkin' => '2026-11-19', 'checkout' => '2026-11-20',
            'efectivo' => 0.10, 'transferencia' => 0.20, 'tarjeta' => 10, 'total' => 10.30,
            'user_id' => $usuario->id,
        ]);
        $this->assertSame(3, DB::table('comandas')->where('habitacion', '10')->where('estado', 'Cerrada')->count());
        $this->assertSame(1, DB::table('comandas')->where('habitacion', '20')->where('estado', 'Abierta')->count());

        $this->post(route('comandas.cerrar'), ['habitacion' => '10', 'efectivo' => '10.30'])
            ->assertSessionHasErrors('habitacion');
        $this->assertSame(1, DB::table('cajas')->count());
    }

    public function test_rechaza_pagos_invalidos_o_distintos_al_total_sin_cerrar(): void
    {
        [, $pasajeros, $articulos] = $this->datos();
        $usuario = User::factory()->create();
        DB::table('comandas')->insert([
            'articulo_id' => $articulos[0], 'pasajero_id' => $pasajeros[0], 'user_id' => $usuario->id,
            'fecha' => now(), 'habitacion' => '10', 'puntoVenta' => 'Bar',
            'cantidad' => 1, 'precio' => 10, 'total' => 10, 'estado' => 'Abierta',
            'fecha_creacion' => now(), 'fecha_actualizacion' => now(),
        ]);
        $this->actingAs($usuario);
        $this->post(route('comandas.cerrar'), ['habitacion' => '10', 'efectivo' => '9.99'])->assertSessionHasErrors('total');
        $this->post(route('comandas.cerrar'), ['habitacion' => '10', 'efectivo' => '10.01'])->assertSessionHasErrors('total');
        $this->post(route('comandas.cerrar'), ['habitacion' => '10'])->assertSessionHasErrors('efectivo');
        $this->post(route('comandas.cerrar'), ['habitacion' => '10', 'efectivo' => '-10'])->assertSessionHasErrors('efectivo');
        $this->post(route('comandas.cerrar'), ['habitacion' => '10', 'efectivo' => '10.001'])->assertSessionHasErrors('efectivo');
        $this->post(route('comandas.cerrar'), ['habitacion' => '10', 'efectivo' => '1,0'])->assertSessionHasErrors('efectivo');
        $this->assertDatabaseHas('comandas', ['habitacion' => '10', 'estado' => 'Abierta']);
        $this->assertSame(0, DB::table('cajas')->count());
    }

    public function test_muestra_importes_formateados_y_cierra_con_pago_con_separadores(): void
    {
        [, $pasajeros, $articulos] = $this->datos();
        $usuario = User::factory()->create();
        $datos = [
            'articulo_id' => $articulos[0], 'pasajero_id' => $pasajeros[0], 'user_id' => $usuario->id,
            'fecha' => now(), 'habitacion' => '10', 'puntoVenta' => 'Bar',
            'cantidad' => 1, 'precio' => 1234.50, 'total' => 1234.50, 'estado' => 'Abierta',
            'fecha_creacion' => now(), 'fecha_actualizacion' => now(),
        ];
        DB::table('comandas')->insert($datos);
        DB::table('comandas')->insert(array_merge($datos, ['habitacion' => '20', 'total' => -1234.50]));
        $this->actingAs($usuario)->get(route('comandas.index'))->assertOk()
            ->assertSee('>1,234.50</td>', false)
            ->assertSee('>-1,234.50</td>', false)
            ->assertSee('data-order="1234.5', false)
            ->assertSee('data-order="-1234.5', false);
        $this->post(route('comandas.cerrar'), ['habitacion' => '10', 'efectivo' => '1,234.50'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cajas', ['habitacion' => '10', 'efectivo' => 1234.50, 'total' => 1234.50]);
        $this->assertDatabaseHas('comandas', ['habitacion' => '10', 'estado' => 'Cerrada']);
    }

    public function test_cierre_exige_reserva_ocupada_y_permite_pago_cero_si_el_total_es_cero(): void
    {
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        $usuario = User::factory()->create();
        DB::table('comandas')->insert([
            'articulo_id' => $articulos[0], 'pasajero_id' => $pasajeros[0], 'user_id' => $usuario->id,
            'fecha' => now(), 'habitacion' => '10', 'puntoVenta' => 'Bar',
            'cantidad' => 1, 'precio' => 0, 'total' => 0, 'estado' => 'Abierta',
            'fecha_creacion' => now(), 'fecha_actualizacion' => now(),
        ]);
        DB::table('reservas')->where('habitacion_id', $habitacion)->update(['estado' => 'Reservada']);
        $this->actingAs($usuario)->post(route('comandas.cerrar'), ['habitacion' => '10', 'efectivo' => '0'])
            ->assertSessionHasErrors('habitacion');
        $this->assertSame(0, DB::table('cajas')->count());

        DB::table('reservas')->where('habitacion_id', $habitacion)->update(['estado' => 'Ocupada']);
        $this->post(route('comandas.cerrar'), ['habitacion' => '10', 'efectivo' => '0'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('cajas', ['habitacion' => '10', 'efectivo' => 0, 'total' => 0]);
    }

    public function test_no_cierra_si_hay_varias_reservas_ocupadas_o_sin_autenticacion(): void
    {
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        $usuario = User::factory()->create();
        DB::table('comandas')->insert([
            'articulo_id' => $articulos[0], 'pasajero_id' => $pasajeros[0], 'user_id' => $usuario->id,
            'fecha' => now(), 'habitacion' => '10', 'puntoVenta' => 'Bar',
            'cantidad' => 1, 'precio' => 10, 'total' => 10, 'estado' => 'Abierta',
            'fecha_creacion' => now(), 'fecha_actualizacion' => now(),
        ]);
        DB::table('reservas')->insert([
            'habitacion_id' => $habitacion, 'pasajero_id' => $pasajeros[1], 'estado' => 'Ocupada',
            'fecha_inicio' => '2026-11-21', 'fecha_fin' => '2026-11-22', 'detalle' => 'Reserva',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $datos = ['habitacion' => '10', 'efectivo' => '10'];
        $this->postJson(route('comandas.cerrar'), $datos)->assertUnauthorized();
        $this->actingAs($usuario)->post(route('comandas.cerrar'), $datos)->assertSessionHasErrors('habitacion');
        $this->assertDatabaseHas('comandas', ['habitacion' => '10', 'estado' => 'Abierta']);
        $this->assertSame(0, DB::table('cajas')->count());
    }

    public function test_incluye_el_numero_de_habitacion_en_el_correo_de_cada_pasajero(): void
    {
        Mail::fake();
        [$habitacion, $pasajeros, $articulos] = $this->datos();

        $this->actingAs(User::factory()->create())->postJson(route('comandas.store'), [
            'habitacion' => $habitacion, 'punto' => 'Bar', 'detalles' => $this->detalles($pasajeros, $articulos),
        ])->assertOk()->assertJsonPath('correos_enviados', 2);

        foreach (['ana@example.com', 'luis@example.com'] as $email) {
            Mail::assertSent(ComandaMail::class, function (ComandaMail $mail) use ($email) {
                return $mail->hasTo($email) && str_contains($mail->render(), 'Habitación: 10');
            });
        }
    }

    public function test_devuelve_consumo_guardado_y_notifica_al_pasajero(): void
    {
        Mail::fake();
        $this->travelTo(Carbon::parse('2026-11-19 17:10:00'));
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        $usuario = User::factory()->create();
        $this->actingAs($usuario);
        $detalle = $this->detalles($pasajeros, $articulos)[1];
        $carga = ['habitacion' => $habitacion, 'punto' => 'Bar', 'detalles' => [$detalle]];
        $this->postJson(route('comandas.store'), $carga)->assertOk();
        Mail::fake();

        DB::table('stocks')->where('articulo_id', $articulos[1])->update(['precioVta' => 6]);
        $detalle['precio'] = '6.00';
        $detalle['cantidad'] = '0.5';
        $detalle['devolucion'] = true;
        $carga['detalles'] = [$detalle];
        $this->postJson(route('comandas.store'), $carga)->assertOk()->assertJsonPath('correos_enviados', 1);

        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[1], 'punto' => 'Bar', 'cantidad' => 7.5]);
        $this->assertDatabaseHas('comandas', [
            'articulo_id' => $articulos[1], 'pasajero_id' => $pasajeros[0], 'user_id' => $usuario->id,
            'cantidad' => -0.5, 'precio' => 6, 'total' => -3, 'observacion' => 'Devolucion',
        ]);
        $this->assertDatabaseHas('movimientos', [
            'articulo_id' => $articulos[1], 'user_id' => $usuario->id, 'tipo' => 'Devolucion',
            'cantidad' => 0.5, 'precio' => 6, 'fecha' => '2026-11-19',
        ]);
        Mail::assertSent(ComandaMail::class, function (ComandaMail $mail) {
            $html = $mail->render();

            return $mail->hasTo('ana@example.com') && str_contains($html, 'Café (Devolución)')
                && str_contains($html, '-0.5') && str_contains($html, '-3.00') && $mail->total === '-3.00';
        });
    }

    public function test_rechaza_devoluciones_excesivas_y_consumos_de_la_misma_lista(): void
    {
        Mail::fake();
        $this->travelTo(Carbon::parse('2026-11-19 17:10:00'));
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        $this->actingAs(User::factory()->create());
        $consumo = $this->detalles($pasajeros, $articulos)[1];
        $devolucion = $consumo;
        $devolucion['cantidad'] = '1';
        $devolucion['devolucion'] = true;
        $carga = ['habitacion' => $habitacion, 'punto' => 'Bar', 'detalles' => [$consumo, $devolucion]];
        $this->postJson(route('comandas.store'), $carga)->assertUnprocessable()->assertJsonValidationErrors('detalles');
        $this->assertSame(0, DB::table('comandas')->count());
        $this->assertSame(0, DB::table('movimientos')->count());

        $carga['detalles'] = [$consumo];
        $this->postJson(route('comandas.store'), $carga)->assertOk();
        $devolucion['cantidad'] = '1.1';
        $carga['detalles'] = [$devolucion, $devolucion];
        $this->postJson(route('comandas.store'), $carga)->assertUnprocessable()->assertJsonValidationErrors('detalles');
        $this->assertSame(1, DB::table('comandas')->count());
        $this->assertSame(1, DB::table('movimientos')->count());
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[1], 'punto' => 'Bar', 'cantidad' => 7]);

        $devolucion['cantidad'] = '2';
        $carga['detalles'] = [$devolucion];
        $this->postJson(route('comandas.store'), $carga)->assertOk();
        $this->postJson(route('comandas.store'), $carga)->assertUnprocessable()->assertJsonValidationErrors('detalles');
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[1], 'punto' => 'Bar', 'cantidad' => 9]);
    }

    public function test_no_devuelve_consumos_anteriores_a_la_estadia_actual(): void
    {
        Mail::fake();
        $this->travelTo(Carbon::parse('2026-11-18 17:10:00'));
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        $this->actingAs(User::factory()->create());
        $detalle = $this->detalles($pasajeros, $articulos)[1];
        $carga = ['habitacion' => $habitacion, 'punto' => 'Bar', 'detalles' => [$detalle]];
        $this->postJson(route('comandas.store'), $carga)->assertOk();
        $this->travelTo(Carbon::parse('2026-11-19 17:10:00'));
        $detalle['devolucion'] = true;
        $carga['detalles'] = [$detalle];
        $this->postJson(route('comandas.store'), $carga)->assertUnprocessable()->assertJsonValidationErrors('detalles');
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[1], 'punto' => 'Bar', 'cantidad' => 7]);
    }

    public function test_la_devolucion_corresponde_al_pasajero_y_punto_del_consumo(): void
    {
        Mail::fake();
        $this->travelTo(Carbon::parse('2026-11-19 17:10:00'));
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        $this->actingAs(User::factory()->create());
        $detalle = $this->detalles($pasajeros, $articulos)[1];
        $carga = ['habitacion' => $habitacion, 'punto' => 'Bar', 'detalles' => [$detalle]];
        $this->postJson(route('comandas.store'), $carga)->assertOk();

        $detalle['devolucion'] = true;
        $detalle['pasajero_id'] = $pasajeros[1];
        $carga['detalles'] = [$detalle];
        $this->postJson(route('comandas.store'), $carga)->assertUnprocessable()->assertJsonValidationErrors('detalles');
        $detalle['pasajero_id'] = $pasajeros[0];
        $carga['detalles'] = [$detalle];
        $carga['punto'] = 'Comedor';
        $this->postJson(route('comandas.store'), $carga)->assertUnprocessable()->assertJsonValidationErrors('detalles');
        $this->assertSame(1, DB::table('comandas')->count());
        $this->assertSame(1, DB::table('movimientos')->count());
    }

    public function test_rechaza_devolucion_si_excede_la_capacidad_del_stock(): void
    {
        Mail::fake();
        $this->travelTo(Carbon::parse('2026-11-19 17:10:00'));
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        $this->actingAs(User::factory()->create());
        $detalle = $this->detalles($pasajeros, $articulos)[1];
        $carga = ['habitacion' => $habitacion, 'punto' => 'Bar', 'detalles' => [$detalle]];
        $this->postJson(route('comandas.store'), $carga)->assertOk();
        DB::table('stocks')->where('articulo_id', $articulos[1])->update(['cantidad' => '999999999.999']);
        $detalle['devolucion'] = true;
        $carga['detalles'] = [$detalle];
        $this->postJson(route('comandas.store'), $carga)->assertUnprocessable()->assertJsonValidationErrors('detalles');
        $this->assertSame(1, DB::table('comandas')->count());
        $this->assertSame(1, DB::table('movimientos')->count());
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[1], 'punto' => 'Bar', 'cantidad' => 999999999.999]);
    }

    public function test_guarda_consumos_descuenta_stock_y_envia_un_correo_privado_a_cada_pasajero(): void
    {
        Mail::fake();
        $this->travelTo(Carbon::parse('2026-11-19 17:10:00'));
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        $usuario = User::factory()->create();

        $this->actingAs($usuario)->postJson(route('comandas.store'), [
            'habitacion' => $habitacion, 'punto' => 'Bar', 'detalles' => $this->detalles($pasajeros, $articulos),
        ])->assertOk()->assertJsonPath('advertencias', [])->assertJsonPath('correos_enviados', 2);

        $this->assertSame(3, DB::table('comandas')->count());
        $this->assertDatabaseHas('comandas', [
            'pasajero_id' => $pasajeros[0], 'articulo_id' => $articulos[0], 'user_id' => $usuario->id,
            'cantidad' => 0.1, 'precio' => 2.35, 'total' => 0.24, 'moza' => '12', 'puntoVenta' => 'Bar',
        ]);
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[0], 'punto' => 'Bar', 'cantidad' => 4.9]);
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[1], 'punto' => 'Bar', 'cantidad' => 6]);
        $this->assertSame(3, DB::table('movimientos')->count());
        foreach ([
            [$articulos[0], 0.1, 2.35],
            [$articulos[1], 2, 4],
            [$articulos[1], 1, 4],
        ] as [$articulo, $cantidad, $precio]) {
            $this->assertDatabaseHas('movimientos', [
                'articulo_id' => $articulo, 'user_id' => $usuario->id, 'tipo' => 'Comanda',
                'cantidad' => $cantidad, 'precio' => $precio, 'fecha' => '2026-11-19',
            ]);
        }
        Mail::assertSent(ComandaMail::class, 2);
        Mail::assertSent(ComandaMail::class, function (ComandaMail $mail) {
            $html = $mail->render();

            return $mail->hasTo('ana@example.com') && count($mail->consumos) === 2
                && str_contains($html, 'Agua &lt;fría&gt;') && str_contains($html, 'Café')
                && str_contains($html, '19/11/2026 17:10') && str_contains($html, 'Bar')
                && str_contains($html, '0.24') && str_contains($html, '12')
                && str_contains($html, 'Total de sus consumos de esta comanda: 8.24')
                && str_contains($html, '<table border="1"')
                && substr_count($html, 'border: 1px solid #b8c5cc;') === 16
                && !str_contains($html, 'Luis');
        });
        Mail::assertSent(ComandaMail::class, function (ComandaMail $mail) {
            return $mail->hasTo('luis@example.com') && count($mail->consumos) === 1
                && str_contains($mail->render(), 'Café') && !str_contains($mail->render(), 'Agua')
                && str_contains($mail->render(), 'Total de sus consumos de esta comanda: 4.00');
        });
    }

    public function test_respeta_la_preferencia_y_avisa_si_falta_un_correo(): void
    {
        Mail::fake();
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        DB::table('pasajeros')->where('id', $pasajeros[0])->update(['email' => null]);
        DB::table('pasajeros')->where('id', $pasajeros[1])->update(['usaComanda' => false, 'mailComanda' => true]);

        $this->actingAs(User::factory()->create())->postJson(route('comandas.store'), [
            'habitacion' => $habitacion, 'punto' => 'Bar', 'detalles' => $this->detalles($pasajeros, $articulos),
        ])->assertOk()->assertJsonPath('correos_enviados', 0)->assertJsonCount(2, 'advertencias')
            ->assertJsonFragment(['No se envió el correo a Ana: dirección no válida.'])
            ->assertJsonFragment(['No se envió el correo a Luis: el pasajero no tiene habilitada la comanda.']);
        $this->assertSame(3, DB::table('comandas')->count());
        Mail::assertNothingSent();
    }

    public function test_si_falla_el_correo_la_comanda_queda_guardada_y_se_informa(): void
    {
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        Mail::shouldReceive('to')->once()->andThrow(new TransportException('Fallo de prueba'));

        $this->actingAs(User::factory()->create())->postJson(route('comandas.store'), [
            'habitacion' => $habitacion, 'punto' => 'Bar', 'detalles' => [$this->detalles($pasajeros, $articulos)[0]],
        ])->assertOk()->assertJsonPath('correos_enviados', 0)->assertJsonFragment(['No se pudo enviar el correo a Ana.']);
        $this->assertSame(1, DB::table('comandas')->count());
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[0], 'punto' => 'Bar', 'cantidad' => 4.9]);
        $this->assertDatabaseHas('movimientos', ['articulo_id' => $articulos[0], 'tipo' => 'Comanda', 'cantidad' => 0.1]);
    }

    public function test_rechaza_stock_insuficiente_duplicado_y_precio_modificado_sin_guardar(): void
    {
        Mail::fake();
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        $detalles = $this->detalles($pasajeros, $articulos);
        $this->actingAs(User::factory()->create());
        $carga = ['habitacion' => $habitacion, 'punto' => 'Bar', 'detalles' => $detalles];

        DB::table('stocks')->where('articulo_id', $articulos[1])->update(['cantidad' => 2]);
        $this->postJson(route('comandas.store'), $carga)->assertUnprocessable()->assertJsonValidationErrors('detalles');
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[0], 'cantidad' => 5]);
        DB::table('stocks')->where('articulo_id', $articulos[1])->update(['cantidad' => 9]);
        DB::table('stocks')->insert(['articulo_id' => $articulos[0], 'punto' => 'Bar', 'cantidad' => 10, 'precioVta' => 2.35]);
        $this->postJson(route('comandas.store'), $carga)->assertUnprocessable()->assertJsonValidationErrors('detalles');
        DB::table('stocks')->where('articulo_id', $articulos[0])->where('cantidad', 10)->delete();
        $carga['detalles'][0]['precio'] = '0.01';
        $this->postJson(route('comandas.store'), $carga)->assertUnprocessable()->assertJsonValidationErrors('detalles');
        $this->assertSame(0, DB::table('comandas')->count());
        $this->assertSame(0, DB::table('movimientos')->count());
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[0], 'cantidad' => 5]);
        Mail::assertNothingSent();
    }

    public function test_rechaza_pasajeros_no_asignados_y_exige_autenticacion(): void
    {
        Mail::fake();
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        $carga = ['habitacion' => $habitacion, 'punto' => 'Bar', 'detalles' => $this->detalles($pasajeros, $articulos)];
        $this->postJson(route('comandas.store'), $carga)->assertUnauthorized();
        DB::table('asignadas')->where('pasajero_id', $pasajeros[1])->update(['estado' => 'Reservada']);
        $this->actingAs(User::factory()->create())->postJson(route('comandas.store'), $carga)
            ->assertUnprocessable()->assertJsonValidationErrors('detalles');
        $this->assertSame(0, DB::table('comandas')->count());
        Mail::assertNothingSent();
    }

    public function test_descuenta_solo_del_punto_de_venta_elegido(): void
    {
        Mail::fake();
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        DB::table('stocks')->insert([
            'articulo_id' => $articulos[0], 'punto' => 'Recepción', 'cantidad' => 3, 'precioVta' => 2.35,
        ]);
        $this->actingAs(User::factory()->create())->postJson(route('comandas.store'), [
            'habitacion' => $habitacion, 'punto' => 'Recepcion', 'detalles' => [$this->detalles($pasajeros, $articulos)[0]],
        ])->assertOk();
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[0], 'punto' => 'Recepción', 'cantidad' => 2.9]);
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[0], 'punto' => 'Bar', 'cantidad' => 5]);
        $this->assertDatabaseHas('comandas', ['articulo_id' => $articulos[0], 'puntoVenta' => 'Recepción']);
    }

    public function test_rechaza_importes_fuera_de_la_capacidad_de_la_comanda(): void
    {
        Mail::fake();
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        DB::table('stocks')->where('articulo_id', $articulos[0])->update(['precioVta' => '9999999999.99']);
        $detalle = $this->detalles($pasajeros, $articulos)[0];
        $detalle['cantidad'] = '2';
        $detalle['precio'] = '9999999999.99';
        $this->actingAs(User::factory()->create())->postJson(route('comandas.store'), [
            'habitacion' => $habitacion, 'punto' => 'Bar', 'detalles' => [$detalle],
        ])->assertUnprocessable()->assertJsonValidationErrors('detalles');
        $this->assertSame(0, DB::table('comandas')->count());
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[0], 'cantidad' => 5]);
        Mail::assertNothingSent();
    }

    public function test_guarda_en_movimientos_precios_admitidos_por_comanda(): void
    {
        Mail::fake();
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        DB::table('stocks')->where('articulo_id', $articulos[0])->update(['precioVta' => '100000000.00']);
        $detalle = $this->detalles($pasajeros, $articulos)[0];
        $detalle['precio'] = '100000000.00';

        $this->actingAs(User::factory()->create())->postJson(route('comandas.store'), [
            'habitacion' => $habitacion, 'punto' => 'Bar', 'detalles' => [$detalle],
        ])->assertOk();

        $this->assertDatabaseHas('movimientos', [
            'articulo_id' => $articulos[0], 'tipo' => 'Comanda', 'cantidad' => 0.1, 'precio' => 100000000,
        ]);
    }

    public function test_revierte_los_movimientos_si_falla_una_linea_posterior(): void
    {
        Mail::fake();
        [$habitacion, $pasajeros, $articulos] = $this->datos();
        DB::table('stocks')->where('articulo_id', $articulos[1])->update(['precioVta' => '9999999999.99']);
        $detalles = $this->detalles($pasajeros, $articulos);
        $detalles[1]['precio'] = '9999999999.99';

        $this->actingAs(User::factory()->create())->postJson(route('comandas.store'), [
            'habitacion' => $habitacion, 'punto' => 'Bar', 'detalles' => array_slice($detalles, 0, 2),
        ])->assertUnprocessable()->assertJsonValidationErrors('detalles');

        $this->assertSame(0, DB::table('comandas')->count());
        $this->assertSame(0, DB::table('movimientos')->count());
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[0], 'punto' => 'Bar', 'cantidad' => 5]);
        $this->assertDatabaseHas('stocks', ['articulo_id' => $articulos[1], 'punto' => 'Bar', 'cantidad' => 9]);
        Mail::assertNothingSent();
    }
}
