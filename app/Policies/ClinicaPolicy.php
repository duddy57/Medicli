<?php

declare(strict_types = 1);

namespace App\Policies;

use App\Models\Clinica;
use App\Models\User;

class ClinicaPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Clinica $Clinica): bool
    {
        return $user->belongsToClinica($Clinica);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Clinica $clinica): bool
    {
        return $user->hasClinicaPermission($clinica, 'clinica:update');
    }

    /**
     * Determine whether the user can add a member to the Clinica.
     */
    public function addMember(User $user, Clinica $Clinica): bool
    {
        return $user->hasClinicaPermission($Clinica, 'member:add');
    }

    /**
     * Determine whether the user can update a member's role in the Clinica.
     */
    public function updateMember(User $user, Clinica $Clinica): bool
    {
        return $user->hasClinicaPermission($Clinica, 'member:update');
    }

    /**
     * Determine whether the user can remove a member from the Clinica.
     */
    public function removeMember(User $user, Clinica $Clinica): bool
    {
        return $user->hasClinicaPermission($Clinica, 'member:remove');
    }

    /**
     * Determine whether the user can invite members to the Clinica.
     */
    public function inviteMember(User $user, Clinica $Clinica): bool
    {
        return $user->hasClinicaPermission($Clinica, 'invitation:create');
    }

    /**
     * Determine whether the user can cancel invitations.
     */
    public function cancelInvitation(User $user, Clinica $Clinica): bool
    {
        return $user->hasClinicaPermission($Clinica, 'invitation:cancel');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Clinica $Clinica): bool
    {
        return ! $Clinica->is_personal && $user->hasClinicaPermission($Clinica, 'Clinica:delete');
    }
}
