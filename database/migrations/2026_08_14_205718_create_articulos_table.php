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
        Schema::create('articulos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubro_id')->constrained('rubros');
            $table->string('nombre', 255);
            $table->decimal('precio', 12, 2);
            $table->decimal('stock', 12, 3)->default(0);
            $table->boolean('activo')->default(true);
            $table->boolean('comanda')->default(true);
            $table->string('observacion', 100);
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articulos');
    }
};
