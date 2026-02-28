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
CREATE TABLE `t_usuario_unidades` (
  `USXUN_CODIGO` int NOT NULL,
  `USU_CODIGO` int NOT NULL,
  `UNI_CODIGO` int NOT NULL,
  `USXUN_STATUS` varchar(2) NOT NULL,
  `USXUN_DATACADASTRO` datetime NOT NULL,
  `USU_CADASTRO` int NOT NULL,
  `USXUN_OBSERVACAO` varchar(200) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_usuario_unidades`
  ADD PRIMARY KEY (`USXUN_CODIGO`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_usuario_unidades`
  MODIFY `USXUN_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_usuario_unidades');
    }
};