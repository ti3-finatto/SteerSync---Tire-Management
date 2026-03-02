<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class Pneu extends Model
{
    protected $table = 't_pneu';

    protected $primaryKey = 'PNE_CODIGO';

    public $timestamps = false;

    protected $fillable = [
        'PNE_FOGO',
        'TIPO_CODIGO',
        'CAL_RECOMENDADA',
        'PNE_DOT',
        'PNE_KM',
        'PNE_MM',
        'PNE_STATUS',
        'PNE_STATUSCOMPRA',
        'PNE_VALORCOMPRA',
        'PNE_VIDACOMPRA',
        'PNE_VIDAATUAL',
        'TIPO_CODIGORECAPE',
        'ITS_CODIGO',
        'PNE_CUSTOATUAL',
        'USU_CODIGO',
        'UNI_CODIGO',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'PNE_CODIGO' => 'integer',
            'TIPO_CODIGO' => 'integer',
            'CAL_RECOMENDADA' => 'float',
            'PNE_KM' => 'integer',
            'PNE_MM' => 'float',
            'PNE_VALORCOMPRA' => 'float',
            'TIPO_CODIGORECAPE' => 'integer',
            'ITS_CODIGO' => 'integer',
            'PNE_CUSTOATUAL' => 'float',
            'USU_CODIGO' => 'integer',
            'UNI_CODIGO' => 'integer',
            'user_id' => 'integer',
        ];
    }
}
