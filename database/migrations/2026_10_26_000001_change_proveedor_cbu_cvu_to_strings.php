<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->string('cbu', 22)->nullable()->change();
            $table->string('cvu', 22)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('proveedores', function (Blueprint $table) {
            $table->integer('cbu')->nullable()->change();
            $table->integer('cvu')->nullable()->change();
        });
    }
};
