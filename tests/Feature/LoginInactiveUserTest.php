<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginInactiveUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password123'),
            'activo' => false,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_sidebar_logout_submits_post_and_ends_the_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)
            ->get(route('admin.index'))
            ->assertOk()
            ->assertSeeInOrder([
                'id="sidebar-logout"',
                '<form action="'.route('logout').'" method="POST">',
                'name="_token"',
                'type="submit"',
                'Log Out',
            ], false);
        $this->post(route('logout'))->assertRedirect('/');
        $this->assertGuest();
        $this->get(route('admin.index'))->assertRedirect(route('login'));
    }
}
