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
CREATE TABLE `t_calibragem` (
  `CAL_CODIGO` int NOT NULL,
  `MOV_CODIGO` int NOT NULL,
  `VEI_CODIGO` int DEFAULT NULL,
  `PNE_CODIGO` int NOT NULL,
  `CAL_ENCONTRADA` int NOT NULL,
  `CAL_AJUSTADA` int NOT NULL,
  `INSP_CODIGO` int DEFAULT NULL,
  `USU_CODIGO` int NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'Compatibilidade legado: FK para users.id; coluna legada preservada para migracao',
  `CAL_DATA` date NOT NULL,
  `CAL_DATALANCAMENTO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_calibragem`
  ADD PRIMARY KEY (`CAL_CODIGO`),
  ADD KEY `INSP_CODIGO` (`INSP_CODIGO`),
  ADD KEY `MOV_CODIGO` (`MOV_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`),
  ADD KEY `VEI_CODIGO` (`VEI_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`),
  ADD KEY `user_id` (`user_id`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_calibragem`
  MODIFY `CAL_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_calibragem');
    }
};