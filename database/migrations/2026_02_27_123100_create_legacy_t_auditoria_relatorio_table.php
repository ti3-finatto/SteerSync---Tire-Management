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
CREATE TABLE `t_auditoria_relatorio` (
  `id` int NOT NULL,
  `audit_id` int NOT NULL,
  `conf_usu_codigo` int NOT NULL,
  `rel_titulo` varchar(150) NOT NULL,
  `rel_texto` mediumtext NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_auditoria_relatorio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_audit` (`audit_id`),
  ADD KEY `idx_conf_usu` (`conf_usu_codigo`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_auditoria_relatorio`
  MODIFY `id` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_auditoria_relatorio');
    }
};