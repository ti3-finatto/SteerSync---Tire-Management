<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('admin pode criar fornecedor', function () {
    $admin = User::factory()->create([
        'USU_CODIGO' => 1001,
        'USU_TIPO' => 'A',
    ]);

    $response = $this->actingAs($admin)->postJson(route('cadastros.fornecedor.store'), [
        'FORN_RAZAO' => 'Fornecedor Alpha',
        'FORN_CNPJ' => '12345678000199',
        'FORN_EMAIL' => 'alpha@fornecedor.com',
        'FORN_TELEFONE' => '11999999999',
        'FORN_STATUS' => 'A',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('t_fornecedor', [
        'FORN_RAZAO' => 'Fornecedor Alpha',
        'FORN_CNPJ' => '12345678000199',
        'FORN_STATUS' => 'A',
        'USU_CODIGO' => 1001,
    ]);
});

test('duplicidade de cnpj retorna 409', function () {
    $admin = User::factory()->create([
        'USU_CODIGO' => 1002,
        'USU_TIPO' => 'A',
    ]);

    DB::table('t_fornecedor')->insert([
        'FORN_CNPJ' => '11222333000144',
        'FORN_RAZAO' => 'Fornecedor Existente',
        'FORN_TELEFONE' => '1133334444',
        'FORN_EMAIL' => 'existente@fornecedor.com',
        'FORN_STATUS' => 'A',
        'USU_CODIGO' => 1002,
        'FORN_DATACADASTRO' => now(),
    ]);

    $response = $this->actingAs($admin)->postJson(route('cadastros.fornecedor.store'), [
        'FORN_RAZAO' => 'Fornecedor Duplicado',
        'FORN_CNPJ' => '11222333000144',
        'FORN_EMAIL' => 'duplicado@fornecedor.com',
        'FORN_TELEFONE' => '11999999999',
        'FORN_STATUS' => 'A',
    ]);

    $response->assertStatus(409);
});

test('admin sem usu_codigo usa id autenticado como fallback', function () {
    $admin = User::factory()->create([
        'USU_TIPO' => 'A',
    ]);

    $admin->forceFill([
        'USU_CODIGO' => null,
    ])->saveQuietly();

    $response = $this->actingAs($admin)->postJson(route('cadastros.fornecedor.store'), [
        'FORN_RAZAO' => 'Fornecedor Fallback',
        'FORN_CNPJ' => '44332211000100',
        'FORN_EMAIL' => 'fallback@fornecedor.com',
        'FORN_TELEFONE' => '11977776666',
        'FORN_STATUS' => 'A',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('t_fornecedor', [
        'FORN_RAZAO' => 'Fornecedor Fallback',
        'USU_CODIGO' => $admin->id,
    ]);

    $this->assertDatabaseHas('t_usuario', [
        'id' => $admin->id,
        'USU_CODIGO' => $admin->id,
    ]);
});

test('nao-admin recebe 403 nos cadastros base', function () {
    $user = User::factory()->create([
        'USU_CODIGO' => 1003,
        'USU_TIPO' => 'U',
    ]);

    $response = $this->actingAs($user)->postJson(route('cadastros.fornecedor.store'), [
        'FORN_RAZAO' => 'Fornecedor Bloqueado',
        'FORN_CNPJ' => '55444333000122',
        'FORN_EMAIL' => 'bloqueado@fornecedor.com',
        'FORN_TELEFONE' => '11988887777',
        'FORN_STATUS' => 'A',
    ]);

    $response->assertForbidden();
});

test('admin pode alternar status do fornecedor', function () {
    $admin = User::factory()->create([
        'USU_CODIGO' => 1004,
        'USU_TIPO' => 'A',
    ]);

    $fornecedorId = DB::table('t_fornecedor')->insertGetId([
        'FORN_CNPJ' => '99888777000166',
        'FORN_RAZAO' => 'Fornecedor Toggle',
        'FORN_TELEFONE' => '1144445555',
        'FORN_EMAIL' => 'toggle@fornecedor.com',
        'FORN_STATUS' => 'A',
        'USU_CODIGO' => 1004,
        'FORN_DATACADASTRO' => now(),
    ]);

    $response = $this->actingAs($admin)->patchJson(route('cadastros.fornecedor.toggleStatus', ['id' => $fornecedorId]));

    $response->assertOk();

    $this->assertDatabaseHas('t_fornecedor', [
        'FORN_CODIGO' => $fornecedorId,
        'FORN_STATUS' => 'I',
    ]);
});
