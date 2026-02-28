<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('admin pode criar unidade', function () {
    $admin = User::factory()->create([
        'USU_CODIGO' => 2001,
        'USU_TIPO' => 'A',
    ]);

    $response = $this->actingAs($admin)->postJson(route('cadastros.unidade.store'), [
        'UNI_DESCRICAO' => 'Filial Centro',
        'CLI_CNPJ' => '12345678000199',
        'CLI_UF' => 'SP',
        'CLI_CIDADE' => 'Sao Paulo',
        'UNI_STATUS' => 'A',
    ]);

    $response->assertCreated();

    $this->assertDatabaseHas('t_clienteunidade', [
        'UNI_DESCRICAO' => 'Filial Centro',
        'CLI_CNPJ' => '12345678000199',
        'CLI_UF' => 'SP',
        'CLI_CIDADE' => 'Sao Paulo',
        'UNI_STATUS' => 'A',
    ]);
});

test('inativacao de unidade com vinculo retorna 409', function () {
    $admin = User::factory()->create([
        'USU_CODIGO' => 2002,
        'USU_TIPO' => 'A',
    ]);

    $unidadeId = DB::table('t_clienteunidade')->insertGetId([
        'UNI_DESCRICAO' => 'Filial Norte',
        'UNI_STATUS' => 'A',
        'CLI_CNPJ' => '',
        'CLI_UF' => '',
        'CLI_CIDADE' => '',
    ]);

    DB::table('t_pneu')->insert([
        'UNI_CODIGO' => $unidadeId,
    ]);

    $response = $this->actingAs($admin)->patchJson(route('cadastros.unidade.toggleStatus', ['id' => $unidadeId]));

    $response->assertStatus(409);
});

test('nao-admin recebe 403 no cadastro de unidade', function () {
    $user = User::factory()->create([
        'USU_CODIGO' => 2003,
        'USU_TIPO' => 'U',
    ]);

    $response = $this->actingAs($user)->postJson(route('cadastros.unidade.store'), [
        'UNI_DESCRICAO' => 'Filial Bloqueada',
        'UNI_STATUS' => 'A',
    ]);

    $response->assertForbidden();
});
