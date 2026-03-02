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

        if (Schema::hasTable('t_tipoveiculo')) {
            return;
        }

        DB::statement(<<<'SQL'
CREATE TABLE `t_tipoveiculo` (
  `TPVE_SIGLA`     varchar(5)  NOT NULL COMMENT 'Sigla unica; usada como VEIC_TIPO em t_modeloveiculo',
  `TPVE_DESCRICAO` varchar(50) NOT NULL COMMENT 'Descricao do tipo de veiculo',
  `TPVE_STATUS`    varchar(1)  NOT NULL DEFAULT 'A' COMMENT 'A=Ativo I=Inativo',
  `TPVE_PADRAO`    tinyint(1)  NOT NULL DEFAULT 0   COMMENT '1=tipo padrao protegido; nao pode ser editado nem inativado',
  `TPVE_ORDEM`     tinyint unsigned NOT NULL DEFAULT 99 COMMENT 'Ordem de exibicao'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_tipoveiculo`
  ADD PRIMARY KEY (`TPVE_SIGLA`)
SQL);

        // Tipos padrão protegidos — siglas exatas definidas pelo negócio
        DB::table('t_tipoveiculo')->insert([
            ['TPVE_SIGLA' => 'CA',  'TPVE_DESCRICAO' => 'Carro',        'TPVE_STATUS' => 'A', 'TPVE_PADRAO' => 1, 'TPVE_ORDEM' => 1],
            ['TPVE_SIGLA' => 'CV',  'TPVE_DESCRICAO' => 'Cavalo',       'TPVE_STATUS' => 'A', 'TPVE_PADRAO' => 1, 'TPVE_ORDEM' => 2],
            ['TPVE_SIGLA' => 'CR',  'TPVE_DESCRICAO' => 'Carreta',      'TPVE_STATUS' => 'A', 'TPVE_PADRAO' => 1, 'TPVE_ORDEM' => 3],
            ['TPVE_SIGLA' => 'CM',  'TPVE_DESCRICAO' => 'Caminhão',     'TPVE_STATUS' => 'A', 'TPVE_PADRAO' => 1, 'TPVE_ORDEM' => 4],
            ['TPVE_SIGLA' => 'UT',  'TPVE_DESCRICAO' => 'Utilitário',   'TPVE_STATUS' => 'A', 'TPVE_PADRAO' => 1, 'TPVE_ORDEM' => 5],
            ['TPVE_SIGLA' => 'ON',  'TPVE_DESCRICAO' => 'Ônibus',       'TPVE_STATUS' => 'A', 'TPVE_PADRAO' => 1, 'TPVE_ORDEM' => 6],
            ['TPVE_SIGLA' => 'MC',  'TPVE_DESCRICAO' => 'Motocicleta',  'TPVE_STATUS' => 'A', 'TPVE_PADRAO' => 1, 'TPVE_ORDEM' => 7],
            ['TPVE_SIGLA' => 'RE',  'TPVE_DESCRICAO' => 'Reboque',      'TPVE_STATUS' => 'A', 'TPVE_PADRAO' => 1, 'TPVE_ORDEM' => 8],
        ]);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        Schema::dropIfExists('t_tipoveiculo');
    }
};
