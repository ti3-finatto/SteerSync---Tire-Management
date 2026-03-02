<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class MedidaPneu extends Model
{
    protected $table = 't_medidapneu';

    protected $primaryKey = 'MEDP_CODIGO';

    public $timestamps = false;

    protected $fillable = [
        'MEDP_DESCRICAO',
        'CAL_RECOMENDADA',
        'MEDP_STATUS',
        'USU_CODIGO',
        'MEDP_DATACADASTRO',
    ];

    protected function casts(): array
    {
        return [
            'MEDP_CODIGO' => 'integer',
            'CAL_RECOMENDADA' => 'float',
            'USU_CODIGO' => 'integer',
        ];
    }
}
