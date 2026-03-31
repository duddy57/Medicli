<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Clinicas;

use App\Enums\ClinicaRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinicas\UpdateClinicaMemberRequest;
use App\Models\Clinica;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class ClinicaMemberController extends Controller
{
    /**
     * Update the specified clinica member's role.
     */
    public function update(UpdateClinicaMemberRequest $request, Clinica $clinica, User $user): RedirectResponse
    {
        Gate::authorize('updateMember', $clinica);

        $newRole = ClinicaRole::from($request->validated('role'));

        $clinica->memberships()
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->update(['role' => $newRole]);

        return to_route('clinicas.edit', ['clinica' => $clinica->slug]);
    }

    /**
     * Remove the specified clinica member.
     */
    public function destroy(Clinica $clinica, User $user): RedirectResponse
    {
        Gate::authorize('removeMember', $clinica);

        abort_if($clinica->owner()?->is($user), 403, 'The clinica owner cannot be removed.');

        $clinica->memberships()
            ->where('user_id', $user->id)
            ->delete();

        if ($user->isCurrentClinica($clinica)) {
            $user->switchClinica($user->personalClinica());
        }

        return to_route('clinicas.edit', ['clinica' => $clinica->slug]);
    }
}
