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
CREATE TABLE `t_baixapneu` (
  `BAI_CODIGO` int NOT NULL,
  `PNE_CODIGO` int NOT NULL,
  `MOPA_CODIGO` int NOT NULL,
  `BAI_DESCRICAO` varchar(500) NOT NULL,
  `BAI_DATA` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `BAI_STATUS` char(1) NOT NULL DEFAULT 'F' COMMENT 'P=Pendente, F=Finalizado',
  `BAI_USU_SOLICITA` int DEFAULT NULL,
  `BAI_USU_EFETIVA` int DEFAULT NULL,
  `BAI_DT_SOLICITA` datetime DEFAULT NULL,
  `BAI_DT_EFETIVA` datetime DEFAULT NULL,
  `BAI_IMGDANO` varchar(50) DEFAULT NULL,
  `BAI_IMGFOGO` varchar(50) DEFAULT NULL,
  `BAI_IMGMM` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_baixapneu`
  ADD PRIMARY KEY (`BAI_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`),
  ADD KEY `MOPA_CODIGO` (`MOPA_CODIGO`),
  ADD KEY `idx_baixapneu_status` (`BAI_STATUS`),
  ADD KEY `idx_baixapneu_pne_status` (`PNE_CODIGO`,`BAI_STATUS`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_baixapneu`
  MODIFY `BAI_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_baixapneu');
    }
};