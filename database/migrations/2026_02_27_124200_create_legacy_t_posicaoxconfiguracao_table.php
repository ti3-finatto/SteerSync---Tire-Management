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
CREATE TABLE `t_posicaoxconfiguracao` (
  `PSCF_CODIGO` int NOT NULL,
  `VEIC_CODIGO` int NOT NULL,
  `POS_CODIGO` int NOT NULL,
  `PSCF_PAR` int DEFAULT NULL,
  `PSCF_EIXO` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_posicaoxconfiguracao`
  ADD PRIMARY KEY (`PSCF_CODIGO`),
  ADD KEY `t_posicaoxconfiguracao_PK_ibfk_1` (`POS_CODIGO`),
  ADD KEY `t_posicaoxconfiguracao_PK_ibfk_2` (`VEIC_CODIGO`)
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_posicaoxconfiguracao');
    }
};