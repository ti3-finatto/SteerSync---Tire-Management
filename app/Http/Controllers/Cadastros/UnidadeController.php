<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastros\UnidadeStoreRequest;
use App\Http\Requests\Cadastros\UnidadeUpdateRequest;
use App\Models\Legacy\Unidade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class UnidadeController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('admin');

        $unidades = Unidade::query()
            ->select([
                'UNI_CODIGO',
                'UNI_DESCRICAO',
                'UNI_STATUS',
                'CLI_CNPJ',
                'CLI_UF',
                'CLI_CIDADE',
            ])
            ->selectSub(
                DB::table('t_pneu as p')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('p.UNI_CODIGO', 't_clienteunidade.UNI_CODIGO'),
                'pneus_count',
            )
            ->selectSub(
                DB::table('t_veiculo as v')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('v.UNI_CODIGO', 't_clienteunidade.UNI_CODIGO'),
                'veiculos_count',
            )
            ->orderByDesc('UNI_CODIGO')
            ->get();

        return Inertia::render('cadastros/unidade/index', [
            'unidades' => $unidades,
        ]);
    }

    public function store(UnidadeStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $unidade = Unidade::query()->create([
            'UNI_DESCRICAO' => $data['UNI_DESCRICAO'],
            'UNI_STATUS' => $data['UNI_STATUS'] ?? 'A',
            'CLI_CNPJ' => $data['CLI_CNPJ'] ?? '',
            'CLI_UF' => $data['CLI_UF'] ?? '',
            'CLI_CIDADE' => $data['CLI_CIDADE'] ?? '',
        ]);

        return response()->json([
            'message' => 'Unidade cadastrada com sucesso.',
            'unidade' => $unidade,
        ], 201);
    }

    public function update(UnidadeUpdateRequest $request, int $id): JsonResponse
    {
        $unidade = Unidade::query()->findOrFail($id);
        $data = $request->validated();

        if ($data['UNI_STATUS'] === 'I') {
            $blockingResponse = $this->checkBlockingLinks($id);
            if ($blockingResponse !== null) {
                return $blockingResponse;
            }
        }

        $unidade->update([
            'UNI_DESCRICAO' => $data['UNI_DESCRICAO'],
            'UNI_STATUS' => $data['UNI_STATUS'],
            'CLI_CNPJ' => $data['CLI_CNPJ'] ?? '',
            'CLI_UF' => $data['CLI_UF'] ?? '',
            'CLI_CIDADE' => $data['CLI_CIDADE'] ?? '',
        ]);

        return response()->json([
            'message' => 'Unidade atualizada com sucesso.',
            'unidade' => $unidade->fresh(),
        ]);
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        Gate::authorize('admin');

        $unidade = Unidade::query()->findOrFail($id);
        $nextStatus = $unidade->UNI_STATUS === 'A' ? 'I' : 'A';

        if ($nextStatus === 'I') {
            $blockingResponse = $this->checkBlockingLinks($id);
            if ($blockingResponse !== null) {
                return $blockingResponse;
            }
        }

        $unidade->UNI_STATUS = $nextStatus;
        $unidade->save();

        return response()->json([
            'message' => $nextStatus === 'A'
                ? 'Unidade ativada com sucesso.'
                : 'Unidade inativada com sucesso.',
            'status' => $nextStatus,
        ]);
    }

    private function checkBlockingLinks(int $unidadeId): ?JsonResponse
    {
        $pneusCount = DB::table('t_pneu')->where('UNI_CODIGO', $unidadeId)->count();
        $veiculosCount = DB::table('t_veiculo')->where('UNI_CODIGO', $unidadeId)->count();

        if ($pneusCount > 0 || $veiculosCount > 0) {
            return response()->json([
                'message' => sprintf(
                    'Nao e possivel inativar: existe vinculo com pneus (%d) e/ou veiculos (%d).',
                    $pneusCount,
                    $veiculosCount,
                ),
            ], 409);
        }

        return null;
    }
}
