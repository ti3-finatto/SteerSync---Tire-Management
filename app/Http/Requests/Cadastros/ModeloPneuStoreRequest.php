<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ModeloPneuStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        return [
            'MODP_DESCRICAO' => ['required', 'string', 'max:30'],
            'MARP_CODIGO' => ['required', 'integer', 'min:1'],
            'MODP_STATUS' => ['sometimes', 'string', 'in:A,I'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'MODP_DESCRICAO' => mb_strtoupper(trim((string) $this->input('MODP_DESCRICAO', ''))),
            'MARP_CODIGO' => (int) $this->input('MARP_CODIGO', 0),
            'MODP_STATUS' => strtoupper((string) $this->input('MODP_STATUS', 'A')),
        ]);
    }
}
