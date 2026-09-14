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
        Schema::create('ctrocostos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 255);
            $table->string('tipo', 100);
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
        Schema::dropIfExists('ctrocostos');
    }
};
