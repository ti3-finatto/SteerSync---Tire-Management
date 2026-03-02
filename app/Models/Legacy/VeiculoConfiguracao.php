<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class VeiculoConfiguracao extends Model
{
    protected $table = 't_veiculoconfiguracao';

    protected $primaryKey = 'VEIC_CODIGO';

    public $timestamps = false;

    protected $fillable = [
        'VEIC_DESCRICAO',
        'VEIC_STATUS',
        'VEIC_TIPO',
        'VEIC_IMAGEM',
        'VEIC_IMG_LARGURA',
        'VEIC_IMG_ALTURA',
        'VEIC_MARGIN_TOP',
    ];

    protected function casts(): array
    {
        return [
            'VEIC_CODIGO' => 'integer',
            'VEIC_IMG_LARGURA' => 'integer',
            'VEIC_IMG_ALTURA' => 'integer',
            'VEIC_MARGIN_TOP' => 'integer',
        ];
    }
}
