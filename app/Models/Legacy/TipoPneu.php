<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class TipoPneu extends Model
{
    protected $table = 't_tipo';

    protected $primaryKey = 'TIPO_CODIGO';

    public $timestamps = false;

    protected $fillable = [
        'TIPO_STATUS',
        'TIPO_DESCRICAO',
        'TIPO_INSPECAO',
        'MARP_CODIGO',
        'MODP_CODIGO',
        'MEDP_CODIGO',
        'TIPO_DESENHO',
        'TIPO_NSULCO',
        'TIPO_MMSEGURANCA',
        'TIPO_MMNOVO',
        'TIPO_MMDESGEIXOS',
        'TIPO_MMDESGPAR',
        'USU_CODIGO',
        'TIPO_DATACADASTRO',
    ];

    protected function casts(): array
    {
        return [
            'TIPO_CODIGO' => 'integer',
            'MARP_CODIGO' => 'integer',
            'MODP_CODIGO' => 'integer',
            'MEDP_CODIGO' => 'integer',
            'TIPO_NSULCO' => 'integer',
            'TIPO_MMSEGURANCA' => 'float',
            'TIPO_MMNOVO' => 'float',
            'TIPO_MMDESGEIXOS' => 'float',
            'TIPO_MMDESGPAR' => 'float',
            'USU_CODIGO' => 'integer',
        ];
    }
}
