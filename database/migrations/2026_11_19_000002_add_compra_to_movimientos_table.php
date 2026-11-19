<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimientos', function (Blueprint $table) {
            $table->decimal('cantidad', 12, 3)->change();
        });
    }

    public function down(): void
    {
        if (DB::table('movimientos')->where('cantidad', '>', '9999999.999')
            ->orWhere('cantidad', '<', '-9999999.999')->exists()) {
            throw new RuntimeException('No se puede reducir la precisión: existen cantidades fuera del límite anterior.');
        }

        Schema::table('movimientos', function (Blueprint $table) {
            $table->decimal('cantidad', 10, 3)->change();
        });
    }
};
