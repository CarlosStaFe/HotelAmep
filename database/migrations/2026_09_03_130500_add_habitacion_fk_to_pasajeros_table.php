<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pasajeros', function (Blueprint $table) {
            $table->foreign('habitacion_id')->references('id')->on('habitaciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pasajeros', function (Blueprint $table) {
            $table->dropForeign(['habitacion_id']);
        });
    }
};
