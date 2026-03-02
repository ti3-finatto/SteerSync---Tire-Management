<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class PosicaoConfiguracao extends Model
{
    protected $table = 't_posicaoxconfiguracao';

    protected $primaryKey = 'PSCF_CODIGO';

    public $timestamps = false;

    protected $fillable = [
        'VEIC_CODIGO',
        'POS_CODIGO',
        'PSCF_PAR',
        'PSCF_EIXO',
    ];

    protected function casts(): array
    {
        return [
            'PSCF_CODIGO' => 'integer',
            'VEIC_CODIGO' => 'integer',
            'POS_CODIGO' => 'integer',
            'PSCF_PAR' => 'integer',
            'PSCF_EIXO' => 'integer',
        ];
    }
}
