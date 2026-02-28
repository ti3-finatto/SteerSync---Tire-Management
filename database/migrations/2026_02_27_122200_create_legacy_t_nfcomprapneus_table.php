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
CREATE TABLE `t_nfcomprapneus` (
  `NF_CODIGO` int NOT NULL,
  `NF_NUM` int NOT NULL,
  `FORN_CODIGO` int NOT NULL,
  `UNI_CODIGO` int NOT NULL,
  `NF_DATA` date DEFAULT NULL,
  `NF_DATA_RECEBIMENTO` date DEFAULT NULL,
  `NF_CADASTRODATA` timestamp NULL DEFAULT NULL,
  `NF_TIPO` varchar(1) NOT NULL,
  `NF_VLTOTAL` double NOT NULL,
  `USU_CODIGO` int DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'Compatibilidade legado: FK para users.id; coluna legada preservada para migracao',
  `NF_STATUS` char(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_nfcomprapneus`
  ADD PRIMARY KEY (`NF_CODIGO`),
  ADD UNIQUE KEY `NF_ID` (`NF_CODIGO`),
  ADD KEY `FORN_CODIGO` (`FORN_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`),
  ADD KEY `user_id` (`user_id`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_nfcomprapneus`
  MODIFY `NF_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_nfcomprapneus');
    }
};