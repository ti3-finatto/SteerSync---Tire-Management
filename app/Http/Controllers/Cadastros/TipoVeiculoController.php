<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cadastros\TipoVeiculoStoreRequest;
use App\Http\Requests\Cadastros\TipoVeiculoUpdateRequest;
use App\Models\Legacy\TipoVeiculo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class TipoVeiculoController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('admin');

        return Inertia::render('cadastros/tipo-veiculo/index', [
            'tipos' => TipoVeiculo::orderBy('TPVE_ORDEM')->orderBy('TPVE_DESCRICAO')->get(),
        ]);
    }

    public function store(TipoVeiculoStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        $ordem = TipoVeiculo::where('TPVE_PADRAO', 0)->max('TPVE_ORDEM') ?? 98;

        $tipo = TipoVeiculo::create([
            'TPVE_SIGLA'     => $data['TPVE_SIGLA'],
            'TPVE_DESCRICAO' => $data['TPVE_DESCRICAO'],
            'TPVE_STATUS'    => 'A',
            'TPVE_PADRAO'    => 0,
            'TPVE_ORDEM'     => (int) $ordem + 1,
        ]);

        return response()->json([
            'message' => 'Tipo de veiculo cadastrado com sucesso.',
            'tipo'    => $tipo,
        ], 201);
    }

    public function update(TipoVeiculoUpdateRequest $request, string $sigla): JsonResponse
    {
        $tipo = TipoVeiculo::findOrFail($sigla);

        if ($tipo->TPVE_PADRAO) {
            return response()->json([
                'message' => 'Tipos padrao nao podem ser editados.',
            ], 409);
        }

        $tipo->update($request->validated());

        return response()->json([
            'message' => 'Tipo de veiculo atualizado com sucesso.',
            'tipo'    => $tipo->fresh(),
        ]);
    }

    public function toggleStatus(Request $request, string $sigla): JsonResponse
    {
        Gate::authorize('admin');

        $tipo = TipoVeiculo::findOrFail($sigla);

        if ($tipo->TPVE_PADRAO) {
            return response()->json([
                'message' => 'Tipos padrao nao podem ser inativados.',
            ], 409);
        }

        $tipo->TPVE_STATUS = $tipo->TPVE_STATUS === 'A' ? 'I' : 'A';
        $tipo->save();

        return response()->json([
            'message' => $tipo->TPVE_STATUS === 'A'
                ? 'Tipo ativado com sucesso.'
                : 'Tipo inativado com sucesso.',
            'status'  => $tipo->TPVE_STATUS,
        ]);
    }
}
