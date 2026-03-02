<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class StatusPneu extends Model
{
    protected $table = 't_statuspneu';

    protected $primaryKey = 'STP_SIGLA';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'STP_SIGLA',
        'STP_DESCRICAO',
        'STP_ORDEM',
    ];

    protected function casts(): array
    {
        return [
            'STP_ORDEM' => 'integer',
        ];
    }
}
