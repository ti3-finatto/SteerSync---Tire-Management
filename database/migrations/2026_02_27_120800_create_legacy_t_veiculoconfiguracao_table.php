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
CREATE TABLE `t_veiculoconfiguracao` (
  `VEIC_CODIGO` int NOT NULL,
  `VEIC_DESCRICAO` varchar(50) NOT NULL,
  `VEIC_STATUS` varchar(2) NOT NULL,
  `VEIC_TIPO` varchar(2) NOT NULL,
  `VEIC_IMAGEM` varchar(100) NOT NULL,
  `VEIC_IMG_LARGURA` int NOT NULL,
  `VEIC_IMG_ALTURA` int NOT NULL,
  `VEIC_MARGIN_TOP` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_veiculoconfiguracao`
  ADD PRIMARY KEY (`VEIC_CODIGO`)
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_veiculoconfiguracao');
    }
};