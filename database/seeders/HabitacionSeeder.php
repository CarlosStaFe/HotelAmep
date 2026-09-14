<?php

namespace Database\Seeders;

use App\Models\Habitacion;
use Illuminate\Database\Seeder;

class HabitacionSeeder extends Seeder
{
    public function run(): void
    {
        $habitaciones = [
            ['numero' => '101', 'tipo' => 'Simple', 'estado' => 'Disponible', 'observacion' => 'Frente al patio'],
            ['numero' => '102', 'tipo' => 'Doble', 'estado' => 'Ocupada', 'observacion' => 'Vista al jardín'],
            ['numero' => '103', 'tipo' => 'Triple', 'estado' => 'Disponible', 'observacion' => 'Con baño privado'],
        ];

        foreach ($habitaciones as $habitacion) {
            Habitacion::updateOrCreate(
                ['numero' => $habitacion['numero']],
                [
                    'tipo' => $habitacion['tipo'],
                    'estado' => $habitacion['estado'],
                    'observacion' => $habitacion['observacion'],
                ]
            );
        }
    }
}
