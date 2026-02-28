<?php

namespace App\Actions\Fortify;

use Closure;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\LoginRateLimiter;

class EnsureUserIsActive
{
    public function __construct(
        protected StatefulGuard $guard,
        protected LoginRateLimiter $limiter,
    ) {}

    /**
     * Valida credenciais antes do fluxo padrao do Fortify e bloqueia login
     * quando o usuario autenticavel estiver inativo.
     */
    public function handle($request, Closure $next): mixed
    {
        $provider = $this->guard->getProvider();

        $credentials = $request->only(Fortify::username(), 'password');
        $user = $provider->retrieveByCredentials($credentials);

        if (! $user || ! $provider->validateCredentials($user, ['password' => $request->password])) {
            return $next($request);
        }

        if (method_exists($user, 'isActive') && ! $user->isActive()) {
            $this->limiter->increment($request);

            throw ValidationException::withMessages([
                Fortify::username() => ["Usu\u{00E1}rio inativo"],
            ]);
        }

        return $next($request);
    }
}
