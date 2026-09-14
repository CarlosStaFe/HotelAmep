<?php

namespace Tests\Feature;

use App\Models\Habitacion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeders_create_required_data()
    {
        $this->artisan('db:seed')->assertOk();

        $this->assertGreaterThan(0, User::count());
        $this->assertGreaterThan(0, Habitacion::count());
    }
}
