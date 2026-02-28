<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Refatoracao de autenticacao: consolida campos legados de usuario na tabela users
     * para eliminar dependencias da tabela legada de usuarios no runtime.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            if (! Schema::hasColumn('users', 'USU_CODIGO')) {
                $table->unsignedInteger('USU_CODIGO')->nullable()->after('id');
                $table->unique('USU_CODIGO', 'users_usu_codigo_unique');
            }

            if (! Schema::hasColumn('users', 'cpf')) {
                $table->string('cpf', 11)->after('email');
                $table->unique('cpf', 'users_cpf_unique');
            }

            if (! Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['ATIVO', 'INATIVO'])->default('ATIVO')->after('cpf');
                $table->index('status', 'users_status_index');
            }

            if (! Schema::hasColumn('users', 'profile_photo_path')) {
                $table->string('profile_photo_path', 255)->nullable()->after('status');
            }

            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 30)->nullable()->after('profile_photo_path');
            }

            if (! Schema::hasColumn('users', 'username')) {
                $table->string('username', 40)->nullable()->after('phone');
                $table->unique('username', 'users_username_unique');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) {
                $table->dropUnique('users_username_unique');
                $table->dropColumn('username');
            }

            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }

            if (Schema::hasColumn('users', 'profile_photo_path')) {
                $table->dropColumn('profile_photo_path');
            }

            if (Schema::hasColumn('users', 'status')) {
                $table->dropIndex('users_status_index');
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('users', 'cpf')) {
                $table->dropUnique('users_cpf_unique');
                $table->dropColumn('cpf');
            }

            if (Schema::hasColumn('users', 'USU_CODIGO')) {
                $table->dropUnique('users_usu_codigo_unique');
                $table->dropColumn('USU_CODIGO');
            }
        });
    }
};
