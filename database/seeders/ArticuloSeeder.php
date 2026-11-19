<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ArticuloSeeder extends Seeder
{
    public function run()
    {
        $path = storage_path('articulos.json');

        if (!File::exists($path)) {
            $path = storage_path('app/articulos.json');
        }

        if (!File::exists($path)) {
            throw new \RuntimeException('No se encontro articulos.json en storage/ ni en storage/app/.');
        }

        $json = File::get($path);

        $articulos = json_decode($json, true);

        if (!is_array($articulos) || empty($articulos)) {
            throw new \RuntimeException('El archivo articulos.json no contiene un arreglo valido.');
        }

        $columns = ['id', 'subrubro_id', 'nombre', 'activo', 'comanda', 'vencimiento', 'observacion', 'fecha_creacion', 'fecha_actualizacion'];

        $chunks = array_chunk($articulos, 500);

        foreach ($chunks as $chunk) {
            DB::table('articulos')->upsert($chunk, ['id'], $columns);
        }
    }
}

// php artisan db:seed --class=ArticuloSeeder