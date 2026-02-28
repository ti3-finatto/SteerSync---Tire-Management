<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('t_clienteunidade')) {
            Schema::create('t_clienteunidade', function (Blueprint $table) {
                $table->increments('UNI_CODIGO');
                $table->string('UNI_DESCRICAO', 40);
                $table->string('UNI_STATUS', 1);
                $table->string('CLI_CNPJ', 16)->default('');
                $table->char('CLI_UF', 2)->default('');
                $table->string('CLI_CIDADE', 60)->default('');
            });
        }

        if (! Schema::hasTable('t_pneu')) {
            Schema::create('t_pneu', function (Blueprint $table) {
                $table->increments('PNE_CODIGO');
                $table->unsignedInteger('UNI_CODIGO');
            });
        }

        if (! Schema::hasTable('t_veiculo')) {
            Schema::create('t_veiculo', function (Blueprint $table) {
                $table->increments('VEI_CODIGO');
                $table->unsignedInteger('UNI_CODIGO');
            });
        }
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql' && Schema::hasTable('t_veiculo')) {
            Schema::drop('t_veiculo');
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql' && Schema::hasTable('t_pneu')) {
            Schema::drop('t_pneu');
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql' && Schema::hasTable('t_clienteunidade')) {
            Schema::drop('t_clienteunidade');
        }
    }
};
