<?php

namespace Tests\Feature;

use App\Http\Middleware\AuthorizeMenuAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MenuAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('Administrador', 'web');
        Role::findOrCreate('User', 'web');
        $this->admin = User::factory()->create(['activo' => true]);
        $this->admin->assignRole('Administrador');
    }

    public function test_administra_permisos_y_aplica_cambios_al_menu_y_las_rutas(): void
    {
        $user = User::factory()->create(['activo' => true]);
        $this->actingAs($user)->get(route('admin.index'))->assertOk()
            ->assertDontSee('Listar Comandas')->assertDontSee('Permisos de Menú');
        $this->get(route('comandas.index'))->assertForbidden();
        $this->post(route('comandas.cerrar'))->assertForbidden();
        $this->get(route('permisos.index'))->assertForbidden();

        $this->actingAs($this->admin)->get(route('permisos.index', ['usuario' => $user->id]))
            ->assertOk()->assertSee('value="comandas.index"', false);
        $this->put(route('permisos.update', $user), ['opciones' => ['comandas.index']])
            ->assertRedirect(route('permisos.index', ['usuario' => $user->id]));
        $this->assertDatabaseHas('user_menu_access', ['user_id' => $user->id, 'menu_key' => 'comandas.index']);

        $this->actingAs($user->fresh())->get(route('comandas.index'))->assertOk()
            ->assertSee('Listar Comandas')->assertDontSee('Cargar Consumo')->assertDontSee('Permisos de Menú');
        $this->post(route('comandas.cerrar'))->assertSessionHasErrors();
        $this->get(route('comandas.comanda'))->assertForbidden();
        $this->get(route('caja.index'))->assertForbidden();

        $this->actingAs($this->admin)->put(route('permisos.update', $user), [])->assertRedirect();
        $this->assertDatabaseMissing('user_menu_access', ['user_id' => $user->id, 'menu_key' => 'comandas.index']);
        $this->actingAs($user->fresh())->get(route('comandas.index'))->assertForbidden();
    }

    public function test_rutas_compartidas_y_acciones_de_creacion(): void
    {
        $user = User::factory()->create();
        DB::table('user_menu_access')->insert([
            ['user_id' => $user->id, 'menu_key' => 'proveedores.create'],
            ['user_id' => $user->id, 'menu_key' => 'rubros.create'],
        ]);
        $this->actingAs($user)->get(route('localidades.provincia', 999999))->assertOk()->assertJson([]);
        $this->get(route('proveedores.index'))->assertForbidden();
        $this->get(route('rubros.index'))->assertForbidden();
        $this->post(route('rubros.store'), ['nombre' => 'Prueba', 'tipo' => 'Prueba'])
            ->assertRedirect(route('rubros.create'));
        $this->assertDatabaseHas('rubros', ['nombre' => 'Prueba']);
    }

    public function test_solo_administradores_acceden_a_permisos_y_restauracion(): void
    {
        $user = User::factory()->create();
        DB::table('user_menu_access')->insert(['user_id' => $user->id, 'menu_key' => 'copias.index']);
        $this->actingAs($user)->get(route('copias.restaurar'))->assertForbidden();
        $this->post(route('copias.restaurar.post'))->assertForbidden();
        $this->put(route('permisos.update', $user), ['opciones' => ['caja.index']])->assertForbidden();
        $this->actingAs($this->admin)->get(route('copias.restaurar'))->assertOk()
            ->assertSee('Permisos de Menú');
        $this->get(route('permisos.index', ['usuario' => $this->admin->id]))
            ->assertOk()->assertSee('acceso completo permanente');
        $this->put(route('permisos.update', $this->admin), [])->assertForbidden();
        $this->put(route('permisos.update', $user), ['opciones' => ['copias.restaurar']])
            ->assertSessionHasErrors('opciones.0');
    }

    public function test_no_se_pueden_obtener_privilegios_ni_modificar_cuentas_administrativas(): void
    {
        $user = User::factory()->create(['activo' => true]);
        $otro = User::factory()->create(['activo' => true]);
        DB::table('user_menu_access')->insert([
            ['user_id' => $user->id, 'menu_key' => 'usuarios.create'],
            ['user_id' => $user->id, 'menu_key' => 'usuarios.index'],
        ]);
        $datos = [
            'nombre' => 'Intento', 'email' => 'intento@example.com', 'telefono' => '123',
            'password' => 'password123', 'password_confirmation' => 'password123',
            'role' => 'Administrador', 'activo' => 1,
        ];
        $this->actingAs($user)->post(route('usuarios.store'), $datos)->assertForbidden();
        $this->put(route('usuarios.update', $user), array_merge($datos, ['email' => $user->email]))->assertForbidden();
        $this->get(route('usuarios.edit', $this->admin))->assertForbidden();
        $this->put(route('usuarios.update', $this->admin), array_merge($datos, ['email' => $this->admin->email]))->assertForbidden();
        $this->put(route('usuarios.update', $otro), array_merge($datos, ['role' => 'User', 'email' => $otro->email]))->assertForbidden();
        $this->put(route('usuarios.update', $otro), array_merge($datos, [
            'role' => 'User', 'email' => 'cambiado@example.com', 'password' => null, 'password_confirmation' => null,
        ]))->assertForbidden();
        $this->assertFalse($user->fresh()->hasRole('Administrador'));
    }

    public function test_no_se_puede_desactivar_ni_cambiar_el_rol_del_ultimo_administrador(): void
    {
        $datos = [
            'nombre' => $this->admin->name, 'email' => $this->admin->email, 'telefono' => '123',
            'role' => 'User', 'activo' => 1,
        ];
        $this->actingAs($this->admin)->put(route('usuarios.update', $this->admin), $datos)->assertSessionHasErrors('role');
        $this->put(route('usuarios.update', $this->admin), array_merge($datos, ['role' => 'Administrador', 'activo' => 0]))
            ->assertSessionHasErrors('role');
        $this->assertTrue($this->admin->fresh()->hasRole('Administrador'));
        $this->assertTrue((bool) $this->admin->fresh()->activo);

        $otroAdmin = User::factory()->create(['activo' => true]);
        $otroAdmin->assignRole('Administrador');
        $this->put(route('usuarios.update', $this->admin), $datos)->assertSessionHasNoErrors();
        $this->assertFalse($this->admin->fresh()->hasRole('Administrador'));
        $this->assertTrue($otroAdmin->fresh()->hasRole('Administrador'));
    }

    public function test_usuario_autorizado_puede_editar_datos_no_sensibles_de_otro_usuario(): void
    {
        $user = User::factory()->create();
        $otro = User::factory()->create(['activo' => true]);
        DB::table('user_menu_access')->insert(['user_id' => $user->id, 'menu_key' => 'usuarios.index']);
        $this->actingAs($user)->get(route('usuarios.edit', $otro))->assertOk()
            ->assertDontSee('name="password"', false)->assertSee('readonly');
        $this->put(route('usuarios.update', $otro), [
            'nombre' => 'Nombre modificado', 'email' => $otro->email, 'telefono' => '123',
            'role' => 'User', 'activo' => 1,
        ])->assertRedirect(route('usuarios.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $otro->id, 'name' => 'Nombre modificado', 'email' => $otro->email]);
    }

    public function test_cada_opcion_de_menu_asignable_tiene_una_definicion_unica(): void
    {
        $urls = [];
        $recorrer = function (array $items) use (&$recorrer, &$urls) {
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                if (isset($item['submenu'])) {
                    $recorrer($item['submenu']);
                } elseif (isset($item['url'])) {
                    $urls[] = $item['url'];
                }
            }
        };
        $recorrer(config('adminlte.menu'));
        $adminOnly = ['/admin/permisos', '/admin/copias/restaurar'];
        $defined = array_column(config('menu_access'), 'url');
        $this->assertCount(count(array_unique($defined)), $defined);
        $this->assertEqualsCanonicalizing(array_merge($defined, $adminOnly), $urls);
    }

    public function test_todas_las_rutas_administrativas_estan_mapeadas_o_reservadas_al_administrador(): void
    {
        $rutas = collect(config('menu_access'))->pluck('rutas')->flatten()->unique()->all();
        $soloAdmin = ['permisos.index', 'permisos.update', 'copias.restaurar', 'copias.restaurar.post'];
        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'admin/')) {
                continue;
            }
            $this->assertContains(AuthorizeMenuAccess::class, $route->gatherMiddleware(), $route->uri());
            $this->assertContains($route->getName(), array_merge($rutas, $soloAdmin), $route->uri());
        }
    }
}
