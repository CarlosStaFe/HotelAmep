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
        Schema::create('movimientos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->useCurrent();
            $table->foreignId('articulo_id')->constrained('articulos');
            $table->foreignId('user_id')->constrained('users');
            $table->string('tipo', 20);
            $table->string('motivo', 20);
            $table->decimal('cantidad', 10, 3);
            $table->string('observacion',100)->nullable();
            $table->dateTime('fecha_creacion')->nullable()->useCurrent();
            $table->dateTime('fecha_actualizacion')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos');
    }
};
