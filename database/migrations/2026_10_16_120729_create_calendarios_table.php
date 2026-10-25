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
        Schema::create('calendarios', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start');
            $table->dateTime('end');
            $table->string('color')->nullable()->default('#0CB371');
            $table->string('textColor')->nullable()->default('#000000');
            $table->string('estado')->default('Disponible');
            $table->foreignId('habitacion_id')->constrained('habitaciones');
            $table->foreignId('pasajero_id')->nullable()->constrained('pasajeros');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calendarios');
    }
};
