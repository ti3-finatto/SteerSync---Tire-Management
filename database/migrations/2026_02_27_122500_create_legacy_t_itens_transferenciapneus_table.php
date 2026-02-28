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
CREATE TABLE `t_itens_transferenciapneus` (
  `idItem` int NOT NULL,
  `idTransferencia` int NOT NULL,
  `pneuCodigo` int NOT NULL,
  `statusPneuEnvio` varchar(2) NOT NULL COMMENT 'Status de como o pneu foi enviado e como deve retornar',
  `dataRetorno` date DEFAULT NULL,
  `dataRetornoCadastro` datetime DEFAULT NULL,
  `usuarioRetorno` int DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'Compatibilidade legado: FK para users.id; coluna legada preservada para migracao',
  `status` varchar(2) NOT NULL,
  `observacao` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itens_transferenciapneus`
  ADD PRIMARY KEY (`idItem`),
  ADD KEY `FK_TransferenciaPneu` (`idTransferencia`),
  ADD KEY `FK_PNECODIGO_TRANSF` (`pneuCodigo`),
  ADD KEY `FK_USUARIO_RETORNO_TRANS` (`usuarioRetorno`),
  ADD KEY `user_id` (`user_id`)
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_itens_transferenciapneus`
  MODIFY `idItem` int NOT NULL AUTO_INCREMENT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_itens_transferenciapneus');
    }
};