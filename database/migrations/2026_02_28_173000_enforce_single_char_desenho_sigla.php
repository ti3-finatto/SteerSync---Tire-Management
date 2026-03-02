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

        if (! Schema::hasTable('t_desenhobanda') || ! Schema::hasTable('t_tipo')) {
            return;
        }

        DB::statement(<<<'SQL'
UPDATE `t_desenhobanda` SET `DESB_SIGLA` = UPPER(LEFT(TRIM(`DESB_SIGLA`), 1))
SQL);

        DB::statement(<<<'SQL'
UPDATE `t_tipo` SET `TIPO_DESENHO` = UPPER(LEFT(TRIM(`TIPO_DESENHO`), 1))
SQL);

        $foreignExists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 't_tipo')
            ->where('CONSTRAINT_NAME', 't_tipo_ibfk_desenho')
            ->exists();

        if ($foreignExists) {
            DB::statement('ALTER TABLE `t_tipo` DROP FOREIGN KEY `t_tipo_ibfk_desenho`');
        }

        DB::statement(<<<'SQL'
ALTER TABLE `t_desenhobanda`
  MODIFY `DESB_SIGLA` varchar(1) NOT NULL
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_tipo`
  MODIFY `TIPO_DESENHO` varchar(1) NOT NULL
SQL);

        DB::statement(<<<'SQL'
INSERT INTO `t_desenhobanda` (`DESB_DESCRICAO`, `DESB_SIGLA`, `DESB_STATUS`, `USU_CODIGO`, `DESB_DATACADASTRO`)
SELECT 'LISO', 'L', 'A', 0, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `t_desenhobanda` WHERE `DESB_SIGLA` = 'L')
SQL);

        DB::statement(<<<'SQL'
INSERT INTO `t_desenhobanda` (`DESB_DESCRICAO`, `DESB_SIGLA`, `DESB_STATUS`, `USU_CODIGO`, `DESB_DATACADASTRO`)
SELECT 'MISTO', 'M', 'A', 0, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `t_desenhobanda` WHERE `DESB_SIGLA` = 'M')
SQL);

        DB::statement(<<<'SQL'
INSERT INTO `t_desenhobanda` (`DESB_DESCRICAO`, `DESB_SIGLA`, `DESB_STATUS`, `USU_CODIGO`, `DESB_DATACADASTRO`)
SELECT 'BORRACHUDO', 'B', 'A', 0, NOW()
WHERE NOT EXISTS (SELECT 1 FROM `t_desenhobanda` WHERE `DESB_SIGLA` = 'B')
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_tipo`
  ADD CONSTRAINT `t_tipo_ibfk_desenho` FOREIGN KEY (`TIPO_DESENHO`) REFERENCES `t_desenhobanda` (`DESB_SIGLA`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        if (! Schema::hasTable('t_desenhobanda') || ! Schema::hasTable('t_tipo')) {
            return;
        }

        $foreignExists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 't_tipo')
            ->where('CONSTRAINT_NAME', 't_tipo_ibfk_desenho')
            ->exists();

        if ($foreignExists) {
            DB::statement('ALTER TABLE `t_tipo` DROP FOREIGN KEY `t_tipo_ibfk_desenho`');
        }

        DB::statement(<<<'SQL'
ALTER TABLE `t_desenhobanda`
  MODIFY `DESB_SIGLA` varchar(2) NOT NULL
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_tipo`
  MODIFY `TIPO_DESENHO` varchar(2) NOT NULL
SQL);

        DB::statement(<<<'SQL'
ALTER TABLE `t_tipo`
  ADD CONSTRAINT `t_tipo_ibfk_desenho` FOREIGN KEY (`TIPO_DESENHO`) REFERENCES `t_desenhobanda` (`DESB_SIGLA`) ON DELETE RESTRICT ON UPDATE RESTRICT
SQL);
    }
};
