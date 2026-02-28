<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('t_usuario') && ! Schema::hasColumn('t_usuario', 'USU_TIPO')) {
            Schema::table('t_usuario', function (Blueprint $table) {
                $table->string('USU_TIPO', 2)->default('U');
            });
        }

        if (! Schema::hasTable('t_fornecedor')) {
            Schema::create('t_fornecedor', function (Blueprint $table) {
                $table->increments('FORN_CODIGO');
                $table->string('FORN_CNPJ', 16);
                $table->string('FORN_RAZAO', 50);
                $table->string('FORN_TELEFONE', 15)->nullable();
                $table->string('FORN_EMAIL', 35)->nullable();
                $table->string('FORN_STATUS', 2);
                $table->unsignedInteger('USU_CODIGO');
                $table->timestamp('FORN_DATACADASTRO')->useCurrent();

                $table->index('USU_CODIGO');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('t_usuario') && Schema::hasColumn('t_usuario', 'USU_TIPO')) {
            Schema::table('t_usuario', function (Blueprint $table) {
                $table->dropColumn('USU_TIPO');
            });
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql' && Schema::hasTable('t_fornecedor')) {
            Schema::drop('t_fornecedor');
        }
    }
};
