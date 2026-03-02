<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class TipoVeiculo extends Model
{
    protected $table = 't_tipoveiculo';

    protected $primaryKey = 'TPVE_SIGLA';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'TPVE_SIGLA',
        'TPVE_DESCRICAO',
        'TPVE_STATUS',
        'TPVE_ORDEM',
    ];

    protected function casts(): array
    {
        return [
            'TPVE_PADRAO' => 'boolean',
            'TPVE_ORDEM'  => 'integer',
        ];
    }
}
