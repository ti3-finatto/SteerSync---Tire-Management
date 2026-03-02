<?php

use App\Http\Controllers\Cadastros\FornecedorController;
use App\Http\Controllers\Cadastros\TipoVeiculoController;
use App\Http\Controllers\Relatorios\PneuRelatorioController;
use App\Http\Controllers\Cadastros\UsuarioController;
use App\Http\Controllers\Cadastros\DesenhoBandaController;
use App\Http\Controllers\Cadastros\MarcaPneuController;
use App\Http\Controllers\Cadastros\MarcaVeiculoController;
use App\Http\Controllers\Cadastros\MedidaPneuController;
use App\Http\Controllers\Cadastros\ModeloPneuController;
use App\Http\Controllers\Cadastros\ModeloVeiculoController;
use App\Http\Controllers\Cadastros\PneuConsultaController;
use App\Http\Controllers\Cadastros\PneuRapidoController;
use App\Http\Controllers\Cadastros\TipoPneuController;
use App\Http\Controllers\Cadastros\UnidadeController;
use App\Http\Controllers\Cadastros\VeiculoController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified', 'can:admin'])->group(function () {
    Route::get('/cadastros/fornecedor', [FornecedorController::class, 'index'])
        ->name('cadastros.fornecedor.index');
    Route::post('/cadastros/fornecedor', [FornecedorController::class, 'store'])
        ->name('cadastros.fornecedor.store');
    Route::put('/cadastros/fornecedor/{id}', [FornecedorController::class, 'update'])
        ->name('cadastros.fornecedor.update');
    Route::patch('/cadastros/fornecedor/{id}/status', [FornecedorController::class, 'toggleStatus'])
        ->name('cadastros.fornecedor.toggleStatus');

    Route::get('/cadastros/unidade', [UnidadeController::class, 'index'])
        ->name('cadastros.unidade.index');
    Route::post('/cadastros/unidade', [UnidadeController::class, 'store'])
        ->name('cadastros.unidade.store');
    Route::put('/cadastros/unidade/{id}', [UnidadeController::class, 'update'])
        ->name('cadastros.unidade.update');
    Route::patch('/cadastros/unidade/{id}/status', [UnidadeController::class, 'toggleStatus'])
        ->name('cadastros.unidade.toggleStatus');

    Route::get('/cadastros/marca-pneu', [MarcaPneuController::class, 'index'])
        ->name('cadastros.marca-pneu.index');
    Route::post('/cadastros/marca-pneu', [MarcaPneuController::class, 'store'])
        ->name('cadastros.marca-pneu.store');
    Route::put('/cadastros/marca-pneu/{id}', [MarcaPneuController::class, 'update'])
        ->name('cadastros.marca-pneu.update');
    Route::patch('/cadastros/marca-pneu/{id}/status', [MarcaPneuController::class, 'toggleStatus'])
        ->name('cadastros.marca-pneu.toggleStatus');

    Route::get('/cadastros/modelo-pneu', [ModeloPneuController::class, 'index'])
        ->name('cadastros.modelo-pneu.index');
    Route::post('/cadastros/modelo-pneu', [ModeloPneuController::class, 'store'])
        ->name('cadastros.modelo-pneu.store');
    Route::put('/cadastros/modelo-pneu/{id}', [ModeloPneuController::class, 'update'])
        ->name('cadastros.modelo-pneu.update');
    Route::patch('/cadastros/modelo-pneu/{id}/status', [ModeloPneuController::class, 'toggleStatus'])
        ->name('cadastros.modelo-pneu.toggleStatus');

    Route::get('/cadastros/desenho-banda', [DesenhoBandaController::class, 'index'])
        ->name('cadastros.desenho-banda.index');
    Route::post('/cadastros/desenho-banda', [DesenhoBandaController::class, 'store'])
        ->name('cadastros.desenho-banda.store');
    Route::put('/cadastros/desenho-banda/{id}', [DesenhoBandaController::class, 'update'])
        ->name('cadastros.desenho-banda.update');
    Route::patch('/cadastros/desenho-banda/{id}/status', [DesenhoBandaController::class, 'toggleStatus'])
        ->name('cadastros.desenho-banda.toggleStatus');

    Route::get('/cadastros/medida-pneu', [MedidaPneuController::class, 'index'])
        ->name('cadastros.medida-pneu.index');
    Route::post('/cadastros/medida-pneu', [MedidaPneuController::class, 'store'])
        ->name('cadastros.medida-pneu.store');
    Route::put('/cadastros/medida-pneu/{id}', [MedidaPneuController::class, 'update'])
        ->name('cadastros.medida-pneu.update');
    Route::patch('/cadastros/medida-pneu/{id}/status', [MedidaPneuController::class, 'toggleStatus'])
        ->name('cadastros.medida-pneu.toggleStatus');

    Route::get('/cadastros/configuracao-pneu', [TipoPneuController::class, 'index'])
        ->name('cadastros.configuracao-pneu.index');
    Route::post('/cadastros/configuracao-pneu', [TipoPneuController::class, 'store'])
        ->name('cadastros.configuracao-pneu.store');
    Route::put('/cadastros/configuracao-pneu/{id}', [TipoPneuController::class, 'update'])
        ->name('cadastros.configuracao-pneu.update');
    Route::patch('/cadastros/configuracao-pneu/{id}/status', [TipoPneuController::class, 'toggleStatus'])
        ->name('cadastros.configuracao-pneu.toggleStatus');

    Route::get('/cadastros/pneu-rapido', [PneuRapidoController::class, 'index'])
        ->name('cadastros.pneu-rapido.index');
    Route::post('/cadastros/pneu-rapido', [PneuRapidoController::class, 'store'])
        ->name('cadastros.pneu-rapido.store');
    Route::get('/cadastros/pneu-consulta', [PneuConsultaController::class, 'index'])
        ->name('cadastros.pneu-consulta.index');

    Route::get('/cadastros/marca-veiculo', [MarcaVeiculoController::class, 'index'])
        ->name('cadastros.marca-veiculo.index');
    Route::post('/cadastros/marca-veiculo', [MarcaVeiculoController::class, 'store'])
        ->name('cadastros.marca-veiculo.store');
    Route::put('/cadastros/marca-veiculo/{id}', [MarcaVeiculoController::class, 'update'])
        ->name('cadastros.marca-veiculo.update');
    Route::patch('/cadastros/marca-veiculo/{id}/status', [MarcaVeiculoController::class, 'toggleStatus'])
        ->name('cadastros.marca-veiculo.toggleStatus');

    Route::get('/cadastros/tipo-veiculo', [TipoVeiculoController::class, 'index'])
        ->name('cadastros.tipo-veiculo.index');
    Route::post('/cadastros/tipo-veiculo', [TipoVeiculoController::class, 'store'])
        ->name('cadastros.tipo-veiculo.store');
    Route::put('/cadastros/tipo-veiculo/{sigla}', [TipoVeiculoController::class, 'update'])
        ->name('cadastros.tipo-veiculo.update');
    Route::patch('/cadastros/tipo-veiculo/{sigla}/status', [TipoVeiculoController::class, 'toggleStatus'])
        ->name('cadastros.tipo-veiculo.toggleStatus');

    Route::get('/cadastros/modelo-veiculo', [ModeloVeiculoController::class, 'index'])
        ->name('cadastros.modelo-veiculo.index');
    Route::post('/cadastros/modelo-veiculo', [ModeloVeiculoController::class, 'store'])
        ->name('cadastros.modelo-veiculo.store');
    Route::put('/cadastros/modelo-veiculo/{id}', [ModeloVeiculoController::class, 'update'])
        ->name('cadastros.modelo-veiculo.update');
    Route::patch('/cadastros/modelo-veiculo/{id}/status', [ModeloVeiculoController::class, 'toggleStatus'])
        ->name('cadastros.modelo-veiculo.toggleStatus');

    Route::get('/cadastros/veiculo', [VeiculoController::class, 'index'])
        ->name('cadastros.veiculo.index');
    Route::post('/cadastros/veiculo', [VeiculoController::class, 'store'])
        ->name('cadastros.veiculo.store');
    Route::put('/cadastros/veiculo/{id}', [VeiculoController::class, 'update'])
        ->name('cadastros.veiculo.update');
    Route::patch('/cadastros/veiculo/{id}/status', [VeiculoController::class, 'toggleStatus'])
        ->name('cadastros.veiculo.toggleStatus');

    Route::prefix('/api')->group(function () {
        Route::get('/vehicle-models', [VeiculoController::class, 'getModels'])
            ->name('cadastros.veiculo.api.models');
        Route::get('/vehicle-configurations', [VeiculoController::class, 'getConfigurations'])
            ->name('cadastros.veiculo.api.configurations');
        Route::get('/configuration-positions', [VeiculoController::class, 'getConfigurationPositions'])
            ->name('cadastros.veiculo.api.configuration-positions');
    });

    Route::get('/cadastros/usuario', [UsuarioController::class, 'index'])
        ->name('cadastros.usuario.index');
    Route::post('/cadastros/usuario', [UsuarioController::class, 'store'])
        ->name('cadastros.usuario.store');
    Route::put('/cadastros/usuario/{id}', [UsuarioController::class, 'update'])
        ->name('cadastros.usuario.update');
    Route::patch('/cadastros/usuario/{id}/status', [UsuarioController::class, 'toggleStatus'])
        ->name('cadastros.usuario.toggleStatus');
    Route::patch('/cadastros/usuario/{id}/reset-password', [UsuarioController::class, 'resetPassword'])
        ->name('cadastros.usuario.resetPassword');

    Route::get('/relatorios/pneus', [PneuRelatorioController::class, 'index'])
        ->name('relatorios.pneus.index');
    Route::get('/relatorios/pneus/exportar-excel', [PneuRelatorioController::class, 'exportarExcel'])
        ->name('relatorios.pneus.excel');
});

require __DIR__.'/settings.php';
