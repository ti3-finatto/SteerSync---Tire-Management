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
CREATE TABLE `t_movimentacao` (
  `MOV_CODIGO` int NOT NULL,
  `PNE_CODIGO` int DEFAULT NULL,
  `PNEU_VIDA_ATUAL` varchar(2) DEFAULT NULL,
  `MOV_OPERACAO` varchar(2) NOT NULL,
  `MOV_MM_MINIMA` float DEFAULT NULL,
  `UNI_CODIGO` int DEFAULT NULL,
  `MOV_DATA` date NOT NULL,
  `MOV_DATAMOVIMENTO` timestamp NULL DEFAULT NULL,
  `VEI_CODIGO` int DEFAULT NULL,
  `USU_CODIGO` int DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'Compatibilidade legado: FK para users.id; coluna legada preservada para migracao',
  `POS_CODIGO` int DEFAULT NULL,
  `MOV_KMVEICULO` int DEFAULT NULL,
  `MOV_KMPNEU` int DEFAULT NULL,
  `MOV_COMENTARIO` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_movimentacao`
  ADD PRIMARY KEY (`MOV_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`),
  ADD KEY `POS_CODIGO` (`POS_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`),
  ADD KEY `VEI_CODIGO` (`VEI_CODIGO`),
  ADD KEY `user_id` (`user_id`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_movimentacao`
  MODIFY `MOV_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_movimentacao');
    }
};