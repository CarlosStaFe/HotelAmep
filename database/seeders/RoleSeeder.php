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
        $user = Role::create(['name' => 'User']);
        $cocinero = Role::create(['name' => 'Cocinero']);
        $mucama = Role::create(['name' => 'Mucama']);
        $moza = Role::create(['name' => 'Moza']);
        $gerente = Role::create(['name' => 'Gerente']);
        $conserje = Role::create(['name' => 'Conserje']);
        $administrativo = Role::create(['name' => 'Administrativo']);

        Permission::create(['name' => 'admin.home'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);

        Permission::create(['name' => 'admin.ctrocostos.index'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);
        Permission::create(['name' => 'admin.ctrocostos.create'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);
        Permission::create(['name' => 'admin.ctrocostos.edit'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);
        Permission::create(['name' => 'admin.ctrocostos.delete'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);

        Permission::create(['name' => 'admin.rubros.index'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);
        Permission::create(['name' => 'admin.rubros.create'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);
        Permission::create(['name' => 'admin.rubros.edit'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);
        Permission::create(['name' => 'admin.rubros.delete'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);

        Permission::create(['name' => 'admin.articulos.index'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);
        Permission::create(['name' => 'admin.articulos.create'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);
        Permission::create(['name' => 'admin.articulos.edit'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);
        Permission::create(['name' => 'admin.articulos.delete'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);

        Permission::create(['name' => 'admin.proveedores.index'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);
        Permission::create(['name' => 'admin.proveedores.create'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);
        Permission::create(['name' => 'admin.proveedores.edit'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);
        Permission::create(['name' => 'admin.proveedores.delete'])->syncRoles([$admin, $user, $cocinero, $mucama, $moza, $gerente, $conserje, $administrativo]);


    }
}
