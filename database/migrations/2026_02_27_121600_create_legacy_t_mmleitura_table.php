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
CREATE TABLE `t_mmleitura` (
  `MML_CODIGO` int NOT NULL,
  `MOV_CODIGO` int NOT NULL,
  `MML_MEDIA` float NOT NULL,
  `MML_MINIMO` float NOT NULL,
  `MML_LEITURA` varchar(100) NOT NULL,
  `VEI_CODIGO` int DEFAULT NULL,
  `PNE_CODIGO` int NOT NULL,
  `INSP_CODIGO` int DEFAULT NULL,
  `SULCO_INTERNO` float NOT NULL,
  `SULCO_CENTRAL_INTERNO` float NOT NULL,
  `SULCO_CENTRAL_EXTERNO` float NOT NULL,
  `SULCO_EXTERNO` float NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_mmleitura`
  ADD PRIMARY KEY (`MML_CODIGO`),
  ADD KEY `INSP_CODIGO` (`INSP_CODIGO`),
  ADD KEY `MOV_CODIGO` (`MOV_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`),
  ADD KEY `VEI_CODIGO` (`VEI_CODIGO`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_mmleitura`
  MODIFY `MML_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_mmleitura');
    }
};