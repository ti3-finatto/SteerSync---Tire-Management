<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class MarcaPneuStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        return [
            'MARP_DESCRICAO' => ['required', 'string', 'max:30'],
            'MARP_TIPO' => ['required', 'string', 'in:P,R'],
            'MARP_STATUS' => ['sometimes', 'string', 'in:A,I'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'MARP_DESCRICAO' => mb_strtoupper(trim((string) $this->input('MARP_DESCRICAO', ''))),
            'MARP_TIPO' => strtoupper(trim((string) $this->input('MARP_TIPO', ''))),
            'MARP_STATUS' => strtoupper((string) $this->input('MARP_STATUS', 'A')),
        ]);
    }
}
