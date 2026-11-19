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

    public function test_formulario_de_compras_lista_proveedores_activos_por_nombre_y_cuit(): void
    {
        $ahora = now();
        $crear = fn (string $nombre, string $cuit, bool $activo) => DB::table('proveedores')->insertGetId([
            'nombre' => $nombre, 'razonSocial' => 'Razón social', 'cuit' => $cuit,
            'tipoIva' => 'Exento', 'activo' => $activo, 'fecha_creacion' => $ahora, 'fecha_actualizacion' => $ahora,
        ]);
        $zeta = $crear('Zeta', '20999999999', true);
        $alfa = $crear('Alfa <Uno>', '00123456789', true);
        $crear('Proveedor oculto', '20111111111', false);

        $this->get(route('compras.create'))->assertRedirect(route('login'));
        $respuesta = $this->actingAs(User::factory()->create())
            ->withSession(['_old_input' => ['proveedor_id' => (string) $zeta]])
            ->get(route('compras.create'))
            ->assertOk()
            ->assertSee('name="proveedor_id"', false)
            ->assertSee('Alfa &lt;Uno&gt; - 00123456789', false)
            ->assertSee('Zeta - 20999999999')
            ->assertDontSee('Proveedor oculto')
            ->assertSeeInOrder(['Alfa &lt;Uno&gt; - 00123456789', 'Zeta - 20999999999'], false);

        $this->assertMatchesRegularExpression('~<option value="'.$zeta.'"\\s+selected>\\s*Zeta - 20999999999\\s*</option>~', $respuesta->getContent());
        $this->assertMatchesRegularExpression('~<option value="'.$alfa.'"[^>]*>\\s*Alfa &lt;Uno&gt; - 00123456789\\s*</option>~', $respuesta->getContent());
    }

    public function test_formulario_de_compras_sin_proveedores_activos(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('compras.create'))
            ->assertOk()
            ->assertSee('Seleccione proveedor')
            ->assertViewHas('proveedores', fn ($proveedores) => $proveedores->isEmpty());
    }

    public function test_formulario_de_compras_carga_rubros_subrubros_y_articulos_y_restaura_su_seleccion(): void
    {
        $fecha = now();
        $rubroA = DB::table('rubros')->insertGetId([
            'nombre' => 'Alimentos', 'tipo' => 'Gasto', 'fecha_creacion' => $fecha, 'fecha_actualizacion' => $fecha,
        ]);
        $rubroB = DB::table('rubros')->insertGetId([
            'nombre' => 'Limpieza', 'tipo' => 'Gasto', 'fecha_creacion' => $fecha, 'fecha_actualizacion' => $fecha,
        ]);
        $subrubroA = DB::table('subrubros')->insertGetId([
            'rubro_id' => $rubroA, 'nombre' => 'Bebidas', 'activo' => false,
            'fecha_creacion' => $fecha, 'fecha_actualizacion' => $fecha,
        ]);
        $subrubroB = DB::table('subrubros')->insertGetId([
            'rubro_id' => $rubroB, 'nombre' => 'Higiene', 'activo' => true,
            'fecha_creacion' => $fecha, 'fecha_actualizacion' => $fecha,
        ]);
        $articuloA = DB::table('articulos')->insertGetId([
            'subrubro_id' => $subrubroA, 'nombre' => 'Agua', 'activo' => false,
            'fecha_creacion' => $fecha, 'fecha_actualizacion' => $fecha,
        ]);
        $articuloB = DB::table('articulos')->insertGetId([
            'subrubro_id' => $subrubroB, 'nombre' => 'Jabón', 'activo' => true,
            'fecha_creacion' => $fecha, 'fecha_actualizacion' => $fecha,
        ]);

        $this->actingAs(User::factory()->create());
        $respuesta = $this->withSession(['_old_input' => [
            'rubro_id' => (string) $rubroA, 'subrubro_id' => (string) $subrubroA, 'articulo_id' => (string) $articuloA,
        ]])->get(route('compras.create'))
            ->assertOk()
            ->assertViewHas('rubros', fn ($rubros) => $rubros->pluck('id')->all() === [$rubroA, $rubroB])
            ->assertViewHas('subrubros', fn ($subrubros) => $subrubros->pluck('id')->all() === [$subrubroA, $subrubroB])
            ->assertViewHas('articulos', fn ($articulos) => $articulos->pluck('id')->all() === [$articuloA, $articuloB]);

        $this->assertMatchesRegularExpression('~<option value="'.$rubroA.'"\\s+selected>Alimentos</option>~', $respuesta->getContent());
        $this->assertMatchesRegularExpression('~<option value="'.$subrubroA.'"\\s+selected>Bebidas</option>~', $respuesta->getContent());
        $this->assertMatchesRegularExpression('~<option value="'.$articuloA.'"\\s+selected>Agua</option>~', $respuesta->getContent());

        $invalida = $this->withSession(['_old_input' => [
            'rubro_id' => (string) $rubroA, 'subrubro_id' => (string) $subrubroB, 'articulo_id' => (string) $articuloB,
        ]])->get(route('compras.create'))->assertOk();
        $this->assertMatchesRegularExpression('~<select[^>]*id="articulo_id"[^>]*disabled[^>]*>\\s*<option value="">Seleccione artículo</option>\\s*</select>~', $invalida->getContent());
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
