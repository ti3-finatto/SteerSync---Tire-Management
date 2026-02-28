<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastros\FornecedorStoreRequest;
use App\Models\User;
use App\Http\Requests\Cadastros\FornecedorUpdateRequest;
use App\Models\Legacy\Fornecedor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FornecedorController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('admin');

        return Inertia::render('cadastros/fornecedor/index', [
            'fornecedores' => Fornecedor::query()
                ->select([
                    'FORN_CODIGO',
                    'FORN_RAZAO',
                    'FORN_CNPJ',
                    'FORN_EMAIL',
                    'FORN_TELEFONE',
                    'FORN_STATUS',
                    'USU_CODIGO',
                    'FORN_DATACADASTRO',
                ])
                ->orderByDesc('FORN_CODIGO')
                ->get(),
        ]);
    }

    public function store(FornecedorStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (($data['FORN_CNPJ'] ?? null) && $this->hasDuplicatedCnpj($data['FORN_CNPJ'])) {
            return $this->conflict('Ja existe fornecedor com este CNPJ.');
        }

        $authUser = $request->user();
        $usuCodigo = $authUser instanceof User
            ? $authUser->ensureLegacyUserCode()
            : 0;

        if ($usuCodigo <= 0) {
            return response()->json([
                'message' => 'Usuario autenticado sem USU_CODIGO valido.',
            ], 422);
        }

        $fornecedor = Fornecedor::create([
            'FORN_RAZAO' => $data['FORN_RAZAO'],
            'FORN_CNPJ' => $data['FORN_CNPJ'] ?? '',
            'FORN_EMAIL' => $data['FORN_EMAIL'] ?? null,
            'FORN_TELEFONE' => $data['FORN_TELEFONE'] ?? null,
            'FORN_STATUS' => $data['FORN_STATUS'] ?? 'A',
            'USU_CODIGO' => $usuCodigo,
        ]);

        return response()->json([
            'message' => 'Fornecedor cadastrado com sucesso.',
            'fornecedor' => $fornecedor,
        ], 201);
    }

    public function update(FornecedorUpdateRequest $request, int $id): JsonResponse
    {
        $fornecedor = Fornecedor::query()->findOrFail($id);
        $data = $request->validated();

        if (($data['FORN_CNPJ'] ?? null) && $this->hasDuplicatedCnpj($data['FORN_CNPJ'], $fornecedor->FORN_CODIGO)) {
            return $this->conflict('Ja existe fornecedor com este CNPJ.');
        }

        $fornecedor->update([
            'FORN_RAZAO' => $data['FORN_RAZAO'],
            'FORN_CNPJ' => $data['FORN_CNPJ'] ?? '',
            'FORN_EMAIL' => $data['FORN_EMAIL'] ?? null,
            'FORN_TELEFONE' => $data['FORN_TELEFONE'] ?? null,
            'FORN_STATUS' => $data['FORN_STATUS'],
        ]);

        return response()->json([
            'message' => 'Fornecedor atualizado com sucesso.',
            'fornecedor' => $fornecedor->fresh(),
        ]);
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        Gate::authorize('admin');

        $fornecedor = Fornecedor::query()->findOrFail($id);
        $fornecedor->FORN_STATUS = $fornecedor->FORN_STATUS === 'A' ? 'I' : 'A';
        $fornecedor->save();

        return response()->json([
            'message' => $fornecedor->FORN_STATUS === 'A'
                ? 'Fornecedor ativado com sucesso.'
                : 'Fornecedor inativado com sucesso.',
            'status' => $fornecedor->FORN_STATUS,
        ]);
    }

    private function hasDuplicatedCnpj(string $cnpj, ?int $ignoreId = null): bool
    {
        $query = Fornecedor::query()->where('FORN_CNPJ', $cnpj);

        if ($ignoreId !== null) {
            $query->where('FORN_CODIGO', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function conflict(string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
        ], 409);
    }
}
