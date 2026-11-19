<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('detallecpras', function (Blueprint $table) {
            $table->date('vence')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (DB::table('detallecpras')->whereNull('vence')->exists()) {
            throw new RuntimeException('No se puede exigir vencimiento mientras existan detalles sin fecha.');
        }

        Schema::table('detallecpras', function (Blueprint $table) {
            $table->date('vence')->nullable(false)->change();
        });
    }
};
