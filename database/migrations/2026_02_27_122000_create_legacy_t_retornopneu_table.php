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
CREATE TABLE `t_retornopneu` (
  `RETPNE_CODIGO` int NOT NULL,
  `RETPNE_NDOC` double NOT NULL,
  `RETPNE_DATA` date NOT NULL,
  `RETPNE_DATALANCAMENTO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `RETPNE_STATUS` varchar(2) NOT NULL,
  `UNI_CODIGO` int NOT NULL,
  `FORN_CODIGO` int NOT NULL,
  `USU_CODIGO` int NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'Compatibilidade legado: FK para users.id; coluna legada preservada para migracao',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_retornopneu`
  ADD PRIMARY KEY (`RETPNE_CODIGO`),
  ADD KEY `FORN_CODIGO` (`FORN_CODIGO`),
  ADD KEY `UNI_CODIGO` (`UNI_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`),
  ADD KEY `user_id` (`user_id`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_retornopneu`
  MODIFY `RETPNE_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_retornopneu');
    }
};