<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class MarcaVeiculo extends Model
{
    protected $table = 't_marcaveiculo';

    protected $primaryKey = 'MARV_CODIGO';

    public $timestamps = false;

    protected $fillable = [
        'MARV_DESCRICAO',
        'MARV_STATUS',
        'USU_CODIGO',
    ];

    protected function casts(): array
    {
        return [
            'MARV_CODIGO' => 'integer',
            'USU_CODIGO' => 'integer',
        ];
    }
}
