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
        Schema::create('detallecpras', function (Blueprint $table) {
            $table->id();
            $table->string('compra_id');
            $table->string('articulo_id');
            $table->decimal('cantidad', 15, 3);
            $table->decimal('precio', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->date('vence');
            $table->string('punto',20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detallecpras');
    }
};
