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
            $table->decimal('precio', 12, 2)->change();
        });
    }

    public function down(): void
    {
        if (DB::table('movimientos')->where('precio', '>', '99999999.99')
            ->orWhere('precio', '<', '-99999999.99')->exists()) {
            throw new RuntimeException('No se puede reducir la precisión: existen precios fuera del límite anterior.');
        }

        Schema::table('movimientos', function (Blueprint $table) {
            $table->decimal('precio', 10, 2)->change();
        });
    }
};
