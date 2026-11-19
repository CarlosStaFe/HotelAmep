<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ProveedorSeeder extends Seeder
{
    public function run()
    {
        $path = storage_path('proveedores.json');

        if (!File::exists($path)) {
            $path = storage_path('app/proveedores.json');
        }

        if (!File::exists($path)) {
            throw new \RuntimeException('No se encontro proveedores.json en storage/ ni en storage/app/.');
        }

        $json = File::get($path);

        $proveedores = json_decode($json, true);

        if (!is_array($proveedores) || empty($proveedores)) {
            throw new \RuntimeException('El archivo proveedores.json no contiene un arreglo valido.');
        }

        $columns = ['id', 'nombre', 'razonSocial', 'cuit', 'tipoIva', 'domicilio', 'cod_postal_id', 'telefono', 'email', 'echeck', 'cbu', 'cvu', 'alias', 'dias', 'activo', 'observacion', 'fecha_creacion', 'fecha_actualizacion'];

        $chunks = array_chunk($proveedores, 500);

        foreach ($chunks as $chunk) {
            DB::table('proveedores')->upsert($chunk, ['id'], $columns);
        }
    }
}

// php artisan db:seed --class=ProveedorSeeder