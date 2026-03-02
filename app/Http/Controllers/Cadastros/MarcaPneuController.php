<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastros\MarcaPneuStoreRequest;
use App\Http\Requests\Cadastros\MarcaPneuUpdateRequest;
use App\Models\Legacy\MarcaPneu;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MarcaPneuController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('admin');

        return Inertia::render('cadastros/marca-pneu/index', [
            'marcas' => MarcaPneu::query()
                ->select(['MARP_CODIGO', 'MARP_DESCRICAO', 'MARP_TIPO', 'MARP_STATUS', 'USU_CODIGO', 'MARP_DATACADASTRO'])
                ->orderByDesc('MARP_CODIGO')
                ->get(),
        ]);
    }

    public function store(MarcaPneuStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($this->hasDuplicate($data['MARP_TIPO'], $data['MARP_DESCRICAO'])) {
            return $this->conflict('Ja existe marca com este tipo e descricao.');
        }

        $authUser = $request->user();
        $usuCodigo = $authUser instanceof User
            ? $authUser->ensureLegacyUserCode()
            : 0;

        if ($usuCodigo <= 0) {
            return response()->json(['message' => 'Usuario autenticado sem USU_CODIGO valido.'], 422);
        }

        $marca = MarcaPneu::create([
            'MARP_DESCRICAO' => $data['MARP_DESCRICAO'],
            'MARP_TIPO' => $data['MARP_TIPO'],
            'MARP_STATUS' => $data['MARP_STATUS'] ?? 'A',
            'USU_CODIGO' => $usuCodigo,
            'MARP_DATACADASTRO' => now(),
        ]);

        return response()->json([
            'message' => 'Marca de pneu cadastrada com sucesso.',
            'marca' => $marca,
        ], 201);
    }

    public function update(MarcaPneuUpdateRequest $request, int $id): JsonResponse
    {
        $marca = MarcaPneu::query()->findOrFail($id);
        $data = $request->validated();

        if ($this->hasDuplicate($data['MARP_TIPO'], $data['MARP_DESCRICAO'], $marca->MARP_CODIGO)) {
            return $this->conflict('Ja existe marca com este tipo e descricao.');
        }

        $marca->update([
            'MARP_DESCRICAO' => $data['MARP_DESCRICAO'],
            'MARP_TIPO' => $data['MARP_TIPO'],
            'MARP_STATUS' => $data['MARP_STATUS'],
        ]);

        return response()->json([
            'message' => 'Marca de pneu atualizada com sucesso.',
            'marca' => $marca->fresh(),
        ]);
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        Gate::authorize('admin');

        $marca = MarcaPneu::query()->findOrFail($id);

        if ($marca->MARP_STATUS === 'A' && $this->hasLinkedPneus($marca->MARP_CODIGO)) {
            return $this->conflict('Nao e possivel inativar: existem pneus vinculados a esta marca.');
        }

        $marca->MARP_STATUS = $marca->MARP_STATUS === 'A' ? 'I' : 'A';
        $marca->save();

        return response()->json([
            'message' => $marca->MARP_STATUS === 'A'
                ? 'Marca ativada com sucesso.'
                : 'Marca inativada com sucesso.',
            'status' => $marca->MARP_STATUS,
        ]);
    }

    private function hasDuplicate(string $tipo, string $descricao, ?int $ignoreId = null): bool
    {
        $query = MarcaPneu::query()
            ->where('MARP_TIPO', $tipo)
            ->whereRaw('UPPER(MARP_DESCRICAO) = ?', [mb_strtoupper($descricao)]);

        if ($ignoreId !== null) {
            $query->where('MARP_CODIGO', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function hasLinkedPneus(int $marcaId): bool
    {
        return DB::table('t_tipo')
            ->join('t_pneu', 't_pneu.TIPO_CODIGO', '=', 't_tipo.TIPO_CODIGO')
            ->where('t_tipo.MARP_CODIGO', $marcaId)
            ->where('t_pneu.PNE_STATUS', '!=', 'B')
            ->exists();
    }

    private function conflict(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 409);
    }
}
