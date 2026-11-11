<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->text('detalle')->change();
        });
    }

    public function down(): void
    {
        foreach (DB::table('reservas')->select('detalle')->cursor() as $reserva) {
            if (mb_strlen($reserva->detalle) > 255) {
                throw new RuntimeException('No se puede reducir el campo detalle: existen reservas con más de 255 caracteres.');
            }
        }

        Schema::table('reservas', function (Blueprint $table) {
            $table->string('detalle', 255)->change();
        });
    }
};
