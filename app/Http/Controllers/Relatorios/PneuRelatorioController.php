<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\Legacy\StatusPneu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PneuRelatorioController extends Controller
{
    private function buildQuery(Request $request): \Illuminate\Database\Query\Builder
    {
        $query = DB::table('t_pneu as p')
            ->join('t_clienteunidade as u', 'p.UNI_CODIGO', '=', 'u.UNI_CODIGO')
            ->join('t_tipo as tc', 'p.TIPO_CODIGO', '=', 'tc.TIPO_CODIGO')
            ->join('t_marcapneu as mc', 'tc.MARP_CODIGO', '=', 'mc.MARP_CODIGO')
            ->join('t_modelopneu as mod', 'tc.MODP_CODIGO', '=', 'mod.MODP_CODIGO')
            ->join('t_medidapneu as med', 'tc.MEDP_CODIGO', '=', 'med.MEDP_CODIGO')
            ->leftJoin('t_statuspneu as sp', 'p.PNE_STATUS', '=', 'sp.STP_SIGLA')
            ->leftJoin('t_tipo as tr', 'p.TIPO_CODIGORECAPE', '=', 'tr.TIPO_CODIGO')
            ->leftJoin('t_marcapneu as mr', 'tr.MARP_CODIGO', '=', 'mr.MARP_CODIGO')
            ->select([
                'p.PNE_CODIGO',
                'p.PNE_FOGO',
                'u.UNI_DESCRICAO',
                'mc.MARP_DESCRICAO as MARCA_CARCACA',
                'mod.MODP_DESCRICAO as MODELO_CARCACA',
                'med.MEDP_DESCRICAO as MEDIDA',
                'tc.TIPO_DESCRICAO as SKU_CARCACA',
                'p.PNE_STATUS',
                DB::raw('COALESCE(sp.STP_DESCRICAO, p.PNE_STATUS) as STATUS_DESCRICAO'),
                'p.PNE_STATUSCOMPRA',
                'p.PNE_VIDACOMPRA',
                'p.PNE_VIDAATUAL',
                'p.PNE_DOT',
                'p.PNE_VALORCOMPRA',
                'p.PNE_CUSTOATUAL',
                'p.PNE_MM',
                'p.PNE_KM',
                'mr.MARP_DESCRICAO as MARCA_RECAPAGEM',
                'tr.TIPO_DESCRICAO as SKU_RECAPAGEM',
                'tc.TIPO_MMNOVO',
                'tc.TIPO_MMSEGURANCA',
            ]);

        if ($request->filled('unidade')) {
            $query->where('p.UNI_CODIGO', $request->input('unidade'));
        }

        if ($request->filled('status')) {
            $query->where('p.PNE_STATUS', $request->input('status'));
        }

        if ($request->filled('vida')) {
            $query->where('p.PNE_VIDAATUAL', $request->input('vida'));
        }

        if ($request->filled('marca')) {
            $query->where('mc.MARP_CODIGO', $request->input('marca'));
        }

        if ($request->filled('fogo')) {
            $query->whereRaw('UPPER(p.PNE_FOGO) LIKE ?', ['%' . mb_strtoupper((string) $request->input('fogo')) . '%']);
        }

        return $query->orderBy('u.UNI_DESCRICAO')->orderBy('p.PNE_FOGO');
    }

    public function index(Request $request): InertiaResponse
    {
        Gate::authorize('admin');

        $pneus = $this->buildQuery($request)->limit(2000)->get();

        $unidades = DB::table('t_clienteunidade')
            ->orderBy('UNI_DESCRICAO')
            ->select('UNI_CODIGO', 'UNI_DESCRICAO')
            ->get();

        $marcas = DB::table('t_marcapneu')
            ->where('MARP_STATUS', 'A')
            ->orderBy('MARP_DESCRICAO')
            ->select('MARP_CODIGO', 'MARP_DESCRICAO')
            ->get();

        $statuses = StatusPneu::orderBy('STP_ORDEM')->get(['STP_SIGLA', 'STP_DESCRICAO']);

        return Inertia::render('relatorios/pneus/index', [
            'pneus' => $pneus,
            'filtros' => $request->only(['unidade', 'status', 'vida', 'marca', 'fogo']),
            'unidades' => $unidades,
            'marcas' => $marcas,
            'statuses' => $statuses,
        ]);
    }

    public function exportarExcel(Request $request): StreamedResponse
    {
        Gate::authorize('admin');

        $pneus = $this->buildQuery($request)->get();

        $filename = 'relatorio-pneus-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($pneus) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM para abrir corretamente no Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Codigo', 'Fogo', 'Unidade', 'Marca', 'Modelo', 'Medida', 'SKU Carcaca',
                'Status', 'Status Compra', 'Vida Compra', 'Vida Atual',
                'DOT', 'Valor Compra (R$)', 'Custo Atual (R$)', 'MM Atual', 'KM Atual',
                'Marca Recapagem', 'SKU Recapagem', 'MM Novo', 'MM Seguranca',
            ], ';');

            foreach ($pneus as $pneu) {
                fputcsv($handle, [
                    $pneu->PNE_CODIGO,
                    $pneu->PNE_FOGO,
                    $pneu->UNI_DESCRICAO,
                    $pneu->MARCA_CARCACA,
                    $pneu->MODELO_CARCACA,
                    $pneu->MEDIDA,
                    $pneu->SKU_CARCACA,
                    $pneu->STATUS_DESCRICAO,
                    $pneu->PNE_STATUSCOMPRA === 'N' ? 'Novo' : 'Usado',
                    $pneu->PNE_VIDACOMPRA,
                    $pneu->PNE_VIDAATUAL,
                    $pneu->PNE_DOT ?? '',
                    number_format((float) $pneu->PNE_VALORCOMPRA, 2, ',', '.'),
                    number_format((float) $pneu->PNE_CUSTOATUAL, 2, ',', '.'),
                    number_format((float) $pneu->PNE_MM, 1, ',', '.'),
                    (int) $pneu->PNE_KM,
                    $pneu->MARCA_RECAPAGEM ?? '',
                    $pneu->SKU_RECAPAGEM ?? '',
                    $pneu->TIPO_MMNOVO ?? '',
                    $pneu->TIPO_MMSEGURANCA ?? '',
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
