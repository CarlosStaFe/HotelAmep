<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comandas', function (Blueprint $table) {
            $table->string('moza', 16)->nullable();
        });
    }

    public function down(): void
    {
        if (DB::table('comandas')->whereNotNull('moza')->exists()) {
            throw new RuntimeException('No se puede quitar la moza mientras existan comandas que la informan.');
        }

        Schema::table('comandas', function (Blueprint $table) {
            $table->dropColumn('moza');
        });
    }
};
