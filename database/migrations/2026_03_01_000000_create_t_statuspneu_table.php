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

        if (Schema::hasTable('t_statuspneu')) {
            return;
        }

        DB::statement(<<<'SQL'
CREATE TABLE `t_statuspneu` (
  `STP_SIGLA`     varchar(2)  NOT NULL COMMENT 'Sigla unica usada em t_pneu.PNE_STATUS',
  `STP_DESCRICAO` varchar(60) NOT NULL COMMENT 'Descricao legivel do status',
  `STP_ORDEM`     tinyint unsigned NOT NULL DEFAULT 0 COMMENT 'Ordem de exibicao na UI'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_statuspneu`
  ADD PRIMARY KEY (`STP_SIGLA`)
SQL);

        // Status padrões — siglas imutáveis por regra de negócio
        DB::table('t_statuspneu')->insert([
            ['STP_SIGLA' => 'D',  'STP_DESCRICAO' => 'Disponível',               'STP_ORDEM' => 1],
            ['STP_SIGLA' => 'M',  'STP_DESCRICAO' => 'Montado',                  'STP_ORDEM' => 2],
            ['STP_SIGLA' => 'B',  'STP_DESCRICAO' => 'Baixado',                  'STP_ORDEM' => 3],
            ['STP_SIGLA' => 'R',  'STP_DESCRICAO' => 'Recapagem Pendente',        'STP_ORDEM' => 4],
            ['STP_SIGLA' => 'C',  'STP_DESCRICAO' => 'Conserto Pendente',         'STP_ORDEM' => 5],
            ['STP_SIGLA' => 'S',  'STP_DESCRICAO' => 'Sucateamento Pendente',     'STP_ORDEM' => 6],
            ['STP_SIGLA' => 'F',  'STP_DESCRICAO' => 'No Fornecedor',             'STP_ORDEM' => 7],
            ['STP_SIGLA' => 'G',  'STP_DESCRICAO' => 'Garantia',                  'STP_ORDEM' => 8],
            ['STP_SIGLA' => 'T',  'STP_DESCRICAO' => 'Em Transferência',          'STP_ORDEM' => 9],
            ['STP_SIGLA' => 'PR', 'STP_DESCRICAO' => 'Em Processo de Retorno',    'STP_ORDEM' => 10],
            ['STP_SIGLA' => 'NL', 'STP_DESCRICAO' => 'Não Localizado',            'STP_ORDEM' => 11],
            ['STP_SIGLA' => 'DE', 'STP_DESCRICAO' => 'Divergência de Estoque',    'STP_ORDEM' => 12],
        ]);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_statuspneu');
    }
};
