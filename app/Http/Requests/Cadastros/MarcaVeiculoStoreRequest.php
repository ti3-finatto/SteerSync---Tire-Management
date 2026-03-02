<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class MarcaVeiculoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        return [
            'MARV_DESCRICAO' => ['required', 'string', 'max:30'],
            'MARV_STATUS' => ['sometimes', 'string', 'in:A,I'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'MARV_DESCRICAO' => mb_strtoupper(trim((string) $this->input('MARV_DESCRICAO', ''))),
            'MARV_STATUS' => strtoupper((string) $this->input('MARV_STATUS', 'A')),
        ]);
    }
}
