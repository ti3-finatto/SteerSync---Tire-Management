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
CREATE TABLE `t_acessousuario` (
  `ACE_CODIGO` int NOT NULL,
  `ACE_PAGINA` varchar(50) NOT NULL,
  `ACE_VISUALIZA` tinyint(1) NOT NULL,
  `ACE_EDITA` tinyint(1) NOT NULL,
  `ACE_EXCLUI` tinyint(1) NOT NULL,
  `USU_CODIGOACESSO` int NOT NULL,
  `USU_CODIGOCADASTRO` int NOT NULL,
  `ACE_DATA` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_acessousuario`
  ADD PRIMARY KEY (`ACE_CODIGO`),
  ADD KEY `USU_CODIGOACESSO` (`USU_CODIGOACESSO`),
  ADD KEY `USU_CODIGOCADASTRO` (`USU_CODIGOCADASTRO`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_acessousuario`
  MODIFY `ACE_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_acessousuario');
    }
};