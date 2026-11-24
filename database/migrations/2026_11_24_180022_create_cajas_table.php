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
        Schema::create('cajas', function (Blueprint $table) {
            $table->id();
            $table->date('checkin');
            $table->date('checkout');
            $table->string('habitacion',10);
            $table->decimal('efectivo', 15, 2);
            $table->decimal('transferencia', 15, 2);
            $table->decimal('tarjeta', 15, 2);
            $table->decimal('total', 15, 2);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cajas');
    }
};
