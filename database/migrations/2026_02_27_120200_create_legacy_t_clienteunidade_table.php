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
CREATE TABLE `t_clienteunidade` (
  `UNI_CODIGO` int NOT NULL,
  `UNI_DESCRICAO` varchar(40) NOT NULL,
  `UNI_STATUS` varchar(1) NOT NULL,
  `CLI_CNPJ` varchar(16) NOT NULL,
  `CLI_UF` char(2) NOT NULL,
  `CLI_CIDADE` varchar(60) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_clienteunidade`
  ADD PRIMARY KEY (`UNI_CODIGO`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_clienteunidade`
  MODIFY `UNI_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_clienteunidade');
    }
};