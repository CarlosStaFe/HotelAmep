<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('calendarios')->select('color', 'textColor', 'estado')->cursor() as $evento) {
            foreach (['color', 'textColor', 'estado'] as $campo) {
                if ($evento->$campo !== null && mb_strlen($evento->$campo) > 50) {
                    throw new RuntimeException("No se puede reducir el campo {$campo}: existen valores de más de 50 caracteres.");
                }
            }
        }

        Schema::table('calendarios', function (Blueprint $table) {
            $table->string('color', 50)->nullable()->default('#0CB371')->change();
            $table->string('textColor', 50)->nullable()->default('#000000')->change();
            $table->string('estado', 50)->default('Disponible')->change();
        });
    }

    public function down(): void
    {
        Schema::table('calendarios', function (Blueprint $table) {
            $table->string('color', 255)->nullable()->default('#0CB371')->change();
            $table->string('textColor', 255)->nullable()->default('#000000')->change();
            $table->string('estado', 255)->default('Disponible')->change();
        });
    }
};
