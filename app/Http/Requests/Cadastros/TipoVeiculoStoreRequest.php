<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class TipoVeiculoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        return [
            'TPVE_SIGLA'     => ['required', 'string', 'max:5', 'unique:t_tipoveiculo,TPVE_SIGLA'],
            'TPVE_DESCRICAO' => ['required', 'string', 'max:50'],
        ];
    }

    public function messages(): array
    {
        return [
            'TPVE_SIGLA.unique' => 'Ja existe um tipo de veiculo com esta sigla.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'TPVE_SIGLA'     => strtoupper(trim((string) $this->input('TPVE_SIGLA', ''))),
            'TPVE_DESCRICAO' => trim((string) $this->input('TPVE_DESCRICAO', '')),
        ]);
    }
}
