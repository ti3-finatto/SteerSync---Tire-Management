<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ModeloVeiculoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        return [
            'MODV_DESCRICAO' => ['required', 'string', 'max:30'],
            'MARV_CODIGO'    => ['required', 'integer', 'min:1'],
            'VEIC_TIPO'      => ['required', 'string', Rule::exists('t_tipoveiculo', 'TPVE_SIGLA')->where('TPVE_STATUS', 'A')],
            'MODV_STATUS'    => ['sometimes', 'string', 'in:A,I'],
        ];
    }

    public function messages(): array
    {
        return [
            'VEIC_TIPO.exists' => 'Tipo de veiculo invalido ou inativo.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'MODV_DESCRICAO' => mb_strtoupper(trim((string) $this->input('MODV_DESCRICAO', ''))),
            'MARV_CODIGO'    => (int) $this->input('MARV_CODIGO', 0),
            'VEIC_TIPO'      => strtoupper(trim((string) $this->input('VEIC_TIPO', ''))),
            'MODV_STATUS'    => strtoupper((string) $this->input('MODV_STATUS', 'A')),
        ]);
    }
}
