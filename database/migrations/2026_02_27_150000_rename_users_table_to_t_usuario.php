<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Padronizacao legada: renomeia a tabela de autenticacao para t_usuario.
     * O model App\Models\User continua sendo o ponto unico de acesso.
     */
    public function up(): void
    {
        if (Schema::hasTable('users') && ! Schema::hasTable('t_usuario')) {
            Schema::rename('users', 't_usuario');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('t_usuario') && ! Schema::hasTable('users')) {
            Schema::rename('t_usuario', 'users');
        }
    }
};
