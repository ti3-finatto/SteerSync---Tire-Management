<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $table = 't_usuario';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'USU_CODIGO',
        'cpf',
        'status',
        'profile_photo_path',
        'phone',
        'username',
        'USU_TIPO',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => UserStatus::class,
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::ATIVO;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatus::ATIVO->value);
    }

    /**
     * Resolve o codigo legado do usuario.
     * Prioriza USU_CODIGO e usa o identificador autenticavel (id) como fallback.
     */
    public function legacyUserCode(): int
    {
        $legacyCode = (int) ($this->USU_CODIGO ?? 0);

        if ($legacyCode > 0) {
            return $legacyCode;
        }

        $authIdentifier = $this->getAuthIdentifier();

        return is_numeric($authIdentifier) ? max((int) $authIdentifier, 0) : 0;
    }

    /**
     * Garante que USU_CODIGO esteja sincronizado com o identificador atual.
     */
    public function ensureLegacyUserCode(): int
    {
        $resolvedCode = $this->legacyUserCode();
        $currentCode = (int) ($this->USU_CODIGO ?? 0);

        if ($resolvedCode > 0 && $currentCode !== $resolvedCode) {
            $this->forceFill([
                'USU_CODIGO' => $resolvedCode,
            ])->saveQuietly();

            $this->setAttribute('USU_CODIGO', $resolvedCode);
        }

        return $resolvedCode;
    }
}
