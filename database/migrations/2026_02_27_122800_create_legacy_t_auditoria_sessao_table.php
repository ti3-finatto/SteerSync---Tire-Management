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
CREATE TABLE `t_auditoria_sessao` (
  `id` int NOT NULL,
  `uni_codigo` int NOT NULL,
  `usu_codigo` int NOT NULL,
  `usu_participantes` varchar(255) DEFAULT NULL,
  `data_inicio` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_fim` datetime DEFAULT NULL,
  `resultado` enum('ANDAMENTO','CONCLUIDO','DIVERGENCIAS','CANCELADO') NOT NULL DEFAULT 'ANDAMENTO',
  `acuracidade` decimal(5,2) DEFAULT NULL,
  `conf_status` enum('PENDENTE','ANDAMENTO','CONCLUIDO','CANCELADO') NOT NULL DEFAULT 'PENDENTE',
  `conf_usu_codigo` int DEFAULT NULL,
  `conf_data_inicio` datetime DEFAULT NULL,
  `conf_data_fim` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_auditoria_sessao`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_uni_codigo` (`uni_codigo`),
  ADD KEY `idx_usu_codigo` (`usu_codigo`),
  ADD KEY `idx_resultado` (`resultado`),
  ADD KEY `idx_conf_status` (`conf_status`),
  ADD KEY `idx_conf_usu_codigo` (`conf_usu_codigo`),
  ADD KEY `idx_conf_data_inicio` (`conf_data_inicio`),
  ADD KEY `idx_conf_data_fim` (`conf_data_fim`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_auditoria_sessao`
  MODIFY `id` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_auditoria_sessao');
    }
};