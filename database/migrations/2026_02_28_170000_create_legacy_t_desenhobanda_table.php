<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        if (Schema::hasTable('t_desenhobanda')) {
            return;
        }

        DB::statement(<<<'SQL'
CREATE TABLE `t_desenhobanda` (
  `DESB_CODIGO` int NOT NULL,
  `DESB_DESCRICAO` varchar(30) NOT NULL,
  `DESB_SIGLA` varchar(1) NOT NULL,
  `DESB_STATUS` varchar(1) NOT NULL,
  `USU_CODIGO` int NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'Compatibilidade legado: FK para users.id; coluna legada preservada para migracao',
  `DESB_DATACADASTRO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_desenhobanda`
  ADD PRIMARY KEY (`DESB_CODIGO`),
  ADD UNIQUE KEY `DESB_SIGLA_UNQ` (`DESB_SIGLA`),
  ADD KEY `DESB_USERCADASTRO` (`USU_CODIGO`),
  ADD KEY `user_id` (`user_id`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_desenhobanda`
  MODIFY `DESB_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);

        if (Schema::hasTable('users')) {
            DB::statement(<<<'SQL'
ALTER TABLE `t_desenhobanda`
  ADD CONSTRAINT `t_desenhobanda_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_desenhobanda');
    }
};
