<?php

use App\Http\Controllers\Cadastros\FornecedorController;
use App\Http\Controllers\Cadastros\UnidadeController;
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
});

require __DIR__.'/settings.php';
