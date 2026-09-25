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
        Schema::create('pasajeros', function (Blueprint $table) {
            $table->id();
            $table->string('tipo_documento', 3);
            $table->string('documento', 8);
            $table->string('apelynombre', 100);
            $table->date('fecha_nacimiento')->nullable();
            $table->string('telefono', 50);
            $table->string('email', 100)->nullable();
            $table->string('direccion', 100);
            $table->foreignId('cod_postal_id')->constrained('localidades');
            $table->foreignId('habitacion_id')->nullable();
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pasajeros');
    }
};
