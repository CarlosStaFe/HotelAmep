<?php

namespace Database\Seeders;

use App\Models\Habitacion;
use Illuminate\Database\Seeder;

class HabitacionSeeder extends Seeder
{
    public function run(): void
    {
        $habitaciones = [
            ['numero' => '01', 'tipo' => 'Casero    ', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 0, 'simple' => 0, 'piso' => 'PB', 'vista' => 'Jardín', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => ''],
            ['numero' => '02', 'tipo' => 'Consejo   ', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 1, 'simple' => 1, 'piso' => 'PB', 'vista' => 'Jardín', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => ''],
            ['numero' => '03', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 0, 'simple' => 3, 'piso' => 'PB', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'SI', 'observacion' => ''],
            ['numero' => '04', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 1, 'simple' => 1, 'piso' => 'PB', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'SI', 'observacion' => ''],
            ['numero' => '05', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 2, 'matri' => 0, 'simple' => 2, 'piso' => 'PB', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => ''],
            ['numero' => '06', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 1, 'simple' => 1, 'piso' => 'PB', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '07', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 2, 'matri' => 1, 'simple' => 0, 'piso' => 'PB', 'vista' => 'Jardín', 'aire' => 'SI', 'cuna' => 'SI', 'observacion' => '' ],
            ['numero' => '08', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 1, 'simple' => 1, 'piso' => 'PB', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '09', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 2, 'matri' => 1, 'simple' => 0, 'piso' => 'PB', 'vista' => 'Cerro ', 'aire' => 'SI', 'cuna' => 'SI', 'observacion' => '' ],
            ['numero' => '10', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 1, 'simple' => 1, 'piso' => 'PB', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'SI', 'observacion' => '' ],
            ['numero' => '11', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 1, 'simple' => 0, 'piso' => 'PB', 'vista' => 'Cerro ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '12', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 0, 'simple' => 2, 'piso' => 'PB', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '13', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 0, 'simple' => 2, 'piso' => 'PB', 'vista' => 'Cerro ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '14', 'tipo' => 'Dpto.14   ', 'estado' => 'Disponible', 'plazas' => 5, 'matri' => 1, 'simple' => 3, 'piso' => 'PA', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'SI', 'observacion' => '' ],
            ['numero' => '15', 'tipo' => 'Habitación', 'estado' => '-         ', 'plazas' => 0, 'matri' => 0, 'simple' => 0, 'piso' => 'PA', 'vista' => '-     ', 'aire' => '- ', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '16', 'tipo' => 'Dpto.16   ', 'estado' => 'Disponible', 'plazas' => 5, 'matri' => 1, 'simple' => 3, 'piso' => 'PA', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'SI', 'observacion' => '' ],
            ['numero' => '17', 'tipo' => 'Habitación', 'estado' => '-         ', 'plazas' => 0, 'matri' => 0, 'simple' => 0, 'piso' => 'PA', 'vista' => '-     ', 'aire' => '- ', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '18', 'tipo' => 'Dpto.18   ', 'estado' => 'Disponible', 'plazas' => 5, 'matri' => 1, 'simple' => 3, 'piso' => 'PA', 'vista' => 'Jardín', 'aire' => 'SI', 'cuna' => 'SI', 'observacion' => '' ],
            ['numero' => '19', 'tipo' => 'Habitación', 'estado' => '-         ', 'plazas' => 0, 'matri' => 0, 'simple' => 0, 'piso' => 'PA', 'vista' => '-     ', 'aire' => '- ', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '20', 'tipo' => 'Depósito  ', 'estado' => '-         ', 'plazas' => 0, 'matri' => 0, 'simple' => 2, 'piso' => 'PA', 'vista' => '-     ', 'aire' => 'NO', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '21', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 2, 'matri' => 1, 'simple' => 0, 'piso' => 'PA', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '22', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 6, 'matri' => 1, 'simple' => 4, 'piso' => 'PA', 'vista' => 'Cerro ', 'aire' => 'SI', 'cuna' => 'SI', 'observacion' => '' ],
            ['numero' => '23', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 2, 'matri' => 1, 'simple' => 0, 'piso' => 'PA', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '24', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 2, 'matri' => 1, 'simple' => 0, 'piso' => 'PA', 'vista' => 'Cerro ', 'aire' => 'SI', 'cuna' => 'SI', 'observacion' => '' ],
            ['numero' => '25', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 2, 'matri' => 1, 'simple' => 0, 'piso' => 'PA', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '26', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 1, 'simple' => 1, 'piso' => 'PA', 'vista' => 'Cerro ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '27', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 2, 'matri' => 0, 'simple' => 2, 'piso' => 'PA', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '28', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 1, 'simple' => 1, 'piso' => 'PA', 'vista' => 'Cerro ', 'aire' => 'SI', 'cuna' => 'SI', 'observacion' => '' ],
            ['numero' => '29', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 2, 'matri' => 1, 'simple' => 0, 'piso' => 'PA', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '30', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 1, 'simple' => 1, 'piso' => 'PA', 'vista' => 'Cerro ', 'aire' => 'SI', 'cuna' => 'SI', 'observacion' => '' ],
            ['numero' => '31', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 2, 'matri' => 0, 'simple' => 2, 'piso' => 'PA', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '32', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 2, 'matri' => 0, 'simple' => 2, 'piso' => 'PA', 'vista' => 'Cerro ', 'aire' => 'NO', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '33', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 2, 'matri' => 0, 'simple' => 2, 'piso' => 'PA', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '34', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 0, 'simple' => 3, 'piso' => 'PA', 'vista' => 'Cerro ', 'aire' => 'NO', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '35', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 0, 'simple' => 3, 'piso' => 'PA', 'vista' => 'Calle ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '36', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 0, 'simple' => 3, 'piso' => 'AN', 'vista' => 'Patio ', 'aire' => 'SI', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '37', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 4, 'matri' => 1, 'simple' => 2, 'piso' => 'AN', 'vista' => 'Pileta', 'aire' => 'NO', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '38', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 4, 'matri' => 1, 'simple' => 2, 'piso' => 'AN', 'vista' => 'Patio ', 'aire' => 'NO', 'cuna' => 'SI', 'observacion' => '' ],
            ['numero' => '39', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 0, 'simple' => 3, 'piso' => 'AN', 'vista' => 'Pileta', 'aire' => 'NO', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '40', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 1, 'simple' => 1, 'piso' => 'AN', 'vista' => 'Pileta', 'aire' => 'NO', 'cuna' => 'NO', 'observacion' => '' ],
            ['numero' => '41', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 2, 'matri' => 1, 'simple' => 0, 'piso' => 'AN', 'vista' => 'Pileta', 'aire' => 'NO', 'cuna' => 'SI', 'observacion' => '' ],
            ['numero' => '42', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 3, 'matri' => 1, 'simple' => 1, 'piso' => 'AN', 'vista' => 'Interno', 'aire' => 'NO', 'cuna' => 'SI', 'observacion' => '' ],
            ['numero' => '43', 'tipo' => 'Habitación', 'estado' => 'Disponible', 'plazas' => 2, 'matri' => 1, 'simple' => 0, 'piso' => 'AN', 'vista' => 'Pileta', 'aire' => 'NO', 'cuna' => 'NO', 'observacion' => '' ],
        ];

        foreach ($habitaciones as $habitacion) {
            Habitacion::updateOrCreate(
                ['numero' => $habitacion['numero']],
                $habitacion
            );
        }
    }
}
