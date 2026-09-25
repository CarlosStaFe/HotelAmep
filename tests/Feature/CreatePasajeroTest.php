<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CreatePasajeroTest extends TestCase
{
    use RefreshDatabase;

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
            'tipo_documento' => 'DNI',
            'documento' => '12345678',
            'apelynombre' => 'Perez Juan',
            'telefono' => '2215551234',
            'domicilio' => 'Calle 1 123',
            'cod_postal_id' => $localidadId,
            'email' => 'juan.perez@example.com',
        ]);

        $response->assertRedirect('/admin/pasajeros/index');
        $this->assertDatabaseHas('pasajeros', [
            'tipo_documento' => 'DNI',
            'documento' => '12345678',
            'apelynombre' => 'Perez Juan',
            'direccion' => 'Calle 1 123',
            'cod_postal_id' => $localidadId,
        ]);
    }
}
