<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class Unidade extends Model
{
    protected $table = 't_clienteunidade';

    protected $primaryKey = 'UNI_CODIGO';

    public $timestamps = false;

    protected $fillable = [
        'UNI_DESCRICAO',
        'UNI_STATUS',
        'CLI_CNPJ',
        'CLI_UF',
        'CLI_CIDADE',
    ];

    protected function casts(): array
    {
        return [
            'UNI_CODIGO' => 'integer',
        ];
    }
}
