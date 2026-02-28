<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    /**
     * Migracao/refatoracao do legado: preserva nomes originais de tabelas/colunas,
     * tipos, defaults, indices e adiciona timestamps padrao do Eloquent quando ausentes.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(<<<'SQL'
CREATE TABLE `t_clientepreferencia` (
  `PREF_CODIGO` int NOT NULL,
  `PREF_QTD_MESES` int NOT NULL DEFAULT '6',
  `PREF_DIAS_INSPECAO` int NOT NULL DEFAULT '30',
  `PREF_USU_CADASTRO` int NOT NULL,
  `PREF_DATA_CADASTRO` datetime NOT NULL,
  `PREF_MIN_CPK` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_clientepreferencia`
  ADD PRIMARY KEY (`PREF_CODIGO`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_clientepreferencia`
  MODIFY `PREF_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_clientepreferencia');
    }
};