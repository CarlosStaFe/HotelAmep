<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CopiaSeguridadTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_pages_list_download_and_delete_copies(): void
    {
        $user = User::factory()->create();
        Role::findOrCreate('Administrador', 'web');
        $user->assignRole('Administrador');
        $directorio = storage_path('app/backups');
        File::ensureDirectoryExists($directorio);
        $nombre = 'backup_test_' . Str::random(20) . '.sql';
        File::put($directorio . DIRECTORY_SEPARATOR . $nombre, 'SELECT 1;');

        try {
            $this->actingAs($user)->get('/admin/copias/index')
                ->assertOk()
                ->assertSee($nombre)
                ->assertSee('Generar copia de seguridad');
            $this->actingAs($user)->get('/admin/copias/restaurar')
                ->assertOk()
                ->assertSee($nombre);
            $this->actingAs($user)->get('/admin/copias/descargar/' . $nombre)->assertOk();
            $this->actingAs($user)->get('/admin/copias/descargar/inexistente.sql')->assertNotFound();
            $this->actingAs($user)->get('/admin/copias/descargar/clave.env')->assertNotFound();

            $this->actingAs($user)->delete('/admin/copias/' . $nombre)->assertRedirect(route('copias.index'));
            $this->assertFileDoesNotExist($directorio . DIRECTORY_SEPARATOR . $nombre);
        } finally {
            File::delete($directorio . DIRECTORY_SEPARATOR . $nombre);
        }
    }

    public function test_backup_routes_require_authentication(): void
    {
        $this->get('/admin/copias/index')->assertRedirect();
        $this->post('/admin/copias/restaurar')->assertRedirect();
    }

    public function test_no_se_restaura_una_copia_sin_tabla_de_permisos(): void
    {
        Role::findOrCreate('Administrador', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');
        $usuario = User::factory()->create();
        DB::table('user_menu_access')->insert(['user_id' => $usuario->id, 'menu_key' => 'caja.index']);
        $archivo = UploadedFile::fake()->createWithContent('antigua.sql', "-- CREATE TABLE user_menu_access (id INTEGER);\nDELETE FROM users;");

        $this->actingAs($admin)->from(route('copias.restaurar'))
            ->post(route('copias.restaurar.post'), ['archivo' => $archivo])
            ->assertRedirect(route('copias.restaurar'))
            ->assertSessionHas('error', 'La copia no contiene la tabla de permisos de menú y no puede restaurarse desde el sistema.');
        $this->assertDatabaseHas('users', ['id' => $usuario->id]);
        $this->assertDatabaseHas('user_menu_access', ['user_id' => $usuario->id, 'menu_key' => 'caja.index']);
    }

    public function test_se_acepta_una_copia_que_incluye_la_tabla_de_permisos(): void
    {
        Role::findOrCreate('Administrador', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('Administrador');
        $archivo = UploadedFile::fake()->createWithContent(
            'actual.sql',
            "DROP TABLE IF EXISTS `user_menu_access`;\nCREATE TABLE `user_menu_access` (user_id INTEGER, menu_key TEXT);"
        );

        $this->actingAs($admin)->post(route('copias.restaurar.post'), ['archivo' => $archivo])
            ->assertRedirect(route('copias.restaurar'))
            ->assertSessionHas('success', 'Copia de seguridad restaurada correctamente.');
    }
}
