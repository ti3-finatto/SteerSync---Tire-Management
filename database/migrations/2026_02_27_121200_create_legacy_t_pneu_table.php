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
CREATE TABLE `t_pneu` (
  `PNE_CODIGO` int NOT NULL,
  `PNE_FOGO` varchar(20) NOT NULL,
  `TIPO_CODIGO` int NOT NULL,
  `CAL_RECOMENDADA` double DEFAULT NULL,
  `PNE_DOT` varchar(5) DEFAULT NULL,
  `PNE_KM` int NOT NULL DEFAULT '0',
  `PNE_MM` float NOT NULL,
  `PNE_STATUS` varchar(2) NOT NULL,
  `PNE_STATUSCOMPRA` varchar(1) NOT NULL,
  `PNE_VALORCOMPRA` double NOT NULL,
  `PNE_VIDACOMPRA` varchar(3) NOT NULL,
  `PNE_VIDAATUAL` varchar(3) NOT NULL,
  `TIPO_CODIGORECAPE` int NOT NULL,
  `ITS_CODIGO` int DEFAULT NULL,
  `PNE_CUSTOATUAL` double NOT NULL DEFAULT '0',
  `USU_CODIGO` int DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'Compatibilidade legado: FK para users.id; coluna legada preservada para migracao',
  `UNI_CODIGO` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        // Refatoracao legada: PK composta (PNE_CODIGO, PNE_FOGO) convertida para PK simples em PNE_CODIGO.

        // Para manter compatibilidade e evitar colisao por unidade, aplicamos UNIQUE (UNI_CODIGO, PNE_FOGO).

        DB::statement(<<<'SQL'
ALTER TABLE `t_pneu`
  ADD PRIMARY KEY (`PNE_CODIGO`),
  ADD UNIQUE KEY `uq_t_pneu_uni_codigo_pne_fogo` (`UNI_CODIGO`,`PNE_FOGO`),
  ADD KEY `COD_USUARIO` (`USU_CODIGO`),
  ADD KEY `UNI_CODIGO` (`UNI_CODIGO`),
  ADD KEY `TIPO_CODIGO` (`TIPO_CODIGO`),
  ADD KEY `ITS_CODIGO` (`ITS_CODIGO`) USING BTREE,
  ADD KEY `user_id` (`user_id`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_pneu`
  MODIFY `PNE_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_pneu');
    }
};