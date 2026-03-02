<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class ModeloPneu extends Model
{
    protected $table = 't_modelopneu';

    protected $primaryKey = 'MODP_CODIGO';

    public $timestamps = false;

    protected $fillable = [
        'MODP_DESCRICAO',
        'MODP_STATUS',
        'MARP_CODIGO',
        'USU_CODIGO',
    ];

    protected function casts(): array
    {
        return [
            'MODP_CODIGO' => 'integer',
            'MARP_CODIGO' => 'integer',
            'USU_CODIGO' => 'integer',
        ];
    }
}
