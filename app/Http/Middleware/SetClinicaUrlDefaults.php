<?php

declare(strict_types = 1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetClinicaUrlDefaults
{
    /**
     * Set the default URL parameters for clinica-based routes.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($currentClinica = $request->user()?->currentClinica) {
            URL::defaults([
                'current_clinica' => $currentClinica->slug,
                'clinica'         => $currentClinica->slug,
            ]);
        }

        return $next($request);
    }
}
