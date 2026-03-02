<?php

namespace App\Http\Requests\Cadastros;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UsuarioUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        $id = (int) $this->route('id');

        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique(User::class, 'email')->ignore($id)],
            'username' => ['required', 'string', 'max:40', Rule::unique(User::class, 'username')->ignore($id)],
            'cpf' => ['nullable', 'digits:11'],
            'phone' => ['nullable', 'string', 'max:30'],
            'USU_TIPO' => ['required', 'string', 'in:A,N'],
            'status' => ['required', 'string', 'in:ATIVO,INATIVO'],
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
            'status' => strtoupper(trim((string) $this->input('status', 'ATIVO'))),
        ]);
    }
}
