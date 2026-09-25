<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UpdateUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_detail_route_is_accessible_with_get(): void
    {
        Role::create(['name' => 'Administrador']);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'activo' => true,
        ]);
        $admin->assignRole('Administrador');

        $user = User::factory()->create([
            'name' => 'User Detail',
            'email' => 'detail@example.com',
            'telefono' => '123456',
            'activo' => true,
        ]);

        $response = $this->actingAs($admin)
            ->get('/admin/usuarios/' . $user->id);

        $response->assertOk();
        $response->assertSeeText('User Detail');
    }

    public function test_user_can_be_updated(): void
    {
        Role::create(['name' => 'Administrador']);
        Role::create(['name' => 'User']);

        $admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'activo' => true,
        ]);
        $admin->assignRole('Administrador');

        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@example.com',
            'telefono' => '123',
            'activo' => true,
        ]);
        $user->assignRole('User');

        $response = $this->actingAs($admin)
            ->from('/admin/usuarios/index')
            ->put('/admin/usuarios/' . $user->id, [
                'nombre' => 'Nuevo Nombre',
                'email' => 'nuevo@example.com',
                'telefono' => '456789',
                'role' => 'User',
                'activo' => false,
            ]);

        $response->assertRedirect('/admin/usuarios/index');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Nuevo Nombre',
            'email' => 'nuevo@example.com',
            'telefono' => '456789',
            'activo' => false,
        ]);
        $this->assertTrue($user->fresh()->hasRole('User'));
    }
}
