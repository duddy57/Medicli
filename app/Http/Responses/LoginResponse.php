<?php

declare(strict_types = 1);

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user    = $request->user();
        $clinica = $user?->currentClinica ?? $user?->personalClinica();

        if (! $clinica) {
            abort(403);
        }

        URL::defaults(['current_clinica' => $clinica->slug]);

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false], 200)
            : redirect()->intended(route('dashboard'));
    }
}
