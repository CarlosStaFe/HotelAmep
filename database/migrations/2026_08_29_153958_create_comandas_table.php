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
        Schema::create('comandas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')->constrained('articulos');
            $table->foreignId('pasajero_id');
            $table->dateTime('fecha');
            $table->string('vendedor', 20);
            $table->string('habitacion', 10);
            $table->decimal('cantidad', 12, 3);
            $table->decimal('total', 12, 2);
            $table->string('observacion', 100)->nullable();
            $table->string('usuario', 50);
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comandas');
    }
};
