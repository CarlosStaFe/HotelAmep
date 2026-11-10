<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class CopiaSeguridadTest extends TestCase
{
    use RefreshDatabase;

    public function test_backup_pages_list_download_and_delete_copies(): void
    {
        $user = User::factory()->create();
        $directorio = storage_path('app/backups');
        File::ensureDirectoryExists($directorio);
        $nombre = 'backup_test_prueba.sql';
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
}
