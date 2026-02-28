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
CREATE TABLE `t_inspecao` (
  `INSP_CODIGO` int NOT NULL,
  `INSP_OBSERVACAO` varchar(100) DEFAULT NULL,
  `INSP_TIPO` varchar(30) DEFAULT NULL,
  `INSP_KMATUAL` int NOT NULL,
  `VEI_CODIGO` int NOT NULL,
  `UNI_CODIGO` int DEFAULT NULL,
  `INSP_STATUS` varchar(2) NOT NULL,
  `INSP_TEMPOSEGUNDOS` double DEFAULT NULL,
  `INSP_DATAINSPECAO` date NOT NULL,
  `INSP_DATACADASTRO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `INSP_DATAFECHAMENTO` timestamp NULL DEFAULT NULL,
  `USU_CODIGO` int NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'Compatibilidade legado: FK para users.id; coluna legada preservada para migracao',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_inspecao`
  ADD PRIMARY KEY (`INSP_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`),
  ADD KEY `VEI_CODIGO` (`VEI_CODIGO`),
  ADD KEY `user_id` (`user_id`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_inspecao`
  MODIFY `INSP_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_inspecao');
    }
};