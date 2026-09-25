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
        $barman = Role::create(['name' => 'Barman']);
        $cocinero = Role::create(['name' => 'Cocinero']);
        $mucama = Role::create(['name' => 'Mucama']);
        $moza = Role::create(['name' => 'Moza']);
        $gerente = Role::create(['name' => 'Gerente']);
        $conserje = Role::create(['name' => 'Conserje']);
        $administrativo = Role::create(['name' => 'Administrativo']);
        $turismo = Role::create(['name' => 'Turismo']);

        Permission::create(['name' => 'admin.home'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);

        Permission::create(['name' => 'admin.ctrocostos.index'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);
        Permission::create(['name' => 'admin.ctrocostos.create'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);
        Permission::create(['name' => 'admin.ctrocostos.edit'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);
        Permission::create(['name' => 'admin.ctrocostos.delete'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);

        Permission::create(['name' => 'admin.rubros.index'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);
        Permission::create(['name' => 'admin.rubros.create'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);
        Permission::create(['name' => 'admin.rubros.edit'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);
        Permission::create(['name' => 'admin.rubros.delete'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);

        Permission::create(['name' => 'admin.articulos.index'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);
        Permission::create(['name' => 'admin.articulos.create'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);
        Permission::create(['name' => 'admin.articulos.edit'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);
        Permission::create(['name' => 'admin.articulos.delete'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);

        Permission::create(['name' => 'admin.proveedores.index'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);
        Permission::create(['name' => 'admin.proveedores.create'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);
        Permission::create(['name' => 'admin.proveedores.edit'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);
        Permission::create(['name' => 'admin.proveedores.delete'])->syncRoles([$admin, $barman, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo, $turismo]);
    }
}
