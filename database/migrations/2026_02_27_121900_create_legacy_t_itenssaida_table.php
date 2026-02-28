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
CREATE TABLE `t_itenssaida` (
  `ITSD_CODIGO` int NOT NULL,
  `SAIDA_CODIGO` int NOT NULL,
  `PNE_CODIGO` int NOT NULL,
  `ITSD_PNEKM` int NOT NULL,
  `ITSD_TIPOSAIDA` varchar(2) NOT NULL,
  `FORN_CODIGO` int NOT NULL,
  `ITSD_STATUS` varchar(2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itenssaida`
  ADD PRIMARY KEY (`ITSD_CODIGO`),
  ADD KEY `FORN_CODIGO` (`FORN_CODIGO`),
  ADD KEY `PNE_CODIGO` (`PNE_CODIGO`),
  ADD KEY `SAIDA_CODIGO` (`SAIDA_CODIGO`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itenssaida`
  MODIFY `ITSD_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_itenssaida');
    }
};