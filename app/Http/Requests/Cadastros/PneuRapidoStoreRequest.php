<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Validator;

class PneuRapidoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        return [
            'UNI_CODIGO' => ['required', 'integer', 'min:1'],
            'PNE_FOGO' => ['required', 'string', 'alpha_num', 'max:20'],
            'MARP_CODIGO' => ['nullable', 'integer', 'min:1'],
            'TIPO_CODIGO' => ['required', 'integer', 'min:1'],
            'PNE_STATUSCOMPRA' => ['required', 'string', 'in:N,U'],
            'PNE_VIDA' => ['required', 'string', 'in:N,R1,R2,R3,R4,R5'],
            'MARP_CODIGO_RECAPE' => ['nullable', 'integer', 'min:1'],
            'TIPO_CODIGORECAPE' => ['nullable', 'integer', 'min:1'],
            'PNE_VALORRECAPAGEM' => ['nullable', 'numeric', 'min:0'],
            'PNE_DOT' => ['nullable', 'string', 'regex:/^[0-9]{4}$/'],
            'PNE_VALORCOMPRA' => ['required', 'numeric', 'min:0'],
            'PNE_MM' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'PNE_KM' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'UNI_CODIGO' => (int) $this->input('UNI_CODIGO', 0),
            'PNE_FOGO' => strtoupper(trim((string) $this->input('PNE_FOGO', ''))),
            'MARP_CODIGO' => $this->toNullableInt($this->input('MARP_CODIGO')),
            'TIPO_CODIGO' => (int) $this->input('TIPO_CODIGO', 0),
            'PNE_STATUSCOMPRA' => strtoupper(trim((string) $this->input('PNE_STATUSCOMPRA', ''))),
            'PNE_VIDA' => strtoupper(trim((string) $this->input('PNE_VIDA', ''))),
            'MARP_CODIGO_RECAPE' => $this->toNullableInt($this->input('MARP_CODIGO_RECAPE')),
            'TIPO_CODIGORECAPE' => $this->toNullableInt($this->input('TIPO_CODIGORECAPE')),
            'PNE_VALORRECAPAGEM' => $this->toNullableFloat($this->input('PNE_VALORRECAPAGEM')),
            'PNE_DOT' => $this->normalizeDot($this->input('PNE_DOT')),
            'PNE_VALORCOMPRA' => (float) $this->input('PNE_VALORCOMPRA', 0),
            'PNE_MM' => $this->toNullableFloat($this->input('PNE_MM')),
            'PNE_KM' => $this->toNullableInt($this->input('PNE_KM')),
        ]);
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $vida = (string) $this->input('PNE_VIDA', 'N');
            $isRecapado = str_starts_with($vida, 'R');
            if (! $isRecapado) {
                return;
            }

            if ((int) ($this->input('MARP_CODIGO_RECAPE') ?? 0) <= 0) {
                $validator->errors()->add('MARP_CODIGO_RECAPE', 'Marca da recapagem e obrigatoria para vida recapada.');
            }

            if ((int) ($this->input('TIPO_CODIGORECAPE') ?? 0) <= 0) {
                $validator->errors()->add('TIPO_CODIGORECAPE', 'SKU da recapagem e obrigatorio para vida recapada.');
            }

            if (($this->input('PNE_VALORRECAPAGEM') ?? null) === null) {
                $validator->errors()->add('PNE_VALORRECAPAGEM', 'Valor da recapagem e obrigatorio para vida recapada.');
            }
        });
    }

    private function toNullableInt(mixed $value): ?int
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && trim($value) === '') {
            return null;
        }

        return (int) $value;
    }

    private function toNullableFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value) && trim($value) === '') {
            return null;
        }

        return (float) $value;
    }

    private function normalizeDot(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $dot = preg_replace('/\D/', '', (string) $value);

        return $dot === '' ? null : $dot;
    }
}
