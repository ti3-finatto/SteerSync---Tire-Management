<?php

namespace App\Models\Legacy;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Veiculo extends Model
{
    protected $table = 't_veiculo';

    protected $primaryKey = 'VEI_CODIGO';

    public $timestamps = false;

    protected $fillable = [
        'VEI_PLACA',
        'VEI_CHASSI',
        'VEI_FROTA',
        'VEI_STATUS',
        'CAL_RECOMENDADA',
        'MODV_CODIGO',
        'UNI_CODIGO',
        'VEIC_CODIGO',
        'VEI_KM',
        'USU_CODIGO',
        'user_id',
        'VEI_DATACADASTRO',
        'VEI_OBS',
        'VEI_ODOMETRO',
    ];

    protected function casts(): array
    {
        return [
            'VEI_CODIGO' => 'integer',
            'CAL_RECOMENDADA' => 'integer',
            'MODV_CODIGO' => 'integer',
            'UNI_CODIGO' => 'integer',
            'VEIC_CODIGO' => 'integer',
            'VEI_KM' => 'integer',
            'USU_CODIGO' => 'integer',
            'user_id' => 'integer',
        ];
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class, 'UNI_CODIGO', 'UNI_CODIGO');
    }

    public function modelo(): BelongsTo
    {
        return $this->belongsTo(ModeloVeiculo::class, 'MODV_CODIGO', 'MODV_CODIGO');
    }

    public function configuracao(): BelongsTo
    {
        return $this->belongsTo(VeiculoConfiguracao::class, 'VEIC_CODIGO', 'VEIC_CODIGO');
    }

    public function usuarioCadastro(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('VEI_STATUS', 'A');
    }
}
