<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastros\DesenhoBandaStoreRequest;
use App\Http\Requests\Cadastros\DesenhoBandaUpdateRequest;
use App\Models\Legacy\DesenhoBanda;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class DesenhoBandaController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('admin');

        return Inertia::render('cadastros/desenho-banda/index', [
            'desenhos' => DesenhoBanda::query()
                ->select(['DESB_CODIGO', 'DESB_DESCRICAO', 'DESB_SIGLA', 'DESB_STATUS', 'USU_CODIGO', 'DESB_DATACADASTRO'])
                ->orderByDesc('DESB_CODIGO')
                ->get(),
        ]);
    }

    public function store(DesenhoBandaStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($this->hasDuplicateDescricao($data['DESB_DESCRICAO'])) {
            return $this->conflict('Ja existe desenho de banda com esta descricao.');
        }

        if ($this->hasDuplicateSigla($data['DESB_SIGLA'])) {
            return $this->conflict('Ja existe desenho de banda com esta sigla.');
        }

        $authUser = $request->user();
        $usuCodigo = $authUser instanceof User
            ? $authUser->ensureLegacyUserCode()
            : 0;

        if ($usuCodigo <= 0) {
            return response()->json(['message' => 'Usuario autenticado sem USU_CODIGO valido.'], 422);
        }

        $desenho = DesenhoBanda::create([
            'DESB_DESCRICAO' => $data['DESB_DESCRICAO'],
            'DESB_SIGLA' => $data['DESB_SIGLA'],
            'DESB_STATUS' => $data['DESB_STATUS'] ?? 'A',
            'USU_CODIGO' => $usuCodigo,
            'DESB_DATACADASTRO' => now(),
        ]);

        return response()->json([
            'message' => 'Desenho de banda cadastrado com sucesso.',
            'desenho' => $desenho,
        ], 201);
    }

    public function update(DesenhoBandaUpdateRequest $request, int $id): JsonResponse
    {
        $desenho = DesenhoBanda::query()->findOrFail($id);
        $data = $request->validated();

        if ($this->hasDuplicateDescricao($data['DESB_DESCRICAO'], $desenho->DESB_CODIGO)) {
            return $this->conflict('Ja existe desenho de banda com esta descricao.');
        }

        if ($this->hasDuplicateSigla($data['DESB_SIGLA'], $desenho->DESB_CODIGO)) {
            return $this->conflict('Ja existe desenho de banda com esta sigla.');
        }

        $desenho->update([
            'DESB_DESCRICAO' => $data['DESB_DESCRICAO'],
            'DESB_SIGLA' => $data['DESB_SIGLA'],
            'DESB_STATUS' => $data['DESB_STATUS'],
        ]);

        return response()->json([
            'message' => 'Desenho de banda atualizado com sucesso.',
            'desenho' => $desenho->fresh(),
        ]);
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        Gate::authorize('admin');

        $desenho = DesenhoBanda::query()->findOrFail($id);

        if ($desenho->DESB_STATUS === 'A' && $this->hasLinkedPneus($desenho->DESB_SIGLA)) {
            return $this->conflict('Nao e possivel inativar: existem pneus vinculados a este desenho.');
        }

        $desenho->DESB_STATUS = $desenho->DESB_STATUS === 'A' ? 'I' : 'A';
        $desenho->save();

        return response()->json([
            'message' => $desenho->DESB_STATUS === 'A'
                ? 'Desenho ativado com sucesso.'
                : 'Desenho inativado com sucesso.',
            'status' => $desenho->DESB_STATUS,
        ]);
    }

    private function hasDuplicateDescricao(string $descricao, ?int $ignoreId = null): bool
    {
        $query = DesenhoBanda::query()
            ->whereRaw('UPPER(DESB_DESCRICAO) = ?', [mb_strtoupper($descricao)]);

        if ($ignoreId !== null) {
            $query->where('DESB_CODIGO', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function hasDuplicateSigla(string $sigla, ?int $ignoreId = null): bool
    {
        $query = DesenhoBanda::query()
            ->whereRaw('UPPER(DESB_SIGLA) = ?', [mb_strtoupper($sigla)]);

        if ($ignoreId !== null) {
            $query->where('DESB_CODIGO', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function hasLinkedPneus(string $sigla): bool
    {
        return DB::table('t_tipo')
            ->join('t_pneu', 't_pneu.TIPO_CODIGO', '=', 't_tipo.TIPO_CODIGO')
            ->where('t_tipo.TIPO_DESENHO', $sigla)
            ->where('t_pneu.PNE_STATUS', '!=', 'B')
            ->exists();
    }

    private function conflict(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 409);
    }
}
