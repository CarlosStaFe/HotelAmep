<?php

namespace Tests\Feature;

use App\Mail\UsuarioCreadoMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsuarioCreadoMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_receives_password_setup_and_login_links(): void
    {
        Mail::fake();

        $administrador = User::factory()->create();
        Role::create(['name' => 'Recepcionista', 'guard_name' => 'web']);

        $response = $this->actingAs($administrador)
            ->post(route('usuarios.store'), [
                'nombre' => 'Usuario Nuevo',
                'email' => 'nuevo@example.com',
                'telefono' => '123456789',
                'role' => 'Recepcionista',
                'activo' => '1',
            ]);

        $response->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('users', ['email' => 'nuevo@example.com']);

        $usuario = User::where('email', 'nuevo@example.com')->firstOrFail();

        Mail::assertSent(UsuarioCreadoMail::class, function (UsuarioCreadoMail $mail) use ($usuario) {
            $token = basename(parse_url($mail->urlRestablecer, PHP_URL_PATH));

            return $mail->hasTo($usuario->email)
                && Password::broker()->tokenExists($usuario, $token)
                && $mail->urlAcceso === route('login');
        });
    }
}
