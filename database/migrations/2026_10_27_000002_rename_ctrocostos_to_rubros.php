<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ctrocostos')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            Schema::rename('ctrocostos', 'rubros');

            if (Schema::hasColumn('subrubros', 'ctrocosto_id')) {
                Schema::table('subrubros', function (Blueprint $table) {
                    $table->renameColumn('ctrocosto_id', 'rubro_id');
                });
            }

            return;
        }

        if (Schema::hasColumn('subrubros', 'ctrocosto_id')) {
            Schema::table('subrubros', function (Blueprint $table) {
                $table->dropForeign(['ctrocosto_id']);
            });
        }

        Schema::rename('ctrocostos', 'rubros');

        if (Schema::hasColumn('subrubros', 'ctrocosto_id')) {
            Schema::table('subrubros', function (Blueprint $table) {
                $table->renameColumn('ctrocosto_id', 'rubro_id');
            });

            Schema::table('subrubros', function (Blueprint $table) {
                $table->foreign('rubro_id')->references('id')->on('rubros');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('rubros')) {
            return;
        }

        if (DB::getDriverName() === 'sqlite') {
            if (Schema::hasColumn('subrubros', 'rubro_id')) {
                Schema::table('subrubros', function (Blueprint $table) {
                    $table->renameColumn('rubro_id', 'ctrocosto_id');
                });
            }

            Schema::rename('rubros', 'ctrocostos');

            return;
        }

        if (Schema::hasColumn('subrubros', 'rubro_id')) {
            Schema::table('subrubros', function (Blueprint $table) {
                $table->dropForeign(['rubro_id']);
            });

            Schema::table('subrubros', function (Blueprint $table) {
                $table->renameColumn('rubro_id', 'ctrocosto_id');
            });
        }

        Schema::rename('rubros', 'ctrocostos');

        if (Schema::hasColumn('subrubros', 'ctrocosto_id')) {
            Schema::table('subrubros', function (Blueprint $table) {
                $table->foreign('ctrocosto_id')->references('id')->on('ctrocostos');
            });
        }
    }
};
