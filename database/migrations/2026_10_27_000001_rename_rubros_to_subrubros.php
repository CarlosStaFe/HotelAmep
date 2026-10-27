<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('rubros')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            Schema::rename('rubros', 'subrubros');

            if (Schema::hasColumn('articulos', 'rubro_id')) {
                Schema::table('articulos', function (Blueprint $table) {
                    $table->renameColumn('rubro_id', 'subrubro_id');
                });
            }

            return;
        }

        if (Schema::hasColumn('articulos', 'rubro_id')) {
            Schema::table('articulos', function (Blueprint $table) {
                $table->dropForeign(['rubro_id']);
            });
        }

        Schema::table('rubros', function (Blueprint $table) {
            $table->dropForeign(['ctrocosto_id']);
        });

        Schema::rename('rubros', 'subrubros');

        if (Schema::hasColumn('articulos', 'rubro_id')) {
            Schema::table('articulos', function (Blueprint $table) {
                $table->renameColumn('rubro_id', 'subrubro_id');
            });

            Schema::table('articulos', function (Blueprint $table) {
                $table->foreign('subrubro_id')->references('id')->on('subrubros');
            });
        }

        Schema::table('subrubros', function (Blueprint $table) {
            $table->foreign('ctrocosto_id')->references('id')->on('ctrocostos');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('subrubros')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            Schema::rename('subrubros', 'rubros');

            if (Schema::hasColumn('articulos', 'subrubro_id')) {
                Schema::table('articulos', function (Blueprint $table) {
                    $table->renameColumn('subrubro_id', 'rubro_id');
                });
            }

            return;
        }

        if (Schema::hasColumn('articulos', 'subrubro_id')) {
            Schema::table('articulos', function (Blueprint $table) {
                $table->dropForeign(['subrubro_id']);
            });
        }

        Schema::table('subrubros', function (Blueprint $table) {
            $table->dropForeign(['ctrocosto_id']);
        });

        Schema::rename('subrubros', 'rubros');

        if (Schema::hasColumn('articulos', 'subrubro_id')) {
            Schema::table('articulos', function (Blueprint $table) {
                $table->renameColumn('subrubro_id', 'rubro_id');
            });

            Schema::table('articulos', function (Blueprint $table) {
                $table->foreign('rubro_id')->references('id')->on('rubros');
            });
        }

        Schema::table('rubros', function (Blueprint $table) {
            $table->foreign('ctrocosto_id')->references('id')->on('ctrocostos');
        });
    }
};
