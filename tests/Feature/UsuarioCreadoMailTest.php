<?php

namespace Tests\Feature;

use App\Mail\UsuarioCreadoMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UsuarioCreadoMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_receives_password_and_login_link(): void
    {
        Mail::fake();

        $administrador = User::factory()->create();
        Role::create(['name' => 'Recepcionista', 'guard_name' => 'web']);

        $password = 'clave-segura-123';
        $response = $this->actingAs($administrador)
            ->post(route('usuarios.store'), [
                'nombre' => 'Usuario Nuevo',
                'email' => 'nuevo@example.com',
                'telefono' => '123456789',
                'role' => 'Recepcionista',
                'activo' => '1',
                'password' => $password,
                'password_confirmation' => $password,
            ]);

        $response->assertRedirect(route('usuarios.index'));
        $this->assertDatabaseHas('users', ['email' => 'nuevo@example.com']);

        $usuario = User::where('email', 'nuevo@example.com')->firstOrFail();
        $this->assertTrue(Hash::check($password, $usuario->password));

        Mail::assertSent(UsuarioCreadoMail::class, function (UsuarioCreadoMail $mail) use ($usuario) {
            $contenido = $mail->render();

            return $mail->hasTo($usuario->email)
                && $mail->password === 'clave-segura-123'
                && str_contains($contenido, $mail->password)
                && str_contains($contenido, config('app.url'));
        });

        Auth::logout();

        $this->post(route('login'), [
            'email' => $usuario->email,
            'password' => $password,
        ])->assertRedirect('/home');

        $this->assertAuthenticatedAs($usuario);
    }

    public function test_updated_password_is_emailed_to_users_current_email(): void
    {
        Mail::fake();

        $administrador = User::factory()->create();
        $usuario = User::factory()->create([
            'email' => 'anterior@example.com',
            'telefono' => '123456789',
            'password' => 'password-original',
        ]);
        Role::create(['name' => 'Recepcionista', 'guard_name' => 'web']);

        $password = 'nueva-clave-segura-123';
        $response = $this->actingAs($administrador)
            ->put(route('usuarios.update', $usuario->id), [
                'nombre' => $usuario->name,
                'email' => 'actualizado@example.com',
                'telefono' => $usuario->telefono,
                'role' => 'Recepcionista',
                'activo' => '1',
                'password' => $password,
                'password_confirmation' => $password,
            ]);

        $response->assertRedirect(route('usuarios.index'));
        $usuario->refresh();
        $this->assertSame('actualizado@example.com', $usuario->email);
        $this->assertTrue(Hash::check($password, $usuario->password));

        Mail::assertSent(UsuarioCreadoMail::class, function (UsuarioCreadoMail $mail) use ($usuario, $password) {
            $contenido = $mail->render();

            return $mail->hasTo($usuario->email)
                && $mail->password === $password
                && str_contains($contenido, config('app.url'));
        });
    }

    public function test_user_update_without_password_does_not_send_password_email(): void
    {
        Mail::fake();

        $administrador = User::factory()->create();
        $usuario = User::factory()->create(['telefono' => '123456789']);
        Role::create(['name' => 'Recepcionista', 'guard_name' => 'web']);

        $response = $this->actingAs($administrador)
            ->put(route('usuarios.update', $usuario->id), [
                'nombre' => $usuario->name,
                'email' => $usuario->email,
                'telefono' => $usuario->telefono,
                'role' => 'Recepcionista',
                'activo' => '1',
                'password' => '',
                'password_confirmation' => '',
            ]);

        $response->assertRedirect(route('usuarios.index'));
        Mail::assertNothingSent();
    }
}
