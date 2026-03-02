<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class MedidaPneuStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        return [
            'MEDP_DESCRICAO' => ['required', 'string', 'max:30'],
            'CAL_RECOMENDADA' => ['nullable', 'numeric', 'min:30', 'max:150'],
            'MEDP_STATUS' => ['sometimes', 'string', 'in:A,I'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $calibragem = $this->input('CAL_RECOMENDADA');
        $calibragem = is_string($calibragem) ? trim($calibragem) : $calibragem;

        $this->merge([
            'MEDP_DESCRICAO' => mb_strtoupper(trim((string) $this->input('MEDP_DESCRICAO', ''))),
            'CAL_RECOMENDADA' => $calibragem === '' || $calibragem === null ? null : (float) $calibragem,
            'MEDP_STATUS' => strtoupper((string) $this->input('MEDP_STATUS', 'A')),
        ]);
    }
}
