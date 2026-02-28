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
CREATE TABLE `t_tipo` (
  `TIPO_CODIGO` int NOT NULL,
  `TIPO_STATUS` varchar(2) NOT NULL,
  `TIPO_DESCRICAO` varchar(40) NOT NULL,
  `TIPO_INSPECAO` varchar(2) NOT NULL,
  `MARP_CODIGO` int NOT NULL,
  `MODP_CODIGO` int NOT NULL,
  `MEDP_CODIGO` int NOT NULL,
  `TIPO_DESENHO` varchar(2) NOT NULL,
  `TIPO_NSULCO` int NOT NULL,
  `TIPO_MMSEGURANCA` float NOT NULL,
  `TIPO_MMNOVO` float NOT NULL,
  `TIPO_MMDESGEIXOS` float DEFAULT NULL,
  `TIPO_MMDESGPAR` float DEFAULT NULL,
  `USU_CODIGO` int NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'Compatibilidade legado: FK para users.id; coluna legada preservada para migracao',
  `TIPO_DATACADASTRO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_tipo`
  ADD PRIMARY KEY (`TIPO_CODIGO`),
  ADD KEY `MARP_CODIGO` (`MARP_CODIGO`),
  ADD KEY `MODP_CODIGO` (`MODP_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`),
  ADD KEY `MEDP_CODIGO` (`MEDP_CODIGO`),
  ADD KEY `user_id` (`user_id`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_tipo`
  MODIFY `TIPO_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_tipo');
    }
};