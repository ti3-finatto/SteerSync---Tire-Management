<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class DesenhoBanda extends Model
{
    protected $table = 't_desenhobanda';

    protected $primaryKey = 'DESB_CODIGO';

    public $timestamps = false;

    protected $fillable = [
        'DESB_DESCRICAO',
        'DESB_SIGLA',
        'DESB_STATUS',
        'USU_CODIGO',
        'DESB_DATACADASTRO',
    ];

    protected function casts(): array
    {
        return [
            'DESB_CODIGO' => 'integer',
            'USU_CODIGO' => 'integer',
        ];
    }
}
