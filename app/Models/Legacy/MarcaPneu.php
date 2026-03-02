<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class MarcaPneu extends Model
{
    protected $table = 't_marcapneu';

    protected $primaryKey = 'MARP_CODIGO';

    public $timestamps = false;

    protected $fillable = [
        'MARP_DESCRICAO',
        'MARP_TIPO',
        'MARP_STATUS',
        'USU_CODIGO',
        'MARP_DATACADASTRO',
    ];

    protected function casts(): array
    {
        return [
            'MARP_CODIGO' => 'integer',
            'USU_CODIGO' => 'integer',
        ];
    }
}
