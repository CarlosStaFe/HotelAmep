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
        Role::findOrCreate('Administrador');
        Role::findOrCreate('Auditor');
        Role::findOrCreate('Barman');
        Role::findOrCreate('Cocinero');
        Role::findOrCreate('Contador');
        Role::findOrCreate('Gerente');
        Role::findOrCreate('Moza');
        Role::findOrCreate('Mucama');
        Role::findOrCreate('Recepcion');
        Role::findOrCreate('Turismo');

    }
}
