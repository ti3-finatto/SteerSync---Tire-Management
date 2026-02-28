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
CREATE TABLE `t_itensnfcomprapneus` (
  `ITS_CODIGO` int NOT NULL,
  `ITS_QNT` int NOT NULL,
  `ITS_FOGOINI` int NOT NULL,
  `ITS_STATUS` varchar(1) DEFAULT NULL,
  `TIPO_CODIGO` int NOT NULL,
  `NF_CODIGO` int NOT NULL,
  `ITS_VALORTOTAL` double NOT NULL,
  `TIPO_CODIGORECAPE` int DEFAULT NULL,
  `PNE_STATUS` varchar(2) NOT NULL,
  `ITS_DOT` varchar(10) DEFAULT NULL,
  `PNE_VIDA` varchar(3) NOT NULL,
  `UNI_CODIGO` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itensnfcomprapneus`
  ADD UNIQUE KEY `ITS_ID` (`ITS_CODIGO`),
  ADD KEY `NF_CODIGO` (`NF_CODIGO`),
  ADD KEY `UNI_CODIGO` (`UNI_CODIGO`),
  ADD KEY `TIPO_CODIGO` (`TIPO_CODIGO`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itensnfcomprapneus`
  MODIFY `ITS_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_itensnfcomprapneus');
    }
};