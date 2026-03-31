<?php

declare(strict_types = 1);

namespace App\Http\Middleware;

use App\Enums\ClinicaRole;
use App\Models\Clinica;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicaMembership
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, ?string $minimumRole = null): Response
    {
        [$user, $clinica] = [$request->user(), $this->clinica($request)];

        abort_if(! $user || ! $clinica || ! $user->belongsToClinica($clinica), 403);

        $this->ensureClinicaMemberHasRequiredRole($user, $clinica, $minimumRole);

        if ($request->route('current_clinica') && ! $user->isCurrentClinica($clinica)) {
            $user->switchClinica($clinica);
        }

        return $next($request);
    }

    /**
     * Ensure the given user has at least the given role, if applicable.
     */
    protected function ensureClinicaMemberHasRequiredRole(User $user, Clinica $clinica, ?string $minimumRole): void
    {
        if ($minimumRole === null) {
            return;
        }

        $role = $user->clinicaRole($clinica);

        $requiredRole = ClinicaRole::tryFrom($minimumRole);

        abort_if(
            $requiredRole === null ||
            ! $role instanceof ClinicaRole ||
            ! $role->isAtLeast($requiredRole),
            403,
        );
    }

    /**
     * Get the clinica associated with the request.
     */
    protected function clinica(Request $request): ?Clinica
    {
        $clinica = $request->route('current_clinica') ?? $request->route('clinica');

        if (is_string($clinica)) {
            return Clinica::where('slug', $clinica)->first();
        }

        return $clinica;
    }
}
