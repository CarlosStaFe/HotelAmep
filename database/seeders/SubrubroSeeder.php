<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SubrubroSeeder extends Seeder
{
    public function run()
    {
        $path = storage_path('subrubros.json');

        if (!File::exists($path)) {
            $path = storage_path('app/subrubros.json');
        }

        if (!File::exists($path)) {
            throw new \RuntimeException('No se encontro subrubros.json en storage/ ni en storage/app/.');
        }

        $json = File::get($path);

        $subrubros = json_decode($json, true);

        if (!is_array($subrubros) || empty($subrubros)) {
            throw new \RuntimeException('El archivo subrubros.json no contiene un arreglo valido.');
        }

        $columns = ['id', 'rubro_id', 'nombre', 'activo', 'observacion', 'fecha_creacion', 'fecha_actualizacion'];

        $chunks = array_chunk($subrubros, 500);

        foreach ($chunks as $chunk) {
            DB::table('subrubros')->upsert($chunk, ['id'], $columns);
        }
    }
}

// php artisan db:seed --class=SubrubroSeeder