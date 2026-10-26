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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->string('razonSocial', 100);
            $table->string('cuit', 11);
            $table->string('tipoIva', 50);
            $table->string('domicilio', 100)->nullable();
            $table->foreignId('cod_postal_id')->nullable()->constrained('localidades');
            $table->string('telefono', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('echeck')->default(false);
            $table->string('cbu', 22)->nullable();
            $table->string('cvu', 22)->nullable();
            $table->string('alias', 100)->nullable();
            $table->string('dias', 100)->nullable();
            $table->boolean('activo')->default(true);
            $table->string('observacion', 100)->nullable();
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
