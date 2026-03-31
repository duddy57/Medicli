<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Clinicas;

use App\Enums\ClinicaRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Clinicas\AcceptClinicaInvitationRequest;
use App\Http\Requests\Clinicas\CreateClinicaInvitationRequest;
use App\Models\Clinica;
use App\Models\ClinicaInvitation;
use App\Notifications\Clinicas\ClinicaInvitation as ClinicaInvitationNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;

class ClinicaInvitationController extends Controller
{
    /**
     * Store a newly created invitation.
     */
    public function store(CreateClinicaInvitationRequest $request, Clinica $clinica): RedirectResponse
    {
        Gate::authorize('inviteMember', $clinica);

        $invitation = $clinica->invitations()->create([
            'email'      => $request->validated('email'),
            'role'       => ClinicaRole::from($request->validated('role')),
            'invited_by' => $request->user()->id,
            'expires_at' => now()->addDays(3),
        ]);

        Notification::route('mail', $invitation->email)
            ->notify(new ClinicaInvitationNotification($invitation));

        return to_route('clinicas.edit', ['clinica' => $clinica->slug]);
    }

    /**
     * Cancel the specified invitation.
     */
    public function destroy(Clinica $clinica, ClinicaInvitation $invitation): RedirectResponse
    {
        abort_unless($invitation->clinica_id === $clinica->id, 404);

        Gate::authorize('cancelInvitation', $clinica);

        $invitation->delete();

        return to_route('clinicas.edit', ['clinica' => $clinica->slug]);
    }

    /**
     * Accept the invitation.
     */
    public function accept(AcceptClinicaInvitationRequest $request, ClinicaInvitation $invitation): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user, $invitation): void {
            $clinica = $invitation->clinica;

            $membership = $clinica->memberships()->firstOrCreate(
                ['user_id' => $user->id],
                ['role' => $invitation->role],
            );

            $wasRecentlyCreated = $membership->wasRecentlyCreated;

            $invitation->update(['accepted_at' => now()]);

            $user->switchClinica($clinica);
        });

        return to_route('dashboard');
    }
}
