<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->text('observacion')->nullable()->change();
        });
    }

    public function down(): void
    {
        foreach (DB::table('compras')->whereNotNull('observacion')->select('observacion')->cursor() as $compra) {
            if (mb_strlen($compra->observacion) > 255) {
                throw new RuntimeException('No se puede reducir la observación: existen compras con más de 255 caracteres.');
            }
        }

        Schema::table('compras', function (Blueprint $table) {
            $table->string('observacion', 255)->nullable()->change();
        });
    }
};
