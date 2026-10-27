<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Proveedor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CreateProveedorTest extends TestCase
{
    use RefreshDatabase;

    private function createLocalidad(): int
    {
        return DB::table('localidades')->insertGetId([
            'id_prov' => '06',
            'provincia' => 'BUENOS AIRES',
            'id_local' => '06001',
            'localidad' => 'La Plata',
            'cod_postal' => '1900',
        ]);
    }

    public function test_provider_is_created_from_create_form_field_names(): void
    {
        $user = User::factory()->create();
        $localidadId = $this->createLocalidad();

        $response = $this->actingAs($user)
            ->post(route('proveedores.store'), [
                'nombre' => 'Proveedor de prueba',
                'razonSocial' => 'Proveedor de prueba S.A.',
                'cuit' => '20123456789',
                'tipoIva' => 'Resp. Inscripto',
                'domicilio' => 'Calle 1 123',
                'provincia' => '6',
                'localidad' => '06001',
                'cod_postal_id' => $localidadId,
                'telefono' => '2215551234',
                'email' => 'proveedor@example.com',
                'activo' => '1',
                'echeck' => '1',
                'cbu' => '1234567890123456789012',
                'cvu' => '9876543210987654321098',
                'alias' => 'proveedor.alias',
                'dias' => '30',
                'observacion' => '',
            ]);

        $response->assertRedirect(route('proveedores.index'));
        $this->assertDatabaseHas('proveedores', [
            'nombre' => 'Proveedor de prueba',
            'razonSocial' => 'Proveedor de prueba S.A.',
            'tipoIva' => 'Resp. Inscripto',
            'cod_postal_id' => $localidadId,
            'echeck' => true,
            'cbu' => '1234567890123456789012',
            'cvu' => '9876543210987654321098',
            'alias' => 'proveedor.alias',
            'dias' => '30',
        ]);
    }

    public function test_provider_can_be_created_without_email_or_phone(): void
    {
        $user = User::factory()->create();
        $localidadId = $this->createLocalidad();

        $response = $this->actingAs($user)
            ->post(route('proveedores.store'), [
                'nombre' => 'Proveedor sin contacto',
                'razonSocial' => 'Proveedor sin contacto S.A.',
                'cuit' => '20123456789',
                'tipoIva' => 'Resp. Inscripto',
                'domicilio' => 'Calle 1 123',
                'provincia' => '6',
                'localidad' => '06001',
                'cod_postal_id' => $localidadId,
                'activo' => '1',
                'dias' => '30',
            ]);

        $response->assertRedirect(route('proveedores.index'));
        $this->assertDatabaseHas('proveedores', [
            'nombre' => 'Proveedor sin contacto',
            'telefono' => null,
            'email' => null,
        ]);
    }

    public function test_provider_edit_saves_all_fields_submitted_by_edit_form(): void
    {
        $user = User::factory()->create();
        $localidadId = $this->createLocalidad();
        $proveedor = Proveedor::create([
            'nombre' => 'Proveedor anterior',
            'razonSocial' => 'Nombre anterior S.A.',
            'cuit' => '20123456789',
            'tipoIva' => 'Monotributo',
            'domicilio' => 'Domicilio anterior',
            'cod_postal_id' => $localidadId,
            'telefono' => '111',
            'email' => 'anterior@example.com',
            'activo' => true,
            'echeck' => false,
            'fecha_creacion' => now(),
            'fecha_actualizacion' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('proveedores.edit', $proveedor->id))
            ->assertOk()
            ->assertSee('name="email"', false)
            ->assertSee('value="anterior@example.com"', false);

        $response = $this->actingAs($user)
            ->put(route('proveedores.update', $proveedor->id), [
                'nombre' => 'Proveedor actualizado',
                'razonSocial' => 'Nombre actualizado S.A.',
                'cuit' => '20987654321',
                'tipoIva' => 'Resp. Inscripto',
                'domicilio' => 'Calle actualizada 456',
                'provincia' => '6',
                'localidad' => '06001',
                'cod_postal_id' => $localidadId,
                'telefono' => '',
                'email' => '',
                'activo' => '1',
                'echeck' => '1',
                'cbu' => '1234567890123456789012',
                'cvu' => '9876543210987654321098',
                'alias' => 'proveedor.actualizado',
                'dias' => '45',
                'observacion' => 'Datos actualizados',
            ]);

        $response->assertRedirect(route('proveedores.index'));
        $this->assertDatabaseHas('proveedores', [
            'id' => $proveedor->id,
            'nombre' => 'Proveedor actualizado',
            'razonSocial' => 'Nombre actualizado S.A.',
            'cuit' => '20987654321',
            'tipoIva' => 'Resp. Inscripto',
            'domicilio' => 'Calle actualizada 456',
            'cod_postal_id' => $localidadId,
            'telefono' => null,
            'email' => null,
            'activo' => true,
            'echeck' => true,
            'cbu' => '1234567890123456789012',
            'cvu' => '9876543210987654321098',
            'alias' => 'proveedor.actualizado',
            'dias' => '45',
            'observacion' => 'Datos actualizados',
        ]);
    }
}
