<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class TipoPneuStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        return [
            'MARP_CODIGO' => ['required', 'integer', 'min:1'],
            'MODP_CODIGO' => ['required', 'integer', 'min:1'],
            'MEDP_CODIGO' => ['required', 'integer', 'min:1'],
            'TIPO_DESENHO' => ['required', 'string', 'size:1', 'exists:t_desenhobanda,DESB_SIGLA'],
            'TIPO_INSPECAO' => ['required', 'string', 'in:M,T'],
            'TIPO_NSULCO' => ['required', 'integer', 'min:1', 'max:15'],
            'TIPO_MMNOVO' => ['required', 'numeric', 'min:1', 'max:30'],
            'TIPO_MMSEGURANCA' => ['required', 'numeric', 'min:1', 'max:30'],
            'TIPO_MMDESGPAR' => ['nullable', 'numeric', 'min:1', 'max:30'],
            'TIPO_MMDESGEIXOS' => ['nullable', 'numeric', 'min:1', 'max:30'],
            'TIPO_STATUS' => ['sometimes', 'string', 'in:A,I'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'MARP_CODIGO' => (int) $this->input('MARP_CODIGO', 0),
            'MODP_CODIGO' => (int) $this->input('MODP_CODIGO', 0),
            'MEDP_CODIGO' => (int) $this->input('MEDP_CODIGO', 0),
            'TIPO_DESENHO' => strtoupper(trim((string) $this->input('TIPO_DESENHO', ''))),
            'TIPO_INSPECAO' => strtoupper(trim((string) $this->input('TIPO_INSPECAO', ''))),
            'TIPO_NSULCO' => (int) $this->input('TIPO_NSULCO', 0),
            'TIPO_MMNOVO' => (float) $this->input('TIPO_MMNOVO', 0),
            'TIPO_MMSEGURANCA' => (float) $this->input('TIPO_MMSEGURANCA', 0),
            'TIPO_MMDESGPAR' => $this->normalizeNullableFloat($this->input('TIPO_MMDESGPAR')),
            'TIPO_MMDESGEIXOS' => $this->normalizeNullableFloat($this->input('TIPO_MMDESGEIXOS')),
            'TIPO_STATUS' => strtoupper((string) $this->input('TIPO_STATUS', 'A')),
        ]);
    }

    private function normalizeNullableFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && trim($value) === '') {
            return null;
        }

        return (float) $value;
    }
}
