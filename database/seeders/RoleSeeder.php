<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = Role::create(['name' => 'Administrador']);
        $auditor = Role::create(['name' => 'Auditor']);
        $barman = Role::create(['name' => 'Barman']);
        $cocinero = Role::create(['name' => 'Cocinero']);
        $contador = Role::create(['name' => 'Contador']);
        $gerente = Role::create(['name' => 'Gerente']);
        $moza = Role::create(['name' => 'Moza']);
        $mucama = Role::create(['name' => 'Mucama']);
        $recepcion = Role::create(['name' => 'Recepcion']);
        $turismo = Role::create(['name' => 'Turismo']);

    }
}
