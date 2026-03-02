<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastros\TipoPneuStoreRequest;
use App\Http\Requests\Cadastros\TipoPneuUpdateRequest;
use App\Models\Legacy\DesenhoBanda;
use App\Models\Legacy\MarcaPneu;
use App\Models\Legacy\MedidaPneu;
use App\Models\Legacy\ModeloPneu;
use App\Models\Legacy\TipoPneu;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TipoPneuController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('admin');

        return Inertia::render('cadastros/configuracao-pneu/index', [
            'tipos' => TipoPneu::query()
                ->join('t_marcapneu', 't_tipo.MARP_CODIGO', '=', 't_marcapneu.MARP_CODIGO')
                ->join('t_modelopneu', 't_tipo.MODP_CODIGO', '=', 't_modelopneu.MODP_CODIGO')
                ->join('t_medidapneu', 't_tipo.MEDP_CODIGO', '=', 't_medidapneu.MEDP_CODIGO')
                ->leftJoin('t_desenhobanda', 't_tipo.TIPO_DESENHO', '=', 't_desenhobanda.DESB_SIGLA')
                ->select([
                    't_tipo.TIPO_CODIGO',
                    't_tipo.TIPO_STATUS',
                    't_tipo.TIPO_DESCRICAO',
                    't_tipo.TIPO_INSPECAO',
                    't_tipo.MARP_CODIGO',
                    't_tipo.MODP_CODIGO',
                    't_tipo.MEDP_CODIGO',
                    't_tipo.TIPO_DESENHO',
                    't_tipo.TIPO_NSULCO',
                    't_tipo.TIPO_MMSEGURANCA',
                    't_tipo.TIPO_MMNOVO',
                    't_tipo.TIPO_MMDESGEIXOS',
                    't_tipo.TIPO_MMDESGPAR',
                    't_marcapneu.MARP_DESCRICAO as MARCA_DESCRICAO',
                    't_modelopneu.MODP_DESCRICAO as MODELO_DESCRICAO',
                    't_medidapneu.MEDP_DESCRICAO as MEDIDA_DESCRICAO',
                    't_desenhobanda.DESB_DESCRICAO as DESENHO_DESCRICAO',
                ])
                ->orderByDesc('t_tipo.TIPO_CODIGO')
                ->get(),
            'marcas' => MarcaPneu::query()
                ->where('MARP_STATUS', 'A')
                ->select(['MARP_CODIGO', 'MARP_DESCRICAO', 'MARP_TIPO'])
                ->orderBy('MARP_DESCRICAO')
                ->get(),
            'modelos' => ModeloPneu::query()
                ->where('MODP_STATUS', 'A')
                ->select(['MODP_CODIGO', 'MODP_DESCRICAO', 'MARP_CODIGO'])
                ->orderBy('MODP_DESCRICAO')
                ->get(),
            'medidas' => MedidaPneu::query()
                ->where('MEDP_STATUS', 'A')
                ->select(['MEDP_CODIGO', 'MEDP_DESCRICAO', 'CAL_RECOMENDADA'])
                ->orderBy('MEDP_DESCRICAO')
                ->get(),
            'desenhos' => DesenhoBanda::query()
                ->where('DESB_STATUS', 'A')
                ->select(['DESB_CODIGO', 'DESB_DESCRICAO', 'DESB_SIGLA'])
                ->orderBy('DESB_DESCRICAO')
                ->get(),
        ]);
    }

    public function store(TipoPneuStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! $this->modeloPertenceMarca($data['MODP_CODIGO'], $data['MARP_CODIGO'])) {
            return $this->validationError('Modelo selecionado nao pertence a marca informada.');
        }

        if (! $this->desenhoAtivo($data['TIPO_DESENHO'])) {
            return $this->validationError('Desenho de banda selecionado esta inativo.');
        }

        if ($this->hasDuplicate($data['MARP_CODIGO'], $data['MODP_CODIGO'], $data['MEDP_CODIGO'])) {
            return $this->conflict('Ja existe configuracao com a combinacao de marca, modelo e medida.');
        }

        $authUser = $request->user();
        $usuCodigo = $authUser instanceof User
            ? $authUser->ensureLegacyUserCode()
            : 0;

        if ($usuCodigo <= 0) {
            return response()->json(['message' => 'Usuario autenticado sem USU_CODIGO valido.'], 422);
        }

        $modelo = ModeloPneu::query()->findOrFail($data['MODP_CODIGO']);
        $medida = MedidaPneu::query()->findOrFail($data['MEDP_CODIGO']);

        $tipo = TipoPneu::create([
            'TIPO_STATUS' => $data['TIPO_STATUS'] ?? 'A',
            'TIPO_DESCRICAO' => $this->buildDescricao($modelo->MODP_DESCRICAO, $medida->MEDP_DESCRICAO, $data['TIPO_DESENHO']),
            'TIPO_INSPECAO' => $data['TIPO_INSPECAO'],
            'MARP_CODIGO' => $data['MARP_CODIGO'],
            'MODP_CODIGO' => $data['MODP_CODIGO'],
            'MEDP_CODIGO' => $data['MEDP_CODIGO'],
            'TIPO_DESENHO' => $data['TIPO_DESENHO'],
            'TIPO_NSULCO' => $data['TIPO_NSULCO'],
            'TIPO_MMSEGURANCA' => $data['TIPO_MMSEGURANCA'],
            'TIPO_MMNOVO' => $data['TIPO_MMNOVO'],
            'TIPO_MMDESGEIXOS' => $data['TIPO_MMDESGEIXOS'] ?? null,
            'TIPO_MMDESGPAR' => $data['TIPO_MMDESGPAR'] ?? null,
            'USU_CODIGO' => $usuCodigo,
            'TIPO_DATACADASTRO' => now(),
        ]);

        return response()->json([
            'message' => 'Configuracao de pneu cadastrada com sucesso.',
            'tipo' => $tipo,
        ], 201);
    }

    public function update(TipoPneuUpdateRequest $request, int $id): JsonResponse
    {
        $tipo = TipoPneu::query()->findOrFail($id);
        $data = $request->validated();

        if (! $this->modeloPertenceMarca($data['MODP_CODIGO'], $data['MARP_CODIGO'])) {
            return $this->validationError('Modelo selecionado nao pertence a marca informada.');
        }

        if (! $this->desenhoAtivo($data['TIPO_DESENHO'])) {
            return $this->validationError('Desenho de banda selecionado esta inativo.');
        }

        if ($this->hasDuplicate($data['MARP_CODIGO'], $data['MODP_CODIGO'], $data['MEDP_CODIGO'], $tipo->TIPO_CODIGO)) {
            return $this->conflict('Ja existe configuracao com a combinacao de marca, modelo e medida.');
        }

        $modelo = ModeloPneu::query()->findOrFail($data['MODP_CODIGO']);
        $medida = MedidaPneu::query()->findOrFail($data['MEDP_CODIGO']);

        $tipo->update([
            'TIPO_STATUS' => $data['TIPO_STATUS'],
            'TIPO_DESCRICAO' => $this->buildDescricao($modelo->MODP_DESCRICAO, $medida->MEDP_DESCRICAO, $data['TIPO_DESENHO']),
            'TIPO_INSPECAO' => $data['TIPO_INSPECAO'],
            'MARP_CODIGO' => $data['MARP_CODIGO'],
            'MODP_CODIGO' => $data['MODP_CODIGO'],
            'MEDP_CODIGO' => $data['MEDP_CODIGO'],
            'TIPO_DESENHO' => $data['TIPO_DESENHO'],
            'TIPO_NSULCO' => $data['TIPO_NSULCO'],
            'TIPO_MMSEGURANCA' => $data['TIPO_MMSEGURANCA'],
            'TIPO_MMNOVO' => $data['TIPO_MMNOVO'],
            'TIPO_MMDESGEIXOS' => $data['TIPO_MMDESGEIXOS'] ?? null,
            'TIPO_MMDESGPAR' => $data['TIPO_MMDESGPAR'] ?? null,
        ]);

        return response()->json([
            'message' => 'Configuracao de pneu atualizada com sucesso.',
            'tipo' => $tipo->fresh(),
        ]);
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        Gate::authorize('admin');

        $tipo = TipoPneu::query()->findOrFail($id);

        if ($tipo->TIPO_STATUS === 'A' && $this->hasLinkedPneus($tipo->TIPO_CODIGO)) {
            return $this->conflict('Nao e possivel inativar: existem pneus vinculados a esta configuracao.');
        }

        $tipo->TIPO_STATUS = $tipo->TIPO_STATUS === 'A' ? 'I' : 'A';
        $tipo->save();

        return response()->json([
            'message' => $tipo->TIPO_STATUS === 'A'
                ? 'Configuracao ativada com sucesso.'
                : 'Configuracao inativada com sucesso.',
            'status' => $tipo->TIPO_STATUS,
        ]);
    }

    private function hasDuplicate(int $marcaCodigo, int $modeloCodigo, int $medidaCodigo, ?int $ignoreId = null): bool
    {
        $query = TipoPneu::query()
            ->where('MARP_CODIGO', $marcaCodigo)
            ->where('MODP_CODIGO', $modeloCodigo)
            ->where('MEDP_CODIGO', $medidaCodigo);

        if ($ignoreId !== null) {
            $query->where('TIPO_CODIGO', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function modeloPertenceMarca(int $modeloCodigo, int $marcaCodigo): bool
    {
        return ModeloPneu::query()
            ->where('MODP_CODIGO', $modeloCodigo)
            ->where('MARP_CODIGO', $marcaCodigo)
            ->exists();
    }

    private function hasLinkedPneus(int $tipoId): bool
    {
        return DB::table('t_pneu')
            ->where('TIPO_CODIGO', $tipoId)
            ->where('PNE_STATUS', '!=', 'B')
            ->exists();
    }

    private function desenhoAtivo(string $sigla): bool
    {
        return DesenhoBanda::query()
            ->where('DESB_SIGLA', $sigla)
            ->where('DESB_STATUS', 'A')
            ->exists();
    }

    private function buildDescricao(string $modeloDescricao, string $medidaDescricao, string $desenho): string
    {
        return mb_strtoupper(trim("{$modeloDescricao} {$medidaDescricao} {$desenho}"));
    }

    private function conflict(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 409);
    }

    private function validationError(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 422);
    }
}
