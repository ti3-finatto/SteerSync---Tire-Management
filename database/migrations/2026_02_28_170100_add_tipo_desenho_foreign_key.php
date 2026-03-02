<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(<<<'SQL'
INSERT INTO `t_desenhobanda` (`DESB_DESCRICAO`, `DESB_SIGLA`, `DESB_STATUS`, `USU_CODIGO`, `DESB_DATACADASTRO`)
SELECT DISTINCT
  UPPER(TRIM(`TIPO_DESENHO`)) AS DESB_DESCRICAO,
  UPPER(TRIM(`TIPO_DESENHO`)) AS DESB_SIGLA,
  'A' AS DESB_STATUS,
  0 AS USU_CODIGO,
  NOW() AS DESB_DATACADASTRO
FROM `t_tipo` t
WHERE CHAR_LENGTH(TRIM(t.`TIPO_DESENHO`)) = 1
  AND NOT EXISTS (
    SELECT 1 FROM `t_desenhobanda` d WHERE d.`DESB_SIGLA` = UPPER(TRIM(t.`TIPO_DESENHO`))
  )
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

        DB::statement(<<<'SQL'
ALTER TABLE `t_tipo` DROP FOREIGN KEY `t_tipo_ibfk_desenho`
SQL);
    }
};
