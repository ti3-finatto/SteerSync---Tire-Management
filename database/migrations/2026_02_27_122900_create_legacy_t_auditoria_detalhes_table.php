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
CREATE TABLE `t_auditoria_detalhes` (
  `id` int NOT NULL,
  `audit_id` int NOT NULL,
  `pne_codigo` int DEFAULT NULL,
  `pne_fogo` varchar(50) NOT NULL,
  `status_esperado` varchar(20) NOT NULL,
  `status_encontrado` varchar(20) NOT NULL,
  `uni_esperada` int DEFAULT NULL,
  `uni_encontrada` int DEFAULT NULL,
  `tipo_divergencia` enum('D1','D2','D3','D4','D5','D6','D7','D8') DEFAULT NULL,
  `foto_path` varchar(255) DEFAULT NULL,
  `data_registro` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_auditoria_detalhes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_audit_id` (`audit_id`),
  ADD KEY `idx_pne_fogo` (`pne_fogo`),
  ADD KEY `idx_pne_codigo` (`pne_codigo`),
  ADD KEY `idx_status_esperado` (`status_esperado`),
  ADD KEY `idx_status_encontrado` (`status_encontrado`),
  ADD KEY `idx_tipo_divergencia` (`tipo_divergencia`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_auditoria_detalhes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_auditoria_detalhes');
    }
};