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
        Schema::create('stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('articulo_id')->constrained('articulos');
            $table->decimal('cantidad', 12, 3)->default(0);
            $table->decimal('precioCto', 12, 2)->default(0);
            $table->decimal('precioVta', 12, 2)->default(0);
            $table->string('punto', 20)->nullable();
            $table->date('vence')->nullable();
            $table->string('observacion', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
