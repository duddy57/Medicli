<?php

declare(strict_types = 1);

namespace App\Policies;

use App\Models\Clinica;
use App\Models\User;

class SpecialtyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
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
    public function create(User $user, Clinica $Clinica): bool
    {
        return $user->hasClinicaPermission($Clinica, 'specialty:create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Clinica $Clinica): bool
    {
        return $user->hasClinicaPermission($Clinica, 'specialty:update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Clinica $Clinica): bool
    {
        return ! $Clinica->is_personal && $user->hasClinicaPermission($Clinica, 'specialty:delete');
    }
}
