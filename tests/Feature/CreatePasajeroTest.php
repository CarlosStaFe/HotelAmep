<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CreatePasajeroTest extends TestCase
{
    use RefreshDatabase;

    public function test_passenger_edit_form_loads_existing_location(): void
    {
        $user = User::factory()->create();
        $localidadId = DB::table('localidades')->insertGetId([
            'id_prov' => '06',
            'provincia' => 'BUENOS AIRES',
            'id_local' => '06001',
            'localidad' => 'La Plata',
            'cod_postal' => '1900',
        ]);
        $pasajeroId = DB::table('pasajeros')->insertGetId([
            'tipoDocumento' => 'DNI',
            'documento' => '12345678',
            'apelyNombre' => 'Perez Juan',
            'fechaNacimiento' => '1990-02-15',
            'telefono' => '2215551234',
            'direccion' => 'Calle 1 123',
            'cod_postal_id' => $localidadId,
            'email' => 'juan.perez@example.com',
            'usaComanda' => true,
            'fecha_creacion' => now(),
            'fecha_actualizacion' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('pasajeros.edit', $pasajeroId))
            ->assertOk()
            ->assertSee('Perez Juan')
            ->assertSee('value="1990-02-15"', false)
            ->assertSee('id="cod_postal_id" name="cod_postal_id"', false)
            ->assertSee('const oldProvincia = "06";', false)
            ->assertSee('const oldLocalidad = "06001";', false)
            ->assertSee('const oldCodPostal = '.$localidadId.';', false);

        $this->actingAs($user)
            ->put(route('pasajeros.update', $pasajeroId), [
                'tipoDocumento' => 'DNI',
                'documento' => '12345678',
                'apelyNombre' => 'Perez Juan',
                'fechaNacimiento' => '1991-04-12',
                'telefono' => '2215551234',
                'direccion' => 'Calle 1 123',
                'cod_postal_id' => $localidadId,
                'email' => 'juan.perez@example.com',
                'usaComanda' => '1',
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('pasajeros.index'));

        $this->assertDatabaseHas('pasajeros', [
            'id' => $pasajeroId,
            'cod_postal_id' => $localidadId,
            'fechaNacimiento' => '1991-04-12',
        ]);

        $this->actingAs($user)
            ->get(route('pasajeros.edit', $pasajeroId))
            ->assertOk()
            ->assertSee('value="1991-04-12"', false);

        $this->actingAs($user)
            ->get(route('pasajeros.show', $pasajeroId))
            ->assertOk()
            ->assertSee('value="1991-04-12"', false);
    }

    public function test_passenger_can_be_created_from_form_data(): void
    {
        $user = User::factory()->create();

        $localidadId = DB::table('localidades')->insertGetId([
            'id_prov' => '06',
            'provincia' => 'BUENOS AIRES',
            'id_local' => '06001',
            'localidad' => 'La Plata',
            'cod_postal' => '1900',
        ]);

        $response = $this->actingAs($user)->post('/admin/pasajeros/create', [
            'tipoDocumento' => 'DNI',
            'documento' => '12345678',
            'apelyNombre' => 'Perez Juan',
            'fechaNacimiento' => '1985-06-20',
            'telefono' => '2215551234',
            'direccion' => 'Calle 1 123',
            'cod_postal_id' => $localidadId,
            'email' => 'juan.perez@example.com',
            'usaComanda' => '1',
        ]);

        $response->assertRedirect('/admin/pasajeros/index');
        $this->assertDatabaseHas('pasajeros', [
            'tipoDocumento' => 'DNI',
            'documento' => '12345678',
            'apelyNombre' => 'Perez Juan',
            'fechaNacimiento' => '1985-06-20',
            'telefono' => '2215551234',
            'email' => 'juan.perez@example.com',
            'direccion' => 'Calle 1 123',
            'usaComanda' => true,
            'cod_postal_id' => $localidadId,
        ]);
    }

    public function test_birth_date_is_required_and_must_be_valid_for_creation_and_update(): void
    {
        $user = User::factory()->create();
        $localidadId = DB::table('localidades')->insertGetId([
            'id_prov' => '06',
            'provincia' => 'BUENOS AIRES',
            'id_local' => '06001',
            'localidad' => 'La Plata',
            'cod_postal' => '1900',
        ]);
        $datos = [
            'tipoDocumento' => 'DNI',
            'documento' => '12345678',
            'apelyNombre' => 'Perez Juan',
            'telefono' => '2215551234',
            'direccion' => 'Calle 1 123',
            'cod_postal_id' => $localidadId,
            'email' => 'juan.perez@example.com',
            'usaComanda' => '1',
        ];
        $pasajeroId = DB::table('pasajeros')->insertGetId(array_merge($datos, [
            'fechaNacimiento' => '1990-01-01',
            'fecha_creacion' => now(),
            'fecha_actualizacion' => now(),
        ]));

        foreach (['', '2026-02-30'] as $fecha) {
            $payload = array_merge($datos, ['fechaNacimiento' => $fecha]);
            $this->actingAs($user)->post(route('pasajeros.store'), $payload)
                ->assertSessionHasErrors('fechaNacimiento');
            $this->actingAs($user)->put(route('pasajeros.update', $pasajeroId), $payload)
                ->assertSessionHasErrors('fechaNacimiento');
        }

        $this->assertDatabaseCount('pasajeros', 1);
        $this->assertDatabaseHas('pasajeros', [
            'id' => $pasajeroId,
            'fechaNacimiento' => '1990-01-01',
        ]);
    }
}
