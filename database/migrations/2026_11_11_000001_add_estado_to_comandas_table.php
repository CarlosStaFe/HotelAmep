<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('comandas', 'estado')) {
            Schema::table('comandas', function (Blueprint $table) {
                $table->string('estado', 20)->default('Abierta');
            });
        }
    }

    public function down(): void
    {
        // La columna también puede pertenecer a la migración original.
    }
};
