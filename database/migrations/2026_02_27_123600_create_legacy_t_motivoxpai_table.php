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
CREATE TABLE `t_motivoxpai` (
  `MOPA_CODIGO` int NOT NULL,
  `MOTP_CODIGO` int NOT NULL,
  `MOTPAI_CODIGO` int NOT NULL,
  `MOPA_STATUS` varchar(1) NOT NULL DEFAULT 'A',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_motivoxpai`
  ADD PRIMARY KEY (`MOPA_CODIGO`),
  ADD KEY `MOTPAI_CODIGO` (`MOTPAI_CODIGO`),
  ADD KEY `MOTP_CODIGO` (`MOTP_CODIGO`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_motivoxpai`
  MODIFY `MOPA_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_motivoxpai');
    }
};