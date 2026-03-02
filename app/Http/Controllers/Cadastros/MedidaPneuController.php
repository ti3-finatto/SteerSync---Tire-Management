<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastros\MedidaPneuStoreRequest;
use App\Http\Requests\Cadastros\MedidaPneuUpdateRequest;
use App\Models\Legacy\MedidaPneu;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MedidaPneuController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('admin');

        return Inertia::render('cadastros/medida-pneu/index', [
            'medidas' => MedidaPneu::query()
                ->select(['MEDP_CODIGO', 'MEDP_DESCRICAO', 'CAL_RECOMENDADA', 'MEDP_STATUS', 'USU_CODIGO', 'MEDP_DATACADASTRO'])
                ->orderByDesc('MEDP_CODIGO')
                ->get(),
        ]);
    }

    public function store(MedidaPneuStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($this->hasDuplicate($data['MEDP_DESCRICAO'])) {
            return $this->conflict('Ja existe medida com esta descricao.');
        }

        $authUser = $request->user();
        $usuCodigo = $authUser instanceof User
            ? $authUser->ensureLegacyUserCode()
            : 0;

        if ($usuCodigo <= 0) {
            return response()->json(['message' => 'Usuario autenticado sem USU_CODIGO valido.'], 422);
        }

        $medida = MedidaPneu::create([
            'MEDP_DESCRICAO' => $data['MEDP_DESCRICAO'],
            'CAL_RECOMENDADA' => $data['CAL_RECOMENDADA'] ?? null,
            'MEDP_STATUS' => $data['MEDP_STATUS'] ?? 'A',
            'USU_CODIGO' => $usuCodigo,
            'MEDP_DATACADASTRO' => now(),
        ]);

        return response()->json([
            'message' => 'Medida de pneu cadastrada com sucesso.',
            'medida' => $medida,
        ], 201);
    }

    public function update(MedidaPneuUpdateRequest $request, int $id): JsonResponse
    {
        $medida = MedidaPneu::query()->findOrFail($id);
        $data = $request->validated();

        if ($this->hasDuplicate($data['MEDP_DESCRICAO'], $medida->MEDP_CODIGO)) {
            return $this->conflict('Ja existe medida com esta descricao.');
        }

        $medida->update([
            'MEDP_DESCRICAO' => $data['MEDP_DESCRICAO'],
            'CAL_RECOMENDADA' => $data['CAL_RECOMENDADA'] ?? null,
            'MEDP_STATUS' => $data['MEDP_STATUS'],
        ]);

        return response()->json([
            'message' => 'Medida de pneu atualizada com sucesso.',
            'medida' => $medida->fresh(),
        ]);
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        Gate::authorize('admin');

        $medida = MedidaPneu::query()->findOrFail($id);

        if ($medida->MEDP_STATUS === 'A' && $this->hasLinkedPneus($medida->MEDP_CODIGO)) {
            return $this->conflict('Nao e possivel inativar: existem pneus vinculados a esta medida.');
        }

        $medida->MEDP_STATUS = $medida->MEDP_STATUS === 'A' ? 'I' : 'A';
        $medida->save();

        return response()->json([
            'message' => $medida->MEDP_STATUS === 'A'
                ? 'Medida ativada com sucesso.'
                : 'Medida inativada com sucesso.',
            'status' => $medida->MEDP_STATUS,
        ]);
    }

    private function hasDuplicate(string $descricao, ?int $ignoreId = null): bool
    {
        $query = MedidaPneu::query()
            ->whereRaw('UPPER(MEDP_DESCRICAO) = ?', [mb_strtoupper($descricao)]);

        if ($ignoreId !== null) {
            $query->where('MEDP_CODIGO', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function hasLinkedPneus(int $medidaId): bool
    {
        return DB::table('t_tipo')
            ->join('t_pneu', 't_pneu.TIPO_CODIGO', '=', 't_tipo.TIPO_CODIGO')
            ->where('t_tipo.MEDP_CODIGO', $medidaId)
            ->where('t_pneu.PNE_STATUS', '!=', 'B')
            ->exists();
    }

    private function conflict(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 409);
    }
}
