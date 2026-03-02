<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class Posicao extends Model
{
    protected $table = 't_posicao';

    protected $primaryKey = 'POS_CODIGO';

    public $timestamps = false;

    protected $fillable = [
        'POS_DESCRICAO',
        'POS_STATUS',
    ];

    protected function casts(): array
    {
        return [
            'POS_CODIGO' => 'integer',
        ];
    }
}
