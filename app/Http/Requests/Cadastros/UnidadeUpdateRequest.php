<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UnidadeUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        return [
            'UNI_DESCRICAO' => ['required', 'string', 'max:40'],
            'CLI_CNPJ' => ['nullable', 'digits:14'],
            'CLI_UF' => ['nullable', 'string', Rule::when($this->filled('CLI_UF'), ['regex:/^[A-Z]{2}$/'])],
            'CLI_CIDADE' => ['nullable', 'string', 'max:60'],
            'UNI_STATUS' => ['required', 'string', 'in:A,I'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $cnpj = preg_replace('/\D+/', '', (string) $this->input('CLI_CNPJ', ''));
        $uf = strtoupper(trim((string) $this->input('CLI_UF', '')));
        $cidade = trim((string) $this->input('CLI_CIDADE', ''));

        $this->merge([
            'UNI_DESCRICAO' => trim((string) $this->input('UNI_DESCRICAO', '')),
            'CLI_CNPJ' => $cnpj !== '' ? $cnpj : null,
            'CLI_UF' => $uf !== '' ? $uf : null,
            'CLI_CIDADE' => $cidade !== '' ? $cidade : null,
            'UNI_STATUS' => strtoupper((string) $this->input('UNI_STATUS', 'A')),
        ]);
    }
}
