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
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->string('proveedor_id');
            $table->date('fechaCpra');
            $table->string('tipoCpbte');
            $table->integer('prefijo');
            $table->integer('subfijo');
            $table->integer('dias');
            $table->decimal('total', 15, 2);
            $table->string('observacion')->nullable();
            $table->string('estado')->default('Pendiente');
            $table->string('usuario_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
