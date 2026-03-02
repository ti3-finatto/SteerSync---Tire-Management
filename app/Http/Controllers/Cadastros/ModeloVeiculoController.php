<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastros\ModeloVeiculoStoreRequest;
use App\Http\Requests\Cadastros\ModeloVeiculoUpdateRequest;
use App\Models\Legacy\MarcaVeiculo;
use App\Models\Legacy\ModeloVeiculo;
use App\Models\Legacy\TipoVeiculo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class ModeloVeiculoController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('admin');

        return Inertia::render('cadastros/modelo-veiculo/index', [
            'modelos' => ModeloVeiculo::query()
                ->join('t_marcaveiculo', 't_modeloveiculo.MARV_CODIGO', '=', 't_marcaveiculo.MARV_CODIGO')
                ->leftJoin('t_tipoveiculo', 't_modeloveiculo.VEIC_TIPO', '=', 't_tipoveiculo.TPVE_SIGLA')
                ->select([
                    't_modeloveiculo.MODV_CODIGO',
                    't_modeloveiculo.MODV_DESCRICAO',
                    't_modeloveiculo.MODV_STATUS',
                    't_modeloveiculo.MARV_CODIGO',
                    't_modeloveiculo.VEIC_TIPO',
                    't_marcaveiculo.MARV_DESCRICAO as MARCA_DESCRICAO',
                    \Illuminate\Support\Facades\DB::raw(
                        'COALESCE(t_tipoveiculo.TPVE_DESCRICAO, t_modeloveiculo.VEIC_TIPO) as TIPO_DESCRICAO'
                    ),
                ])
                ->orderByDesc('t_modeloveiculo.MODV_CODIGO')
                ->get(),
            'marcas' => MarcaVeiculo::query()
                ->where('MARV_STATUS', 'A')
                ->select(['MARV_CODIGO', 'MARV_DESCRICAO'])
                ->orderBy('MARV_DESCRICAO')
                ->get(),
            'tipos' => TipoVeiculo::query()
                ->where('TPVE_STATUS', 'A')
                ->orderBy('TPVE_ORDEM')
                ->orderBy('TPVE_DESCRICAO')
                ->select(['TPVE_SIGLA', 'TPVE_DESCRICAO'])
                ->get(),
        ]);
    }

    public function store(ModeloVeiculoStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($this->hasDuplicate($data['MARV_CODIGO'], $data['MODV_DESCRICAO'])) {
            return $this->conflict('Ja existe modelo com esta descricao para a marca selecionada.');
        }

        $authUser = $request->user();
        $usuCodigo = $authUser instanceof User
            ? $authUser->ensureLegacyUserCode()
            : 0;

        if ($usuCodigo <= 0) {
            return response()->json(['message' => 'Usuario autenticado sem USU_CODIGO valido.'], 422);
        }

        $modelo = ModeloVeiculo::create([
            'MODV_DESCRICAO' => $data['MODV_DESCRICAO'],
            'MARV_CODIGO'    => $data['MARV_CODIGO'],
            'VEIC_TIPO'      => $data['VEIC_TIPO'],
            'MODV_STATUS'    => $data['MODV_STATUS'] ?? 'A',
            'USU_CODIGO'     => $usuCodigo,
        ]);

        return response()->json([
            'message' => 'Modelo de veiculo cadastrado com sucesso.',
            'modelo'  => $modelo,
        ], 201);
    }

    public function update(ModeloVeiculoUpdateRequest $request, int $id): JsonResponse
    {
        $modelo = ModeloVeiculo::query()->findOrFail($id);
        $data = $request->validated();

        if ($this->hasDuplicate($data['MARV_CODIGO'], $data['MODV_DESCRICAO'], $modelo->MODV_CODIGO)) {
            return $this->conflict('Ja existe modelo com esta descricao para a marca selecionada.');
        }

        $modelo->update([
            'MODV_DESCRICAO' => $data['MODV_DESCRICAO'],
            'MARV_CODIGO'    => $data['MARV_CODIGO'],
            'VEIC_TIPO'      => $data['VEIC_TIPO'],
            'MODV_STATUS'    => $data['MODV_STATUS'],
        ]);

        return response()->json([
            'message' => 'Modelo de veiculo atualizado com sucesso.',
            'modelo'  => $modelo->fresh(),
        ]);
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        Gate::authorize('admin');

        $modelo = ModeloVeiculo::query()->findOrFail($id);
        $modelo->MODV_STATUS = $modelo->MODV_STATUS === 'A' ? 'I' : 'A';
        $modelo->save();

        return response()->json([
            'message' => $modelo->MODV_STATUS === 'A'
                ? 'Modelo ativado com sucesso.'
                : 'Modelo inativado com sucesso.',
            'status' => $modelo->MODV_STATUS,
        ]);
    }

    private function hasDuplicate(int $marcaCodigo, string $descricao, ?int $ignoreId = null): bool
    {
        $query = ModeloVeiculo::query()
            ->where('MARV_CODIGO', $marcaCodigo)
            ->whereRaw('UPPER(MODV_DESCRICAO) = ?', [mb_strtoupper($descricao)]);

        if ($ignoreId !== null) {
            $query->where('MODV_CODIGO', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function conflict(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 409);
    }
}
