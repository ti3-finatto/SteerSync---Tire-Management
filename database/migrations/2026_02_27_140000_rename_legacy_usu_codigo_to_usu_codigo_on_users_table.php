<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'legacy_usu_codigo') && ! Schema::hasColumn('users', 'USU_CODIGO')) {
            DB::statement('ALTER TABLE `users` RENAME COLUMN `legacy_usu_codigo` TO `USU_CODIGO`');

            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_legacy_usu_codigo_unique');
                $table->unique('USU_CODIGO', 'users_usu_codigo_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'USU_CODIGO') && ! Schema::hasColumn('users', 'legacy_usu_codigo')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_usu_codigo_unique');
            });

            DB::statement('ALTER TABLE `users` RENAME COLUMN `USU_CODIGO` TO `legacy_usu_codigo`');

            Schema::table('users', function (Blueprint $table) {
                $table->unique('legacy_usu_codigo', 'users_legacy_usu_codigo_unique');
            });
        }
    }
};
