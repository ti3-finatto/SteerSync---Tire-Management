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
CREATE TABLE `t_pneuatual` (
  `PNEA_CODIGO` int NOT NULL,
  `MOV_CODIGO` int DEFAULT NULL,
  `PNE_CODIGO` int DEFAULT NULL,
  `PNE_FOGO` varchar(20) DEFAULT NULL,
  `VEI_CODIGO` int NOT NULL,
  `POS_CODIGO` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_pneuatual`
  ADD PRIMARY KEY (`PNEA_CODIGO`),
  ADD KEY `MOV_CODIGO` (`MOV_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`),
  ADD KEY `POS_CODIGO` (`POS_CODIGO`),
  ADD KEY `VEI_CODIGO` (`VEI_CODIGO`),
  ADD KEY `atual_PNEFOGO` (`PNE_FOGO`(10))
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_pneuatual`
  MODIFY `PNEA_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_pneuatual');
    }
};