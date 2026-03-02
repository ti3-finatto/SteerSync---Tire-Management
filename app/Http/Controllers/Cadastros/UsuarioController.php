<?php

namespace App\Http\Controllers\Cadastros;

use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastros\UsuarioStoreRequest;
use App\Http\Requests\Cadastros\UsuarioUpdateRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class UsuarioController extends Controller
{
    private const ACL_PAGES = ['pneus', 'veiculos', 'fornecedor', 'calibragem', 'relatorios', 'espneus', 'movveiculos'];

    public function index(): Response
    {
        Gate::authorize('admin');

        return Inertia::render('cadastros/usuario/index', [
            'usuarios' => User::query()
                ->select(['id', 'name', 'email', 'username', 'cpf', 'phone', 'USU_TIPO', 'status'])
                ->orderByDesc('id')
                ->get()
                ->map(fn($u) => [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'username' => $u->username,
                    'cpf' => $u->cpf ?: null,
                    'phone' => $u->phone,
                    'USU_TIPO' => $u->USU_TIPO,
                    'status' => $u->status instanceof UserStatus ? $u->status->value : (string) $u->status,
                ]),
        ]);
    }

    public function store(UsuarioStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (($data['cpf'] ?? null) && $this->hasDuplicateCpf($data['cpf'])) {
            return $this->conflict('Ja existe usuario com este CPF.');
        }

        $adminCode = $request->user()->ensureLegacyUserCode();

        $usuario = DB::transaction(function () use ($data, $adminCode) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'username' => $data['username'],
                'cpf' => $data['cpf'] ?? '',
                'phone' => $data['phone'] ?? null,
                'USU_TIPO' => $data['USU_TIPO'],
                'status' => UserStatus::ATIVO,
                'password' => Hash::make('12345'),
            ]);

            $user->forceFill(['USU_CODIGO' => $user->id])->saveQuietly();

            foreach (self::ACL_PAGES as $page) {
                DB::table('t_acessousuario')->insert([
                    'ACE_PAGINA' => $page,
                    'ACE_VISUALIZA' => 0,
                    'ACE_EDITA' => 0,
                    'ACE_EXCLUI' => 0,
                    'USU_CODIGOACESSO' => $user->id,
                    'USU_CODIGOCADASTRO' => $adminCode,
                ]);
            }

            return $user;
        });

        return response()->json([
            'message' => 'Usuario cadastrado com sucesso. Senha inicial: 12345',
            'usuario' => $this->toArray($usuario),
        ], 201);
    }

    public function update(UsuarioUpdateRequest $request, int $id): JsonResponse
    {
        $user = User::query()->findOrFail($id);
        $data = $request->validated();

        if (($data['cpf'] ?? null) && $this->hasDuplicateCpf($data['cpf'], $user->id)) {
            return $this->conflict('Ja existe usuario com este CPF.');
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'cpf' => $data['cpf'] ?? '',
            'phone' => $data['phone'] ?? null,
            'USU_TIPO' => $data['USU_TIPO'],
            'status' => $data['status'],
        ]);

        return response()->json([
            'message' => 'Usuario atualizado com sucesso.',
            'usuario' => $this->toArray($user->fresh()),
        ]);
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        Gate::authorize('admin');

        $user = User::query()->findOrFail($id);

        if ($user->id === $request->user()->id) {
            return $this->conflict('Nao e possivel alterar o status do seu proprio usuario.');
        }

        $user->status = $user->status === UserStatus::ATIVO ? UserStatus::INATIVO : UserStatus::ATIVO;
        $user->save();

        $statusValue = $user->status instanceof UserStatus ? $user->status->value : (string) $user->status;

        return response()->json([
            'message' => $user->status === UserStatus::ATIVO
                ? 'Usuario ativado com sucesso.'
                : 'Usuario inativado com sucesso.',
            'status' => $statusValue,
        ]);
    }

    public function resetPassword(Request $request, int $id): JsonResponse
    {
        Gate::authorize('admin');

        $user = User::query()->findOrFail($id);
        $user->password = Hash::make('12345');
        $user->save();

        return response()->json([
            'message' => 'Senha redefinida para o padrao (12345) com sucesso.',
        ]);
    }

    private function hasDuplicateCpf(string $cpf, ?int $ignoreId = null): bool
    {
        $query = User::query()->where('cpf', $cpf)->where('cpf', '!=', '');

        if ($ignoreId !== null) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    private function toArray(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'username' => $user->username,
            'cpf' => $user->cpf ?: null,
            'phone' => $user->phone,
            'USU_TIPO' => $user->USU_TIPO,
            'status' => $user->status instanceof UserStatus ? $user->status->value : (string) $user->status,
        ];
    }

    private function conflict(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 409);
    }
}
