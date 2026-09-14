<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('rubros')) {
            if (! $this->foreignKeyExists('rubros', 'rubros_ctrocosto_id_foreign')) {
                Schema::table('rubros', function (Blueprint $table) {
                    $table->foreign('ctrocosto_id')->references('id')->on('ctrocostos');
                });
            }

            return;
        }

        Schema::create('rubros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ctrocosto_id')->constrained('ctrocostos');
            $table->string('nombre', 255);
            $table->boolean('activo')->default(true);
            $table->string('observacion', 100);
            $table->dateTime('fecha_creacion');
            $table->dateTime('fecha_actualizacion');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rubros') && $this->foreignKeyExists('rubros', 'rubros_ctrocosto_id_foreign')) {
            Schema::table('rubros', function (Blueprint $table) {
                $table->dropForeign('rubros_ctrocosto_id_foreign');
            });
        }

        Schema::dropIfExists('rubros');
    }

    private function foreignKeyExists(string $table, string $foreignKey): bool
    {
        return DB::table('information_schema.table_constraints')
            ->where('constraint_schema', DB::getDatabaseName())
            ->where('table_name', $table)
            ->where('constraint_name', $foreignKey)
            ->where('constraint_type', 'FOREIGN KEY')
            ->exists();
    }
};
