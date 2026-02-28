<?php

namespace App\Actions\Fortify;

use App\Enums\UserStatus;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * Resumo da refatoracao: cadastro Fortify estendido para persistir dados
     * equivalentes ao legado diretamente em users (cpf/status/username/phone/foto),
     * sem dependencia da tabela legada de usuarios no runtime.
     *
     * @param  array<string, mixed>  $input
     */
    public function create(array $input): User
    {
        $input['cpf'] = preg_replace('/\D+/', '', $input['cpf'] ?? '');

        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'cpf' => ['required', 'string', 'regex:/^\d{11}$/', Rule::unique(User::class, 'cpf')],
            'phone' => ['nullable', 'string', 'max:30'],
            'username' => ['nullable', 'string', 'max:40', Rule::unique(User::class, 'username')],
            'profile_photo' => ['nullable', 'string', 'max:255'],
            'profile_photo_path' => ['nullable', 'string', 'max:255'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'cpf' => $input['cpf'],
            'status' => UserStatus::ATIVO,
            'phone' => $input['phone'] ?? null,
            'username' => $input['username'] ?? null,
            'profile_photo_path' => $input['profile_photo_path'] ?? $input['profile_photo'] ?? null,
        ]);

        $user->ensureLegacyUserCode();

        return $user;
    }
}
