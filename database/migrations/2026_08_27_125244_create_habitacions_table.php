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
            $table->string('numero', 2)->unique();
            $table->string('tipo', 20);
            $table->string('estado', 20);
            $table->integer('plazas')->unsigned();
            $table->integer('matri')->unsigned();
            $table->integer('simple')->unsigned();           
            $table->string('piso', 10)->nullable();
            $table->string('vista', 20)->nullable();   
            $table->string('aire', 2)->nullable();
            $table->string('cuna', 2)->nullable();
            $table->string('toilette', 1)->nullable();
            $table->boolean('venta')->default(true);
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
