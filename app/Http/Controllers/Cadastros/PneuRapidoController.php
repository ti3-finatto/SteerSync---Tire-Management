<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastros\PneuRapidoStoreRequest;
use App\Models\Legacy\MarcaPneu;
use App\Models\Legacy\Pneu;
use App\Models\Legacy\TipoPneu;
use App\Models\Legacy\Unidade;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PneuRapidoController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('admin');

        return Inertia::render('cadastros/pneu-rapido/index', [
            'unidades' => Unidade::query()
                ->where('UNI_STATUS', 'A')
                ->select(['UNI_CODIGO', 'UNI_DESCRICAO'])
                ->orderBy('UNI_DESCRICAO')
                ->get(),
            'marcasCarcaca' => MarcaPneu::query()
                ->where('MARP_STATUS', 'A')
                ->where('MARP_TIPO', 'P')
                ->select(['MARP_CODIGO', 'MARP_DESCRICAO', 'MARP_TIPO'])
                ->orderBy('MARP_DESCRICAO')
                ->get(),
            'marcasRecapagem' => MarcaPneu::query()
                ->where('MARP_STATUS', 'A')
                ->where('MARP_TIPO', 'R')
                ->select(['MARP_CODIGO', 'MARP_DESCRICAO', 'MARP_TIPO'])
                ->orderBy('MARP_DESCRICAO')
                ->get(),
            'tiposCarcaca' => TipoPneu::query()
                ->join('t_marcapneu', 't_tipo.MARP_CODIGO', '=', 't_marcapneu.MARP_CODIGO')
                ->join('t_modelopneu', 't_tipo.MODP_CODIGO', '=', 't_modelopneu.MODP_CODIGO')
                ->join('t_medidapneu', 't_tipo.MEDP_CODIGO', '=', 't_medidapneu.MEDP_CODIGO')
                ->where('t_tipo.TIPO_STATUS', 'A')
                ->where('t_marcapneu.MARP_TIPO', 'P')
                ->select([
                    't_tipo.TIPO_CODIGO',
                    't_tipo.MARP_CODIGO',
                    't_tipo.TIPO_DESCRICAO',
                    't_tipo.TIPO_MMNOVO',
                    't_medidapneu.CAL_RECOMENDADA',
                    't_marcapneu.MARP_DESCRICAO as MARCA_DESCRICAO',
                    't_modelopneu.MODP_DESCRICAO as MODELO_DESCRICAO',
                    't_medidapneu.MEDP_DESCRICAO as MEDIDA_DESCRICAO',
                ])
                ->orderBy('t_tipo.TIPO_DESCRICAO')
                ->get(),
            'tiposRecapagem' => TipoPneu::query()
                ->join('t_marcapneu', 't_tipo.MARP_CODIGO', '=', 't_marcapneu.MARP_CODIGO')
                ->join('t_modelopneu', 't_tipo.MODP_CODIGO', '=', 't_modelopneu.MODP_CODIGO')
                ->join('t_medidapneu', 't_tipo.MEDP_CODIGO', '=', 't_medidapneu.MEDP_CODIGO')
                ->where('t_tipo.TIPO_STATUS', 'A')
                ->where('t_marcapneu.MARP_TIPO', 'R')
                ->select([
                    't_tipo.TIPO_CODIGO',
                    't_tipo.MARP_CODIGO',
                    't_tipo.TIPO_DESCRICAO',
                    't_marcapneu.MARP_DESCRICAO as MARCA_DESCRICAO',
                    't_modelopneu.MODP_DESCRICAO as MODELO_DESCRICAO',
                    't_medidapneu.MEDP_DESCRICAO as MEDIDA_DESCRICAO',
                ])
                ->orderBy('t_tipo.TIPO_DESCRICAO')
                ->get(),
        ]);
    }

    public function store(PneuRapidoStoreRequest $request): JsonResponse
    {
        $data = $request->validated();
        $vida = $data['PNE_VIDA'];
        $isRecapado = str_starts_with($vida, 'R');

        $tipoCarcaca = $this->findTipoAtivo($data['TIPO_CODIGO'], 'P');
        if ($tipoCarcaca === null) {
            return $this->validationError('SKU da carcaca invalido ou inativo.');
        }

        if (! $this->unidadeAtiva($data['UNI_CODIGO'])) {
            return $this->validationError('Unidade informada esta inativa ou nao existe.');
        }

        if ($this->fogoJaExisteNaUnidade((int) $data['UNI_CODIGO'], (string) $data['PNE_FOGO'])) {
            return response()->json(['message' => 'Ja existe pneu com este numero de fogo nesta unidade.'], 409);
        }

        if (($data['MARP_CODIGO'] ?? null) !== null && (int) $data['MARP_CODIGO'] !== (int) $tipoCarcaca->MARP_CODIGO) {
            return $this->validationError('SKU da carcaca nao pertence a marca selecionada.');
        }

        $tipoCodRecape = 0;
        $valorRecapagem = 0.0;

        if ($isRecapado) {
            $tipoRecape = $this->findTipoAtivo((int) $data['TIPO_CODIGORECAPE'], 'R');
            if ($tipoRecape === null) {
                return $this->validationError('SKU da recapagem invalido ou inativo.');
            }

            if ((int) $data['MARP_CODIGO_RECAPE'] !== (int) $tipoRecape->MARP_CODIGO) {
                return $this->validationError('SKU da recapagem nao pertence a marca de recapagem selecionada.');
            }

            $tipoCodRecape = (int) $tipoRecape->TIPO_CODIGO;
            $valorRecapagem = (float) ($data['PNE_VALORRECAPAGEM'] ?? 0);
        }

        $authUser = $request->user();
        $usuCodigo = $authUser instanceof User ? $authUser->ensureLegacyUserCode() : 0;
        $userId = $authUser instanceof User ? (int) $authUser->getAuthIdentifier() : null;

        if ($usuCodigo <= 0) {
            return response()->json(['message' => 'Usuario autenticado sem USU_CODIGO valido.'], 422);
        }

        $pneu = DB::transaction(function () use ($data, $tipoCarcaca, $tipoCodRecape, $valorRecapagem, $vida, $usuCodigo, $userId) {
            return Pneu::create([
                'PNE_FOGO' => $data['PNE_FOGO'],
                'TIPO_CODIGO' => (int) $tipoCarcaca->TIPO_CODIGO,
                'CAL_RECOMENDADA' => $tipoCarcaca->CAL_RECOMENDADA,
                'PNE_DOT' => $data['PNE_DOT'] ?? null,
                'PNE_KM' => (int) ($data['PNE_KM'] ?? 0),
                'PNE_MM' => (float) ($data['PNE_MM'] ?? $tipoCarcaca->TIPO_MMNOVO ?? 0),
                'PNE_STATUS' => 'D',
                'PNE_STATUSCOMPRA' => $data['PNE_STATUSCOMPRA'],
                'PNE_VALORCOMPRA' => (float) $data['PNE_VALORCOMPRA'],
                'PNE_VIDACOMPRA' => $vida,
                'PNE_VIDAATUAL' => $vida,
                'TIPO_CODIGORECAPE' => $tipoCodRecape,
                'ITS_CODIGO' => null,
                'PNE_CUSTOATUAL' => $valorRecapagem,
                'USU_CODIGO' => $usuCodigo,
                'UNI_CODIGO' => (int) $data['UNI_CODIGO'],
                'user_id' => $userId,
            ]);
        })->fresh();

        return response()->json([
            'message' => 'Pneu cadastrado com sucesso.',
            'pneu' => $pneu,
        ], 201);
    }

    private function unidadeAtiva(int $unidadeId): bool
    {
        return Unidade::query()
            ->where('UNI_CODIGO', $unidadeId)
            ->where('UNI_STATUS', 'A')
            ->exists();
    }

    private function findTipoAtivo(int $tipoCodigo, string $marcaTipo): ?object
    {
        return TipoPneu::query()
            ->join('t_marcapneu', 't_tipo.MARP_CODIGO', '=', 't_marcapneu.MARP_CODIGO')
            ->leftJoin('t_medidapneu', 't_tipo.MEDP_CODIGO', '=', 't_medidapneu.MEDP_CODIGO')
            ->where('t_tipo.TIPO_CODIGO', $tipoCodigo)
            ->where('t_tipo.TIPO_STATUS', 'A')
            ->where('t_marcapneu.MARP_TIPO', $marcaTipo)
            ->select([
                't_tipo.TIPO_CODIGO',
                't_tipo.MARP_CODIGO',
                't_tipo.TIPO_MMNOVO',
                't_medidapneu.CAL_RECOMENDADA',
            ])
            ->first();
    }

    private function fogoJaExisteNaUnidade(int $unidadeId, string $fogo): bool
    {
        return Pneu::query()
            ->where('UNI_CODIGO', $unidadeId)
            ->whereRaw('UPPER(PNE_FOGO) = ?', [mb_strtoupper($fogo)])
            ->exists();
    }

    private function validationError(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 422);
    }
}
