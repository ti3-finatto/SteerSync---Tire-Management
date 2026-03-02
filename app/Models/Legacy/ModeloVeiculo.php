<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class ModeloVeiculo extends Model
{
    protected $table = 't_modeloveiculo';

    protected $primaryKey = 'MODV_CODIGO';

    public $timestamps = false;

    protected $fillable = [
        'MODV_DESCRICAO',
        'MODV_STATUS',
        'MARV_CODIGO',
        'VEIC_TIPO',
        'USU_CODIGO',
    ];

    protected function casts(): array
    {
        return [
            'MODV_CODIGO' => 'integer',
            'MARV_CODIGO' => 'integer',
            'USU_CODIGO' => 'integer',
        ];
    }
}
