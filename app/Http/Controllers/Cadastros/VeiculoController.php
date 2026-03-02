<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastros\VeiculoRequest;
use App\Models\Legacy\MarcaVeiculo;
use App\Models\Legacy\ModeloVeiculo;
use App\Models\Legacy\PosicaoConfiguracao;
use App\Models\Legacy\TipoVeiculo;
use App\Models\Legacy\Unidade;
use App\Models\Legacy\Veiculo;
use App\Models\Legacy\VeiculoConfiguracao;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class VeiculoController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('admin');

        return Inertia::render('cadastros/veiculo/index', [
            'veiculos' => Veiculo::query()
                ->join('t_clienteunidade', 't_veiculo.UNI_CODIGO', '=', 't_clienteunidade.UNI_CODIGO')
                ->join('t_modeloveiculo', 't_veiculo.MODV_CODIGO', '=', 't_modeloveiculo.MODV_CODIGO')
                ->join('t_marcaveiculo', 't_modeloveiculo.MARV_CODIGO', '=', 't_marcaveiculo.MARV_CODIGO')
                ->join('t_veiculoconfiguracao', 't_veiculo.VEIC_CODIGO', '=', 't_veiculoconfiguracao.VEIC_CODIGO')
                ->select([
                    't_veiculo.VEI_CODIGO',
                    't_veiculo.VEI_PLACA',
                    't_veiculo.VEI_CHASSI',
                    't_veiculo.VEI_FROTA',
                    't_veiculo.VEI_STATUS',
                    't_veiculo.CAL_RECOMENDADA',
                    't_veiculo.MODV_CODIGO',
                    't_veiculo.UNI_CODIGO',
                    't_veiculo.VEIC_CODIGO',
                    't_veiculo.VEI_KM',
                    't_veiculo.VEI_OBS',
                    't_veiculo.VEI_ODOMETRO',
                    't_modeloveiculo.MARV_CODIGO',
                    't_modeloveiculo.MODV_DESCRICAO as MODELO_DESCRICAO',
                    't_modeloveiculo.VEIC_TIPO',
                    't_marcaveiculo.MARV_DESCRICAO as MARCA_DESCRICAO',
                    't_clienteunidade.UNI_DESCRICAO',
                    't_veiculoconfiguracao.VEIC_DESCRICAO as CONFIGURACAO_DESCRICAO',
                ])
                ->orderByDesc('t_veiculo.VEI_CODIGO')
                ->get(),
            'unidades' => Unidade::query()
                ->where('UNI_STATUS', 'A')
                ->select(['UNI_CODIGO', 'UNI_DESCRICAO'])
                ->orderBy('UNI_DESCRICAO')
                ->get(),
            'marcas' => MarcaVeiculo::query()
                ->where('MARV_STATUS', 'A')
                ->select(['MARV_CODIGO', 'MARV_DESCRICAO'])
                ->orderBy('MARV_DESCRICAO')
                ->get(),
            'tipos' => TipoVeiculo::query()
                ->where('TPVE_STATUS', 'A')
                ->select(['TPVE_SIGLA', 'TPVE_DESCRICAO'])
                ->orderBy('TPVE_ORDEM')
                ->orderBy('TPVE_DESCRICAO')
                ->get(),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function store(VeiculoRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (! $this->modeloPertenceMarca($data['MODV_CODIGO'], $data['MARV_CODIGO'])) {
            return $this->validationError('Modelo selecionado nao pertence a marca informada.');
        }

        if (! $this->configuracaoCompativelComModelo($data['VEIC_CODIGO'], $data['MODV_CODIGO'])) {
            return $this->validationError('Configuracao selecionada nao e compativel com o tipo do modelo.');
        }

        $authUser = $request->user();
        $usuCodigo = $authUser instanceof User ? $authUser->ensureLegacyUserCode() : 0;
        $userId = $authUser instanceof User ? (int) $authUser->getAuthIdentifier() : null;

        if ($usuCodigo <= 0) {
            return response()->json(['message' => 'Usuario autenticado sem USU_CODIGO valido.'], 422);
        }

        $veiculo = Veiculo::create([
            'VEI_PLACA' => $data['VEI_PLACA'] ?? '',
            'VEI_CHASSI' => $data['VEI_CHASSI'],
            'VEI_FROTA' => $data['VEI_FROTA'] !== '' ? $data['VEI_FROTA'] : null,
            'VEI_STATUS' => $data['VEI_STATUS'] ?? 'A',
            'CAL_RECOMENDADA' => $data['CAL_RECOMENDADA'],
            'MODV_CODIGO' => $data['MODV_CODIGO'],
            'UNI_CODIGO' => $data['UNI_CODIGO'],
            'VEIC_CODIGO' => $data['VEIC_CODIGO'],
            'VEI_KM' => $data['VEI_KM'] ?? 0,
            'USU_CODIGO' => $usuCodigo,
            'user_id' => $userId,
            'VEI_DATACADASTRO' => now(),
            'VEI_OBS' => $data['VEI_OBS'] !== '' ? $data['VEI_OBS'] : null,
            'VEI_ODOMETRO' => $data['VEI_ODOMETRO'] ?? 'S',
        ]);

        return response()->json([
            'message' => 'Veiculo cadastrado com sucesso.',
            'veiculo' => $veiculo,
        ], 201);
    }

    public function update(VeiculoRequest $request, int $id): JsonResponse
    {
        $veiculo = Veiculo::query()->findOrFail($id);
        $data = $request->validated();

        if (! $this->modeloPertenceMarca($data['MODV_CODIGO'], $data['MARV_CODIGO'])) {
            return $this->validationError('Modelo selecionado nao pertence a marca informada.');
        }

        if (! $this->configuracaoCompativelComModelo($data['VEIC_CODIGO'], $data['MODV_CODIGO'])) {
            return $this->validationError('Configuracao selecionada nao e compativel com o tipo do modelo.');
        }

        $veiculo->update([
            'VEI_PLACA' => $data['VEI_PLACA'] ?? '',
            'VEI_CHASSI' => $data['VEI_CHASSI'],
            'VEI_FROTA' => $data['VEI_FROTA'] !== '' ? $data['VEI_FROTA'] : null,
            'VEI_STATUS' => $data['VEI_STATUS'] ?? 'A',
            'CAL_RECOMENDADA' => $data['CAL_RECOMENDADA'],
            'MODV_CODIGO' => $data['MODV_CODIGO'],
            'UNI_CODIGO' => $data['UNI_CODIGO'],
            'VEIC_CODIGO' => $data['VEIC_CODIGO'],
            'VEI_KM' => $data['VEI_KM'] ?? 0,
            'VEI_OBS' => $data['VEI_OBS'] !== '' ? $data['VEI_OBS'] : null,
            'VEI_ODOMETRO' => $data['VEI_ODOMETRO'] ?? 'S',
        ]);

        return response()->json([
            'message' => 'Veiculo atualizado com sucesso.',
            'veiculo' => $veiculo->fresh(),
        ]);
    }

    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        Gate::authorize('admin');

        $veiculo = Veiculo::query()->findOrFail($id);
        $veiculo->VEI_STATUS = $veiculo->VEI_STATUS === 'A' ? 'I' : 'A';
        $veiculo->save();

        return response()->json([
            'message' => $veiculo->VEI_STATUS === 'A'
                ? 'Veiculo ativado com sucesso.'
                : 'Veiculo inativado com sucesso.',
            'status' => $veiculo->VEI_STATUS,
        ]);
    }

    public function getModels(Request $request): JsonResponse
    {
        Gate::authorize('admin');

        $brandId = (int) $request->query('brand_id', 0);

        if ($brandId <= 0) {
            return response()->json([]);
        }

        $models = ModeloVeiculo::query()
            ->where('MARV_CODIGO', $brandId)
            ->where('MODV_STATUS', 'A')
            ->orderBy('MODV_DESCRICAO')
            ->get(['MODV_CODIGO', 'MODV_DESCRICAO', 'VEIC_TIPO'])
            ->map(fn (ModeloVeiculo $model) => [
                'id' => (int) $model->MODV_CODIGO,
                'name' => $model->MODV_DESCRICAO,
                'type' => $model->VEIC_TIPO,
            ])
            ->values();

        return response()->json($models);
    }

    public function getConfigurations(Request $request): JsonResponse
    {
        Gate::authorize('admin');

        $type = strtoupper(trim((string) $request->query('type', '')));

        if (! in_array($type, ['CV', 'CR'], true)) {
            return response()->json([]);
        }

        $configurations = VeiculoConfiguracao::query()
            ->where('VEIC_TIPO', $type)
            ->where('VEIC_STATUS', 'A')
            ->orderBy('VEIC_DESCRICAO')
            ->get(['VEIC_CODIGO', 'VEIC_DESCRICAO', 'VEIC_TIPO'])
            ->map(fn (VeiculoConfiguracao $configuration) => [
                'id' => (int) $configuration->VEIC_CODIGO,
                'name' => $configuration->VEIC_DESCRICAO,
                'description' => sprintf('%s (%s)', $configuration->VEIC_DESCRICAO, $configuration->VEIC_TIPO),
            ])
            ->values();

        return response()->json($configurations);
    }

    public function getConfigurationPositions(Request $request): JsonResponse
    {
        Gate::authorize('admin');

        $configurationId = (int) $request->query('configuration_id', 0);

        if ($configurationId <= 0) {
            return response()->json([
                'axles' => [],
                'spares' => [],
            ]);
        }

        $positions = PosicaoConfiguracao::query()
            ->join('t_posicao', 't_posicaoxconfiguracao.POS_CODIGO', '=', 't_posicao.POS_CODIGO')
            ->where('t_posicaoxconfiguracao.VEIC_CODIGO', $configurationId)
            ->select([
                't_posicaoxconfiguracao.POS_CODIGO',
                't_posicaoxconfiguracao.PSCF_PAR',
                't_posicaoxconfiguracao.PSCF_EIXO',
                't_posicao.POS_DESCRICAO',
            ])
            ->orderBy('t_posicaoxconfiguracao.PSCF_EIXO')
            ->orderBy('t_posicaoxconfiguracao.POS_CODIGO')
            ->get();

        /** @var Collection<int, array<int, array<string, mixed>>> $groupedAxles */
        $groupedAxles = $positions
            ->whereNotNull('PSCF_EIXO')
            ->groupBy('PSCF_EIXO')
            ->sortKeys()
            ->map(function (Collection $rows, $axleNumber) {
                return [
                    'axle_number' => (int) $axleNumber,
                    'positions' => $rows
                        ->map(function ($row) {
                            $description = mb_strtoupper((string) $row->POS_DESCRICAO);
                            $isDouble = $row->PSCF_PAR !== null
                                && (str_contains($description, 'EXTERNO') || str_contains($description, 'INTERNO'));

                            return [
                                'id' => (int) $row->POS_CODIGO,
                                'code' => (int) $row->POS_CODIGO,
                                'description' => (string) $row->POS_DESCRICAO,
                                'short' => $this->shortPositionLabel((string) $row->POS_DESCRICAO),
                                'side' => $this->positionSide((string) $row->POS_DESCRICAO),
                                'pair_id' => $row->PSCF_PAR !== null ? (int) $row->PSCF_PAR : null,
                                'is_double' => $isDouble,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        $spares = $positions
            ->whereNull('PSCF_EIXO')
            ->values()
            ->map(fn ($row) => [
                'id' => (int) $row->POS_CODIGO,
                'code' => (int) $row->POS_CODIGO,
                'description' => (string) $row->POS_DESCRICAO,
                'short' => $this->shortPositionLabel((string) $row->POS_DESCRICAO),
            ])
            ->values();

        return response()->json([
            'axles' => $groupedAxles,
            'spares' => $spares,
        ]);
    }

    private function modeloPertenceMarca(int $modeloId, int $marcaId): bool
    {
        return ModeloVeiculo::query()
            ->where('MODV_CODIGO', $modeloId)
            ->where('MARV_CODIGO', $marcaId)
            ->exists();
    }

    private function configuracaoCompativelComModelo(int $configuracaoId, int $modeloId): bool
    {
        $tipoModelo = ModeloVeiculo::query()
            ->where('MODV_CODIGO', $modeloId)
            ->value('VEIC_TIPO');

        if (! is_string($tipoModelo) || $tipoModelo === '') {
            return false;
        }

        return VeiculoConfiguracao::query()
            ->where('VEIC_CODIGO', $configuracaoId)
            ->where('VEIC_TIPO', $tipoModelo)
            ->exists();
    }

    private function positionSide(string $description): string
    {
        $normalized = mb_strtoupper($description);

        if (str_contains($normalized, 'DIREITO')) {
            return 'right';
        }

        if (str_contains($normalized, 'ESQUERDO')) {
            return 'left';
        }

        return 'center';
    }

    private function shortPositionLabel(string $description): string
    {
        $normalized = mb_strtoupper(trim($description));

        $map = [
            'DIANTEIRO DIREITO' => 'DD',
            'DIANTEIRO ESQUERDO' => 'DE',
            'TRACAO DIREITO EXTERNO' => 'TDE',
            'TRACAO DIREITO INTERNO' => 'TDI',
            'TRACAO ESQUERDO EXTERNO' => 'TEE',
            'TRACAO ESQUERDO INTERNO' => 'TEI',
            'TRUCK DIREITO EXTERNO' => 'LDE',
            'TRUCK DIREITO INTERNO' => 'LDI',
            'TRUCK ESQUERDO EXTERNO' => 'LEE',
            'TRUCK ESQUERDO INTERNO' => 'LEI',
            'ESTEPE' => 'ESP1',
            'ESTEPE 2' => 'ESP2',
        ];

        foreach ($map as $key => $value) {
            if (str_contains($normalized, $key)) {
                return $value;
            }
        }

        return str_replace(' ', '', mb_substr($normalized, 0, 8));
    }

    private function validationError(string $message): JsonResponse
    {
        return response()->json(['message' => $message], 422);
    }
}
