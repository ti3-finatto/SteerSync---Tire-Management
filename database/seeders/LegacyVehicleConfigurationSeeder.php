<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LegacyVehicleConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedConfigurations();
        $this->seedPositions();
        $this->seedConfigurationPositions();
    }

    private function seedConfigurations(): void
    {
        $rows = [
            [
                'VEIC_CODIGO' => 1,
                'VEIC_DESCRICAO' => '4X2 SIMPLES',
                'VEIC_STATUS' => 'A',
                'VEIC_TIPO' => 'CV',
                'VEIC_IMAGEM' => '',
                'VEIC_IMG_LARGURA' => 0,
                'VEIC_IMG_ALTURA' => 0,
                'VEIC_MARGIN_TOP' => 0,
            ],
            [
                'VEIC_CODIGO' => 2,
                'VEIC_DESCRICAO' => '6X2 B - D/2, T/4, L/4',
                'VEIC_STATUS' => 'A',
                'VEIC_TIPO' => 'CV',
                'VEIC_IMAGEM' => '',
                'VEIC_IMG_LARGURA' => 0,
                'VEIC_IMG_ALTURA' => 0,
                'VEIC_MARGIN_TOP' => 0,
            ],
            [
                'VEIC_CODIGO' => 3,
                'VEIC_DESCRICAO' => '6X4 TRUCADO',
                'VEIC_STATUS' => 'A',
                'VEIC_TIPO' => 'CV',
                'VEIC_IMAGEM' => '',
                'VEIC_IMG_LARGURA' => 0,
                'VEIC_IMG_ALTURA' => 0,
                'VEIC_MARGIN_TOP' => 0,
            ],
            [
                'VEIC_CODIGO' => 10,
                'VEIC_DESCRICAO' => 'CARRETA 2 EIXOS',
                'VEIC_STATUS' => 'A',
                'VEIC_TIPO' => 'CR',
                'VEIC_IMAGEM' => '',
                'VEIC_IMG_LARGURA' => 0,
                'VEIC_IMG_ALTURA' => 0,
                'VEIC_MARGIN_TOP' => 0,
            ],
            [
                'VEIC_CODIGO' => 11,
                'VEIC_DESCRICAO' => 'CARRETA 3 EIXOS',
                'VEIC_STATUS' => 'A',
                'VEIC_TIPO' => 'CR',
                'VEIC_IMAGEM' => '',
                'VEIC_IMG_LARGURA' => 0,
                'VEIC_IMG_ALTURA' => 0,
                'VEIC_MARGIN_TOP' => 0,
            ],
            [
                'VEIC_CODIGO' => 12,
                'VEIC_DESCRICAO' => 'CARRETA 4 EIXOS MISTA',
                'VEIC_STATUS' => 'A',
                'VEIC_TIPO' => 'CR',
                'VEIC_IMAGEM' => '',
                'VEIC_IMG_LARGURA' => 0,
                'VEIC_IMG_ALTURA' => 0,
                'VEIC_MARGIN_TOP' => 0,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('t_veiculoconfiguracao')->updateOrInsert(
                ['VEIC_CODIGO' => $row['VEIC_CODIGO']],
                $row
            );
        }
    }

    private function seedPositions(): void
    {
        $rows = [
            ['POS_CODIGO' => 1, 'POS_DESCRICAO' => 'DIANTEIRO DIREITO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 2, 'POS_DESCRICAO' => 'DIANTEIRO ESQUERDO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 7, 'POS_DESCRICAO' => 'ESTEPE', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 14, 'POS_DESCRICAO' => 'TRACAO DIREITO EXTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 15, 'POS_DESCRICAO' => 'TRACAO DIREITO INTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 16, 'POS_DESCRICAO' => 'TRACAO ESQUERDO EXTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 17, 'POS_DESCRICAO' => 'TRACAO ESQUERDO INTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 18, 'POS_DESCRICAO' => 'TRUCK DIREITO EXTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 19, 'POS_DESCRICAO' => 'TRUCK DIREITO INTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 20, 'POS_DESCRICAO' => 'ESTEPE 2', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 21, 'POS_DESCRICAO' => '1 EIXO DIREITO EXTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 22, 'POS_DESCRICAO' => '1 EIXO DIREITO INTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 23, 'POS_DESCRICAO' => '1 EIXO ESQUERDO EXTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 24, 'POS_DESCRICAO' => '1 EIXO ESQUERDO INTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 25, 'POS_DESCRICAO' => '2 EIXO DIREITO EXTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 26, 'POS_DESCRICAO' => '2 EIXO DIREITO INTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 27, 'POS_DESCRICAO' => '2 EIXO ESQUERDO EXTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 28, 'POS_DESCRICAO' => '2 EIXO ESQUERDO INTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 29, 'POS_DESCRICAO' => '3 EIXO DIREITO EXTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 30, 'POS_DESCRICAO' => '3 EIXO DIREITO INTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 31, 'POS_DESCRICAO' => '3 EIXO ESQUERDO EXTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 32, 'POS_DESCRICAO' => '3 EIXO ESQUERDO INTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 37, 'POS_DESCRICAO' => '4 EIXO DIREITO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 38, 'POS_DESCRICAO' => '4 EIXO ESQUERDO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 39, 'POS_DESCRICAO' => 'TRUCK ESQUERDO EXTERNO', 'POS_STATUS' => 'A'],
            ['POS_CODIGO' => 40, 'POS_DESCRICAO' => 'TRUCK ESQUERDO INTERNO', 'POS_STATUS' => 'A'],
        ];

        foreach ($rows as $row) {
            DB::table('t_posicao')->updateOrInsert(
                ['POS_CODIGO' => $row['POS_CODIGO']],
                $row
            );
        }
    }

    private function seedConfigurationPositions(): void
    {
        $mapping = [
            1 => [
                ['pos' => 1, 'par' => 2, 'eixo' => 1],
                ['pos' => 2, 'par' => 1, 'eixo' => 1],
                ['pos' => 14, 'par' => 15, 'eixo' => 2],
                ['pos' => 15, 'par' => 14, 'eixo' => 2],
                ['pos' => 16, 'par' => 17, 'eixo' => 2],
                ['pos' => 17, 'par' => 16, 'eixo' => 2],
                ['pos' => 7, 'par' => null, 'eixo' => null],
            ],
            2 => [
                ['pos' => 1, 'par' => 2, 'eixo' => 1],
                ['pos' => 2, 'par' => 1, 'eixo' => 1],
                ['pos' => 14, 'par' => 15, 'eixo' => 2],
                ['pos' => 15, 'par' => 14, 'eixo' => 2],
                ['pos' => 16, 'par' => 17, 'eixo' => 2],
                ['pos' => 17, 'par' => 16, 'eixo' => 2],
                ['pos' => 18, 'par' => 19, 'eixo' => 3],
                ['pos' => 19, 'par' => 18, 'eixo' => 3],
                ['pos' => 39, 'par' => 40, 'eixo' => 3],
                ['pos' => 40, 'par' => 39, 'eixo' => 3],
                ['pos' => 7, 'par' => null, 'eixo' => null],
                ['pos' => 20, 'par' => null, 'eixo' => null],
            ],
            3 => [
                ['pos' => 1, 'par' => 2, 'eixo' => 1],
                ['pos' => 2, 'par' => 1, 'eixo' => 1],
                ['pos' => 14, 'par' => 15, 'eixo' => 2],
                ['pos' => 15, 'par' => 14, 'eixo' => 2],
                ['pos' => 16, 'par' => 17, 'eixo' => 2],
                ['pos' => 17, 'par' => 16, 'eixo' => 2],
                ['pos' => 18, 'par' => 19, 'eixo' => 3],
                ['pos' => 19, 'par' => 18, 'eixo' => 3],
                ['pos' => 39, 'par' => 40, 'eixo' => 3],
                ['pos' => 40, 'par' => 39, 'eixo' => 3],
                ['pos' => 7, 'par' => null, 'eixo' => null],
            ],
            10 => [
                ['pos' => 21, 'par' => 22, 'eixo' => 1],
                ['pos' => 22, 'par' => 21, 'eixo' => 1],
                ['pos' => 23, 'par' => 24, 'eixo' => 1],
                ['pos' => 24, 'par' => 23, 'eixo' => 1],
                ['pos' => 25, 'par' => 26, 'eixo' => 2],
                ['pos' => 26, 'par' => 25, 'eixo' => 2],
                ['pos' => 27, 'par' => 28, 'eixo' => 2],
                ['pos' => 28, 'par' => 27, 'eixo' => 2],
                ['pos' => 7, 'par' => null, 'eixo' => null],
            ],
            11 => [
                ['pos' => 21, 'par' => 22, 'eixo' => 1],
                ['pos' => 22, 'par' => 21, 'eixo' => 1],
                ['pos' => 23, 'par' => 24, 'eixo' => 1],
                ['pos' => 24, 'par' => 23, 'eixo' => 1],
                ['pos' => 25, 'par' => 26, 'eixo' => 2],
                ['pos' => 26, 'par' => 25, 'eixo' => 2],
                ['pos' => 27, 'par' => 28, 'eixo' => 2],
                ['pos' => 28, 'par' => 27, 'eixo' => 2],
                ['pos' => 29, 'par' => 30, 'eixo' => 3],
                ['pos' => 30, 'par' => 29, 'eixo' => 3],
                ['pos' => 31, 'par' => 32, 'eixo' => 3],
                ['pos' => 32, 'par' => 31, 'eixo' => 3],
                ['pos' => 7, 'par' => null, 'eixo' => null],
                ['pos' => 20, 'par' => null, 'eixo' => null],
            ],
            12 => [
                ['pos' => 21, 'par' => 22, 'eixo' => 1],
                ['pos' => 22, 'par' => 21, 'eixo' => 1],
                ['pos' => 23, 'par' => 24, 'eixo' => 1],
                ['pos' => 24, 'par' => 23, 'eixo' => 1],
                ['pos' => 25, 'par' => 26, 'eixo' => 2],
                ['pos' => 26, 'par' => 25, 'eixo' => 2],
                ['pos' => 27, 'par' => 28, 'eixo' => 2],
                ['pos' => 28, 'par' => 27, 'eixo' => 2],
                ['pos' => 29, 'par' => 30, 'eixo' => 3],
                ['pos' => 30, 'par' => 29, 'eixo' => 3],
                ['pos' => 31, 'par' => 32, 'eixo' => 3],
                ['pos' => 32, 'par' => 31, 'eixo' => 3],
                ['pos' => 37, 'par' => 38, 'eixo' => 4],
                ['pos' => 38, 'par' => 37, 'eixo' => 4],
                ['pos' => 7, 'par' => null, 'eixo' => null],
                ['pos' => 20, 'par' => null, 'eixo' => null],
            ],
        ];

        $nextCode = (int) DB::table('t_posicaoxconfiguracao')->max('PSCF_CODIGO');
        $nextCode = $nextCode > 0 ? $nextCode + 1 : 1;

        foreach ($mapping as $veicCodigo => $items) {
            foreach ($items as $item) {
                $existing = DB::table('t_posicaoxconfiguracao')
                    ->where('VEIC_CODIGO', $veicCodigo)
                    ->where('POS_CODIGO', $item['pos'])
                    ->first();

                if ($existing !== null) {
                    DB::table('t_posicaoxconfiguracao')
                        ->where('PSCF_CODIGO', $existing->PSCF_CODIGO)
                        ->update([
                            'PSCF_PAR' => $item['par'],
                            'PSCF_EIXO' => $item['eixo'],
                        ]);
                    continue;
                }

                DB::table('t_posicaoxconfiguracao')->insert([
                    'PSCF_CODIGO' => $nextCode,
                    'VEIC_CODIGO' => $veicCodigo,
                    'POS_CODIGO' => $item['pos'],
                    'PSCF_PAR' => $item['par'],
                    'PSCF_EIXO' => $item['eixo'],
                ]);

                $nextCode++;
            }
        }
    }
}
