<?php

namespace App\Http\Requests\Cadastros;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class VeiculoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('admin');
    }

    public function rules(): array
    {
        $vehicleId = (int) $this->route('id', 0);

        return [
            'registration_method' => ['required', 'string', 'in:plate,chassis'],
            'VEI_PLACA' => [
                'nullable',
                'string',
                'max:7',
                'required_if:registration_method,plate',
                Rule::unique('t_veiculo', 'VEI_PLACA')->ignore($vehicleId, 'VEI_CODIGO'),
            ],
            'VEI_CHASSI' => [
                'nullable',
                'string',
                'max:17',
                'required_if:registration_method,chassis',
                Rule::unique('t_veiculo', 'VEI_CHASSI')->ignore($vehicleId, 'VEI_CODIGO'),
            ],
            'VEI_FROTA' => ['nullable', 'string', 'max:25'],
            'UNI_CODIGO' => [
                'required',
                'integer',
                Rule::exists('t_clienteunidade', 'UNI_CODIGO')->where('UNI_STATUS', 'A'),
            ],
            'MARV_CODIGO' => [
                'required',
                'integer',
                Rule::exists('t_marcaveiculo', 'MARV_CODIGO')->where('MARV_STATUS', 'A'),
            ],
            'MODV_CODIGO' => [
                'required',
                'integer',
                Rule::exists('t_modeloveiculo', 'MODV_CODIGO')->where('MODV_STATUS', 'A'),
            ],
            'VEIC_CODIGO' => [
                'required',
                'integer',
                Rule::exists('t_veiculoconfiguracao', 'VEIC_CODIGO')->where('VEIC_STATUS', 'A'),
            ],
            'CAL_RECOMENDADA' => ['nullable', 'integer', 'min:0', 'max:999'],
            'VEI_ODOMETRO' => ['required', 'string', 'in:S,N'],
            'VEI_KM' => ['nullable', 'integer', 'min:0'],
            'VEI_OBS' => ['nullable', 'string'],
            'VEI_STATUS' => ['sometimes', 'string', 'in:A,I'],
        ];
    }

    public function messages(): array
    {
        return [
            'VEI_PLACA.required_if' => 'Placa e obrigatoria quando o cadastro for por placa.',
            'VEI_CHASSI.required_if' => 'Chassi e obrigatorio quando o cadastro for por chassi.',
            'VEI_PLACA.unique' => 'Ja existe veiculo com esta placa.',
            'VEI_CHASSI.unique' => 'Ja existe veiculo com este chassi.',
            'UNI_CODIGO.exists' => 'Unidade invalida ou inativa.',
            'MARV_CODIGO.exists' => 'Marca invalida ou inativa.',
            'MODV_CODIGO.exists' => 'Modelo invalido ou inativo.',
            'VEIC_CODIGO.exists' => 'Configuracao invalida ou inativa.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $plate = trim((string) $this->input('VEI_PLACA', ''));
        $chassis = trim((string) $this->input('VEI_CHASSI', ''));

        $this->merge([
            'registration_method' => strtolower(trim((string) $this->input('registration_method', 'plate'))),
            'VEI_PLACA' => $plate === '' ? null : mb_strtoupper($plate),
            'VEI_CHASSI' => $chassis === '' ? null : mb_strtoupper($chassis),
            'VEI_FROTA' => mb_strtoupper(trim((string) $this->input('VEI_FROTA', ''))),
            'UNI_CODIGO' => (int) $this->input('UNI_CODIGO', 0),
            'MARV_CODIGO' => (int) $this->input('MARV_CODIGO', 0),
            'MODV_CODIGO' => (int) $this->input('MODV_CODIGO', 0),
            'VEIC_CODIGO' => (int) $this->input('VEIC_CODIGO', 0),
            'CAL_RECOMENDADA' => $this->filled('CAL_RECOMENDADA') ? (int) $this->input('CAL_RECOMENDADA') : null,
            'VEI_ODOMETRO' => strtoupper(trim((string) $this->input('VEI_ODOMETRO', 'S'))),
            'VEI_KM' => $this->filled('VEI_KM') ? (int) $this->input('VEI_KM') : 0,
            'VEI_OBS' => trim((string) $this->input('VEI_OBS', '')),
            'VEI_STATUS' => strtoupper((string) $this->input('VEI_STATUS', 'A')),
        ]);
    }
}
