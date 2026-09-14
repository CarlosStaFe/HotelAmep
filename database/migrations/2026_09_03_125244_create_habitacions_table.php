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
        Schema::create('habitaciones', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 10)->unique();
            $table->string('tipo', 50);
            $table->string('estado', 20);
            $table->string('observacion', 100)->nullable();
            $table->dateTime('fecha_creacion')->nullable()->useCurrent();
            $table->dateTime('fecha_actualizacion')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('habitaciones');
    }
};
