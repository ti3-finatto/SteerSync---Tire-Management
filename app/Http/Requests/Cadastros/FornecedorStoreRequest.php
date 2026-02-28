<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class FornecedorStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        return [
            'FORN_RAZAO' => ['required', 'string', 'max:50'],
            'FORN_CNPJ' => ['nullable', 'digits:14'],
            'FORN_EMAIL' => ['nullable', 'email', 'max:35'],
            'FORN_TELEFONE' => ['nullable', 'digits_between:10,11'],
            'FORN_STATUS' => ['sometimes', 'string', 'in:A,I'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $cnpj = preg_replace('/\D+/', '', (string) $this->input('FORN_CNPJ', ''));
        $telefone = preg_replace('/\D+/', '', (string) $this->input('FORN_TELEFONE', ''));

        $this->merge([
            'FORN_RAZAO' => trim((string) $this->input('FORN_RAZAO', '')),
            'FORN_CNPJ' => $cnpj !== '' ? $cnpj : null,
            'FORN_EMAIL' => ($email = trim((string) $this->input('FORN_EMAIL', ''))) !== '' ? $email : null,
            'FORN_TELEFONE' => $telefone !== '' ? $telefone : null,
            'FORN_STATUS' => strtoupper((string) $this->input('FORN_STATUS', 'A')),
        ]);
    }
}
