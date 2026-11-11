<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = ['habitacion_id', 'pasajero_id', 'fecha_inicio', 'fecha_fin', 'detalle', 'estado'];
        $missing = array_filter($columns, fn ($column) => ! Schema::hasColumn('reservas', $column));

        if ($missing === []) {
            return;
        }

        if (DB::table('reservas')->exists()) {
            throw new RuntimeException('No se pueden agregar los campos obligatorios a reservas mientras existan registros; complete sus datos antes de migrar.');
        }

        Schema::table('reservas', function (Blueprint $table) use ($missing) {
            if (in_array('habitacion_id', $missing, true)) {
                $table->foreignId('habitacion_id')->constrained('habitaciones');
            }
            if (in_array('pasajero_id', $missing, true)) {
                $table->foreignId('pasajero_id')->constrained('pasajeros');
            }
            if (in_array('fecha_inicio', $missing, true)) {
                $table->date('fecha_inicio');
            }
            if (in_array('fecha_fin', $missing, true)) {
                $table->date('fecha_fin');
            }
            if (in_array('detalle', $missing, true)) {
                $table->string('detalle', 255);
            }
            if (in_array('estado', $missing, true)) {
                $table->string('estado', 20);
            }
        });
    }

    public function down(): void
    {
        // Los campos pueden pertenecer a la migración original; no se eliminan al revertir.
    }
};
