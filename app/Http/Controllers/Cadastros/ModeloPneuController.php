<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastros\ModeloPneuStoreRequest;
use App\Http\Requests\Cadastros\ModeloPneuUpdateRequest;
use App\Models\Legacy\MarcaPneu;
use App\Models\Legacy\ModeloPneu;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ModeloPneuController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('admin');

        return Inertia::render('cadastros/modelo-pneu/index', [
            'modelos' => ModeloPneu::query()
                ->join('t_marcapneu', 't_modelopneu.MARP_CODIGO', '=', 't_marcapneu.MARP_CODIGO')
                ->select([
                    't_modelopneu.MODP_CODIGO',
                    't_modelopneu.MODP_DESCRICAO',
                    't_modelopneu.MODP_STATUS',
                    't_modelopneu.MARP_CODIGO',
                    't_marcapneu.MARP_DESCRICAO as MARCA_DESCRICAO',
                    't_marcapneu.MARP_TIPO as MARCA_TIPO',
                ])
                ->orderByDesc('t_modelopneu.MODP_CODIGO')
                ->get(),
            'marcas' => MarcaPneu::query()
                ->where('MARP_STATUS', 'A')
                ->select(['MARP_CODIGO', 'MARP_DESCRICAO', 'MARP_TIPO'])
                ->orderBy('MARP_DESCRICAO')
                ->get(),
        ]);
    }

    public function store(ModeloPneuStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($this->hasDuplicate($data['MARP_CODIGO'], $data['MODP_DESCRICAO'])) {
            return $this->conflict('Ja existe modelo com esta descricao para a marca selecionada.');
        }

        $authUser = $request->user();
        $usuCodigo = $authUser instanceof User
            ? $authUser->ensureLegacyUserCode()
            : 0;

        if ($usuCodigo <= 0) {
            return response()->json(['message' => 'Usuario autenticado sem USU_CODIGO valido.'], 422);
        }

        $modelo = ModeloPneu::create([
            'MODP_DESCRICAO' => $data['MODP_DESCRICAO'],
            'MARP_CODIGO' => $data['MARP_CODIGO'],
            'MODP_STATUS' => $data['MODP_STATUS'] ?? 'A',
            'USU_CODIGO' => $usuCodigo,
        ]);

        return response()->json([
            'message' => 'Modelo de pneu cadastrado com sucesso.',
            'modelo' => $modelo,
        ], 201);
    }

    public function update(ModeloPneuUpdateRequest $request, int $id): JsonResponse
    {
        $modelo = ModeloPneu::query()->findOrFail($id);
        $data = $request->validated();

        if ($this->hasDuplicate($data['MARP_CODIGO'], $data['MODP_DESCRICAO'], $modelo->MODP_CODIGO)) {
            return $this->conflict('Ja existe modelo com esta descricao para a marca selecionada.');
        }

        $modelo->update([
            'MODP_DESCRICAO' => $data['MODP_DESCRICAO'],
            'MARP_CODIGO' => $data['MARP_CODIGO'],
            'MODP_STATUS' => $data['MODP_STATUS'],
        ]);

        return response()->json([
            'message' => 'Modelo de pneu atualizado com sucesso.',
            'modelo' => $modelo->fresh(),
        ]);
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        Gate::authorize('admin');

        $modelo = ModeloPneu::query()->findOrFail($id);

        if ($modelo->MODP_STATUS === 'A' && $this->hasLinkedPneus($modelo->MODP_CODIGO)) {
            return $this->conflict('Nao e possivel inativar: existem pneus vinculados a este modelo.');
        }

        $modelo->MODP_STATUS = $modelo->MODP_STATUS === 'A' ? 'I' : 'A';
        $modelo->save();

        return response()->json([
            'message' => $modelo->MODP_STATUS === 'A'
                ? 'Modelo ativado com sucesso.'
                : 'Modelo inativado com sucesso.',
            'status' => $modelo->MODP_STATUS,
        ]);
    }

    private function hasDuplicate(int $marcaCodigo, string $descricao, ?int $ignoreId = null): bool
    {
        $query = ModeloPneu::query()
            ->where('MARP_CODIGO', $marcaCodigo)
            ->whereRaw('UPPER(MODP_DESCRICAO) = ?', [mb_strtoupper($descricao)]);

        if ($ignoreId !== null) {
            $query->where('MODP_CODIGO', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function hasLinkedPneus(int $modeloId): bool
    {
        return DB::table('t_tipo')
            ->join('t_pneu', 't_pneu.TIPO_CODIGO', '=', 't_tipo.TIPO_CODIGO')
            ->where('t_tipo.MODP_CODIGO', $modeloId)
            ->where('t_pneu.PNE_STATUS', '!=', 'B')
            ->exists();
    }

    private function conflict(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 409);
    }
}
