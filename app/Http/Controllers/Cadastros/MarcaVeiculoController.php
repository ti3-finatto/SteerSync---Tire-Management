<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastros\MarcaVeiculoStoreRequest;
use App\Http\Requests\Cadastros\MarcaVeiculoUpdateRequest;
use App\Models\Legacy\MarcaVeiculo;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MarcaVeiculoController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('admin');

        return Inertia::render('cadastros/marca-veiculo/index', [
            'marcas' => MarcaVeiculo::query()
                ->select(['MARV_CODIGO', 'MARV_DESCRICAO', 'MARV_STATUS', 'USU_CODIGO', 'MARV_DATACADASTRO'])
                ->orderByDesc('MARV_CODIGO')
                ->get(),
        ]);
    }

    public function store(MarcaVeiculoStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($this->hasDuplicate($data['MARV_DESCRICAO'])) {
            return $this->conflict('Ja existe marca de veiculo com esta descricao.');
        }

        $authUser = $request->user();
        $usuCodigo = $authUser instanceof User
            ? $authUser->ensureLegacyUserCode()
            : 0;

        if ($usuCodigo <= 0) {
            return response()->json(['message' => 'Usuario autenticado sem USU_CODIGO valido.'], 422);
        }

        $marca = MarcaVeiculo::create([
            'MARV_DESCRICAO' => $data['MARV_DESCRICAO'],
            'MARV_STATUS' => $data['MARV_STATUS'] ?? 'A',
            'USU_CODIGO' => $usuCodigo,
        ]);

        return response()->json([
            'message' => 'Marca de veiculo cadastrada com sucesso.',
            'marca' => $marca,
        ], 201);
    }

    public function update(MarcaVeiculoUpdateRequest $request, int $id): JsonResponse
    {
        $marca = MarcaVeiculo::query()->findOrFail($id);
        $data = $request->validated();

        if ($this->hasDuplicate($data['MARV_DESCRICAO'], $marca->MARV_CODIGO)) {
            return $this->conflict('Ja existe marca de veiculo com esta descricao.');
        }

        $marca->update([
            'MARV_DESCRICAO' => $data['MARV_DESCRICAO'],
            'MARV_STATUS' => $data['MARV_STATUS'],
        ]);

        return response()->json([
            'message' => 'Marca de veiculo atualizada com sucesso.',
            'marca' => $marca->fresh(),
        ]);
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        Gate::authorize('admin');

        $marca = MarcaVeiculo::query()->findOrFail($id);
        $marca->MARV_STATUS = $marca->MARV_STATUS === 'A' ? 'I' : 'A';
        $marca->save();

        return response()->json([
            'message' => $marca->MARV_STATUS === 'A'
                ? 'Marca ativada com sucesso.'
                : 'Marca inativada com sucesso.',
            'status' => $marca->MARV_STATUS,
        ]);
    }

    private function hasDuplicate(string $descricao, ?int $ignoreId = null): bool
    {
        $query = MarcaVeiculo::query()
            ->whereRaw('UPPER(MARV_DESCRICAO) = ?', [mb_strtoupper($descricao)]);

        if ($ignoreId !== null) {
            $query->where('MARV_CODIGO', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function conflict(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 409);
    }
}
