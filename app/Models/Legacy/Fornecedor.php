<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    protected $table = 't_fornecedor';

    protected $primaryKey = 'FORN_CODIGO';

    public $timestamps = false;

    protected $fillable = [
        'FORN_CNPJ',
        'FORN_RAZAO',
        'FORN_TELEFONE',
        'FORN_EMAIL',
        'FORN_STATUS',
        'USU_CODIGO',
    ];

    protected function casts(): array
    {
        return [
            'FORN_CODIGO' => 'integer',
            'USU_CODIGO' => 'integer',
        ];
    }
}
