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
CREATE TABLE `t_itensretorno` (
  `ITRT_CODIGO` int NOT NULL,
  `ITEM_SAIDA` int DEFAULT NULL,
  `RETPNE_CODIGO` int NOT NULL,
  `PNE_CODIGO` int NOT NULL,
  `ITRT_TIPORETORNO` varchar(2) NOT NULL,
  `ITRT_STATUS` varchar(2) NOT NULL,
  `TIPO_CODIGO` int DEFAULT NULL,
  `ITRT_VALOR` double DEFAULT NULL,
  `ITRT_DESCRICAO` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itensretorno`
  ADD PRIMARY KEY (`ITRT_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`),
  ADD KEY `TIPO_CODIGO` (`TIPO_CODIGO`),
  ADD KEY `RETPNE_CODIGO` (`RETPNE_CODIGO`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itensretorno`
  MODIFY `ITRT_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_itensretorno');
    }
};