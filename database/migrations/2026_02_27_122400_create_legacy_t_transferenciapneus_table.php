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
CREATE TABLE `t_transferenciapneus` (
  `id` int NOT NULL,
  `unidadeRemetente` int NOT NULL,
  `unidadeDestino` int NOT NULL,
  `dataEnvio` date NOT NULL,
  `dataCadastro` datetime NOT NULL,
  `usuarioCadastro` int NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'Compatibilidade legado: FK para users.id; coluna legada preservada para migracao',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_transferenciapneus`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_UNI_REMETENTE` (`unidadeRemetente`),
  ADD KEY `FK_UNI_DESTINO` (`unidadeDestino`),
  ADD KEY `FK_USUARIO_CADASTRO` (`usuarioCadastro`),
  ADD KEY `user_id` (`user_id`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_transferenciapneus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_transferenciapneus');
    }
};