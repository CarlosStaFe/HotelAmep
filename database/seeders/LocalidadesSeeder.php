<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LocalidadesSeeder extends Seeder
{
    public function run()
    {
        $path = storage_path('localidades.json');

        if (!File::exists($path)) {
            $path = storage_path('app/localidades.json');
        }

        if (!File::exists($path)) {
            throw new \RuntimeException('No se encontro localidades.json en storage/ ni en storage/app/.');
        }

        $json = File::get($path);

        $localidades = json_decode($json, true);

        if (!is_array($localidades) || empty($localidades)) {
            throw new \RuntimeException('El archivo localidades.json no contiene un arreglo valido.');
        }

        $columns = ['id', 'id_prov', 'provincia', 'id_local', 'localidad', 'cod_postal'];

        $chunks = array_chunk($localidades, 500);

        foreach ($chunks as $chunk) {
            DB::table('localidades')->upsert($chunk, ['id'], $columns);
        }
    }
}

// php artisan db:seed --class=LocalidadesSeeder