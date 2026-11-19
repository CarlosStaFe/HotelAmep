<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RubroSeeder extends Seeder
{
    public function run()
    {
        $path = storage_path('rubros.json');

        if (!File::exists($path)) {
            $path = storage_path('app/rubros.json');
        }

        if (!File::exists($path)) {
            throw new \RuntimeException('No se encontro rubros.json en storage/ ni en storage/app/.');
        }

        $json = File::get($path);

        $rubros = json_decode($json, true);

        if (!is_array($rubros) || empty($rubros)) {
            throw new \RuntimeException('El archivo rubros.json no contiene un arreglo valido.');
        }

        $columns = ['id', 'nombre', 'tipo', 'observacion', 'fecha_creacion', 'fecha_actualizacion'];

        $chunks = array_chunk($rubros, 500);

        foreach ($chunks as $chunk) {
            DB::table('rubros')->upsert($chunk, ['id'], $columns);
        }
    }
}

// php artisan db:seed --class=RubroSeeder