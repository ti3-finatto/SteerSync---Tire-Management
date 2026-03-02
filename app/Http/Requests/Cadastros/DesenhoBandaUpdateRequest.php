<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class DesenhoBandaUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        return [
            'DESB_DESCRICAO' => ['required', 'string', 'max:30'],
            'DESB_SIGLA' => ['required', 'string', 'size:1'],
            'DESB_STATUS' => ['required', 'string', 'in:A,I'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'DESB_DESCRICAO' => mb_strtoupper(trim((string) $this->input('DESB_DESCRICAO', ''))),
            'DESB_SIGLA' => mb_strtoupper(trim((string) $this->input('DESB_SIGLA', ''))),
            'DESB_STATUS' => strtoupper((string) $this->input('DESB_STATUS', 'A')),
        ]);
    }
}
