<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

class PneuConsultaController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('admin');

        $filtroFogo = mb_strtoupper(trim((string) $request->query('fogo', '')));
        $pneu = null;
        $movimentacoes = [];
        $alertas = [];
        $vidas = [];

        if ($filtroFogo !== '') {
            $pneu = DB::table('t_pneu')
                ->join('t_clienteunidade', 't_pneu.UNI_CODIGO', '=', 't_clienteunidade.UNI_CODIGO')
                ->join('t_tipo as tipo_carcaca', 't_pneu.TIPO_CODIGO', '=', 'tipo_carcaca.TIPO_CODIGO')
                ->leftJoin('t_tipo as tipo_recape', 't_pneu.TIPO_CODIGORECAPE', '=', 'tipo_recape.TIPO_CODIGO')
                ->whereRaw('UPPER(t_pneu.PNE_FOGO) = ?', [$filtroFogo])
                ->select([
                    't_pneu.PNE_CODIGO',
                    't_pneu.PNE_FOGO',
                    't_pneu.UNI_CODIGO',
                    't_clienteunidade.UNI_DESCRICAO',
                    't_pneu.TIPO_CODIGO',
                    'tipo_carcaca.TIPO_DESCRICAO as SKU_CARCACA',
                    'tipo_recape.TIPO_DESCRICAO as SKU_RECAPE',
                    't_pneu.PNE_STATUS',
                    't_pneu.PNE_STATUSCOMPRA',
                    't_pneu.PNE_VIDACOMPRA',
                    't_pneu.PNE_VIDAATUAL',
                    't_pneu.PNE_DOT',
                    't_pneu.PNE_VALORCOMPRA',
                    't_pneu.PNE_CUSTOATUAL',
                    't_pneu.PNE_MM',
                    't_pneu.PNE_KM',
                    't_pneu.TIPO_CODIGORECAPE',
                    'tipo_carcaca.TIPO_MMSEGURANCA',
                    'tipo_carcaca.TIPO_MMNOVO',
                ])
                ->orderByDesc('t_pneu.PNE_CODIGO')
                ->first();

            if ($pneu !== null) {
                $movimentacoes = DB::table('t_movimentacao')
                    ->where('PNE_CODIGO', $pneu->PNE_CODIGO)
                    ->select([
                        'MOV_CODIGO',
                        'MOV_DATA',
                        'MOV_OPERACAO',
                        'MOV_KMPNEU',
                        'MOV_KMVEICULO',
                        'MOV_MM_MINIMA',
                        'MOV_COMENTARIO',
                        'POS_CODIGO',
                        'VEI_CODIGO',
                    ])
                    ->orderByDesc('MOV_CODIGO')
                    ->get()
                    ->all();

                $vidas = $this->buildVidas($pneu);
                $alertas = $this->buildAlertas($pneu);
            }
        }

        return Inertia::render('cadastros/pneu-consulta/index', [
            'filtroFogo' => $filtroFogo,
            'pneu' => $pneu,
            'movimentacoes' => $movimentacoes,
            'alertas' => $alertas,
            'vidas' => $vidas,
        ]);
    }

    private function buildVidas(object $pneu): array
    {
        $vidas = [[
            'VIPN_CODIGO' => null,
            'VIDA' => $pneu->PNE_VIDAATUAL,
            'TIPO_CODIGO' => $pneu->TIPO_CODIGO,
            'SKU' => $pneu->SKU_CARCACA,
            'KM' => $pneu->PNE_KM,
            'MM' => $pneu->PNE_MM,
            'CUSTO' => $pneu->PNE_CUSTOATUAL,
            'DATA_EVENTO' => null,
        ]];

        if (! Schema::hasTable('t_vidapneu')) {
            return $vidas;
        }

        $historico = DB::table('t_vidapneu as vp')
            ->leftJoin('t_tipo as t', 'vp.TIPO_CODIGO', '=', 't.TIPO_CODIGO')
            ->leftJoin('t_retornopneu as r', 'vp.RETPNE_CODIGO', '=', 'r.RETPNE_CODIGO')
            ->where('vp.PNE_CODIGO', $pneu->PNE_CODIGO)
            ->select([
                'vp.VIPN_CODIGO',
                'vp.VIPN_VIDA as VIDA',
                'vp.TIPO_CODIGO',
                't.TIPO_DESCRICAO as SKU',
                'vp.VIPN_KM as KM',
                'vp.VIPN_MM as MM',
                'vp.VIPN_CUSTO as CUSTO',
                'r.RETPNE_DATA as DATA_EVENTO',
            ])
            ->orderByDesc('vp.VIPN_CODIGO')
            ->get()
            ->all();

        return array_merge($vidas, $historico);
    }

    private function buildAlertas(object $pneu): array
    {
        $alertas = [];

        if ((string) $pneu->PNE_STATUS !== 'D') {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Status operacional',
                'mensagem' => "Pneu com status '{$pneu->PNE_STATUS}'.",
            ];
        }

        if ($pneu->TIPO_MMSEGURANCA !== null && (float) $pneu->PNE_MM <= (float) $pneu->TIPO_MMSEGURANCA) {
            $alertas[] = [
                'tipo' => 'critical',
                'titulo' => 'MM abaixo da seguranca',
                'mensagem' => 'A milimetragem atual atingiu ou ficou abaixo do minimo de seguranca.',
            ];
        }

        if ($pneu->PNE_DOT !== null && strlen((string) $pneu->PNE_DOT) === 4) {
            $year = 2000 + (int) substr((string) $pneu->PNE_DOT, 2, 2);
            $currentYear = (int) now()->format('Y');

            if (($currentYear - $year) >= 5) {
                $alertas[] = [
                    'tipo' => 'info',
                    'titulo' => 'DOT antigo',
                    'mensagem' => 'DOT indica fabricacao com 5 anos ou mais.',
                ];
            }
        }

        if (str_starts_with((string) $pneu->PNE_VIDAATUAL, 'R') && ((int) ($pneu->TIPO_CODIGORECAPE ?? 0) <= 0)) {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Vida recapada sem SKU de recapagem',
                'mensagem' => 'A vida atual e recapada, mas nao ha SKU de recapagem vinculado.',
            ];
        }

        if ($alertas === []) {
            $alertas[] = [
                'tipo' => 'info',
                'titulo' => 'Sem alertas',
                'mensagem' => 'Nenhum alerta ativo para este pneu.',
            ];
        }

        return $alertas;
    }
}
