<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UsuariosConectadosTest extends TestCase
{
    use RefreshDatabase;
    use \Tests\WithFullMenuAccess;

    public function test_muestra_sesiones_no_vencidas_con_su_usuario_y_ip(): void
    {
        config(['session.driver' => 'database', 'session.lifetime' => 120]);
        $this->travelTo(Carbon::parse('2026-11-24 20:00:00'));
        $usuario = User::factory()->create(['name' => 'Operador conectado']);
        $sesiones = [
            ['activa1', $usuario->id, '192.0.2.1', now()->subMinutes(1)->timestamp],
            ['activa2', $usuario->id, '2001:db8::1', now()->subMinutes(120)->timestamp],
            ['vencida', $usuario->id, '192.0.2.2', now()->subMinutes(120)->subSecond()->timestamp],
            ['invitado', null, '192.0.2.3', now()->timestamp],
            ['huerfana', 999999, '192.0.2.4', now()->timestamp],
        ];
        foreach ($sesiones as [$id, $userId, $ip, $actividad]) {
            DB::table('sessions')->insert([
                'id' => $id,
                'user_id' => $userId,
                'ip_address' => $ip,
                'payload' => 'informacion privada',
                'last_activity' => $actividad,
            ]);
        }

        $this->actingAs($usuario)->get(route('usuarios.conectados'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertViewHas('sesiones', fn ($sesiones) => $sesiones->count() === 2)
            ->assertSee('Operador conectado')
            ->assertSee('192.0.2.1')
            ->assertSee('2001:db8::1')
            ->assertSee('24/11/2026 19:59:00')
            ->assertDontSee('192.0.2.2')
            ->assertDontSee('192.0.2.3')
            ->assertDontSee('192.0.2.4')
            ->assertDontSee('informacion privada');
    }

    public function test_requiere_autenticacion(): void
    {
        $this->get(route('usuarios.conectados'))->assertRedirect(route('login'));
    }

    public function test_una_sesion_real_aparece_tras_iniciar_y_desaparece_al_cerrar_sesion(): void
    {
        config(['session.driver' => 'database']);
        $usuario = User::factory()->create(['name' => 'Usuario de prueba', 'activo' => true]);
        DB::table('user_menu_access')->insert(['user_id' => $usuario->id, 'menu_key' => 'usuarios.conectados']);
        $login = $this->post(route('login'), ['email' => $usuario->email, 'password' => 'password'])
            ->assertRedirect('/home');
        $cookie = $login->getCookie(config('session.cookie'), false);
        $this->assertNotNull($cookie);
        $this->withUnencryptedCookie($cookie->getName(), $cookie->getValue());

        $this->assertDatabaseHas('sessions', ['user_id' => $usuario->id, 'ip_address' => '127.0.0.1']);
        $this->get(route('usuarios.conectados'))->assertOk()
            ->assertSee('Usuario de prueba')->assertSee('127.0.0.1');

        $this->post(route('logout'))->assertRedirect('/');
        $this->assertDatabaseMissing('sessions', ['user_id' => $usuario->id]);
    }

    public function test_informa_cuando_el_driver_no_es_de_base_de_datos(): void
    {
        config(['session.driver' => 'array']);

        $this->actingAs(User::factory()->create())->get(route('usuarios.conectados'))
            ->assertStatus(503)
            ->assertSee('El listado requiere que las sesiones se almacenen en la base de datos.');
    }
}
