<?php

namespace App\Http\Requests\Cadastros;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UsuarioStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique(User::class, 'email')],
            'username' => ['required', 'string', 'max:40', Rule::unique(User::class, 'username')],
            'cpf' => ['nullable', 'digits:11'],
            'phone' => ['nullable', 'string', 'max:30'],
            'USU_TIPO' => ['required', 'string', 'in:A,N'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $cpf = preg_replace('/\D+/', '', (string) $this->input('cpf', ''));
        $phone = preg_replace('/\D+/', '', (string) $this->input('phone', ''));

        $this->merge([
            'name' => trim((string) $this->input('name', '')),
            'email' => trim(strtolower((string) $this->input('email', ''))),
            'username' => trim((string) $this->input('username', '')),
            'cpf' => $cpf !== '' ? $cpf : null,
            'phone' => $phone !== '' ? $phone : null,
            'USU_TIPO' => strtoupper(trim((string) $this->input('USU_TIPO', 'N'))),
        ]);
    }
}
