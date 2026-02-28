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
CREATE TABLE `t_veiculo` (
  `VEI_CODIGO` int NOT NULL,
  `VEI_PLACA` varchar(7) NOT NULL,
  `VEI_CHASSI` varchar(17) DEFAULT NULL,
  `VEI_FROTA` varchar(25) DEFAULT NULL,
  `VEI_STATUS` varchar(2) NOT NULL,
  `CAL_RECOMENDADA` int DEFAULT NULL,
  `MODV_CODIGO` int NOT NULL,
  `UNI_CODIGO` int NOT NULL,
  `VEIC_CODIGO` int NOT NULL,
  `VEI_KM` int NOT NULL,
  `USU_CODIGO` int NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'Compatibilidade legado: FK para users.id; coluna legada preservada para migracao',
  `VEI_DATACADASTRO` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `VEI_OBS` tinytext,
  `VEI_ODOMETRO` varchar(2) NOT NULL,
  `USU_MOTORISTA` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_veiculo`
  ADD PRIMARY KEY (`VEI_CODIGO`),
  ADD KEY `MODV_CODIGO` (`MODV_CODIGO`),
  ADD KEY `USU_CODIGO` (`USU_CODIGO`),
  ADD KEY `UNI_CODIGO` (`UNI_CODIGO`),
  ADD KEY `VEIC_CODIGO` (`VEIC_CODIGO`),
  ADD KEY `user_id` (`user_id`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_veiculo`
  MODIFY `VEI_CODIGO` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_veiculo');
    }
};