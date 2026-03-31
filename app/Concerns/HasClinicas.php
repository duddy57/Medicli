<?php

declare(strict_types = 1);

namespace App\Concerns;

use App\Enums\ClinicaRole;
use App\Models\Clinica;
use App\Models\Membership;
use App\Support\ClinicaPermissions;
use App\Support\UserClinica;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

trait HasClinicas
{
    /**
     * Get all of the clinicas the user belongs to.
     *
     * @return BelongsToMany<Clinica, $this>
     */
    public function clinicas(): BelongsToMany
    {
        return $this->belongsToMany(Clinica::class, 'clinica_members', 'user_id', 'clinica_id')
            ->withPivot(['role'])
            ->withTimestamps();
    }

    /**
     * Get all of the Clinicas the user owns.
     *
     * @return HasManyThrough<Clinica, Membership, $this>
     */
    public function ownedClinicas(): HasManyThrough
    {
        return $this->hasManyThrough(
            Clinica::class,
            Membership::class,
            'user_id',
            'id',
            'id',
            'clinica_id',
        )->where('clinica_members.role', ClinicaRole::Owner->value);
    }

    /**
     * Get all of the memberships for the user.
     *
     * @return HasMany<Membership, $this>
     */
    public function clinicaMemberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'user_id');
    }

    /**
     * Get the user's current Clinica.
     *
     * @return BelongsTo<Clinica, $this>
     */
    public function currentClinica(): BelongsTo
    {
        return $this->belongsTo(Clinica::class, 'current_clinica_id');
    }

    /**
     * Get the user's personal Clinica.
     */
    public function personalClinica(): ?Clinica
    {
        return $this->clinicas()
            ->where('is_personal', true)
            ->first();
    }

    /**
     * Switch to the given Clinica.
     */
    public function switchClinica(Clinica $clinica): bool
    {
        if (! $this->belongsToClinica($clinica)) {
            return false;
        }

        $this->update(['current_clinica_id' => $clinica->id]);
        $this->setRelation('currentClinica', $clinica);

        URL::defaults(['current_clinica' => $clinica->slug]);

        return true;
    }

    /**
     * Determine if the user belongs to the given Clinica.
     */
    public function belongsToClinica(Clinica $clinica): bool
    {
        return $this->clinicas()->where('clinicas.id', $clinica->id)->exists();
    }

    /**
     * Determine if the given Clinica is the user's current Clinica.
     */
    public function isCurrentClinica(Clinica $clinica): bool
    {
        return $this->current_clinica_id === $clinica->id;
    }

    /**
     * Determine if the user is the owner of the given Clinica.
     */
    public function ownsClinica(Clinica $clinica): bool
    {
        return $this->clinicaRole($clinica) === ClinicaRole::Owner;
    }

    /**
     * Get the user's role on the given Clinica.
     */
    public function clinicaRole(Clinica $clinica): ?ClinicaRole
    {
        return $this->clinicaMemberships()
            ->where('clinica_id', $clinica->id)
            ->first()
            ?->role;
    }

    /**
     * Get the user's Clinicas as a collection of UserClinica objects.
     *
     * @return Collection<int, UserClinica>
     */
    public function toUserClinicas(bool $includeCurrent = false): Collection
    {
        return $this->clinicas()
            ->get()
            ->map(fn (Clinica $clinica) => ! $includeCurrent && $this->isCurrentClinica($clinica) ? null : $this->toUserClinica($clinica))
            ->filter()
            ->values();
    }

    /**
     * Get the user's Clinica as a UserClinica object.
     */
    public function toUserClinica(Clinica $clinica): UserClinica
    {
        $role = $this->clinicaRole($clinica);

        return new UserClinica(
            id: $clinica->id,
            name: $clinica->name,
            slug: $clinica->slug,
            isPersonal: $clinica->is_personal,
            role: $role?->value,
            roleLabel: $role?->label(),
            isCurrent: $this->isCurrentClinica($clinica),
        );
    }

    /**
     * Get the standard permissions for a Clinica as a ClinicaPermissions object.
     */
    public function toClinicaPermissions(Clinica $clinica): ClinicaPermissions
    {
        $role = $this->clinicaRole($clinica);

        return new ClinicaPermissions(
            canUpdateClinica: $role?->hasPermission('clinica:update') ?? false,
            canDeleteClinica: $role?->hasPermission('clinica:delete') ?? false,
            canAddEmployee: $role?->hasPermission('employee:add') ?? false,
            canUpdateEmployee: $role?->hasPermission('employee:update') ?? false,
            canRemoveEmployee: $role?->hasPermission('employee:remove') ?? false,
            canAddRole: $role?->hasPermission('role:add') ?? false,
            canUpdateRole: $role?->hasPermission('role:update') ?? false,
            canRemoveRole: $role?->hasPermission('role:remove') ?? false,
            canAddSpeciality: $role?->hasPermission('speciality:add') ?? false,
            canUpdateSpeciality: $role?->hasPermission('speciality:update') ?? false,
            canRemoveSpeciality: $role?->hasPermission('speciality:remove') ?? false,
            canAddService: $role?->hasPermission('service:add') ?? false,
            canUpdateService: $role?->hasPermission('service:update') ?? false,
            canRemoveService: $role?->hasPermission('service:remove') ?? false,
            canAddAppointment: $role?->hasPermission('appointment:add') ?? false,
            canUpdateAppointment: $role?->hasPermission('appointment:update') ?? false,
            canRemoveAppointment: $role?->hasPermission('appointment:remove') ?? false,
            canCreateInvitation: $role?->hasPermission('invitation:create') ?? false,
            canCancelInvitation: $role?->hasPermission('invitation:cancel') ?? false,
        );
    }

    public function fallbackClinica(?Clinica $excluding = null): ?Clinica
    {
        return $this->clinicas()
            ->when($excluding, fn ($query) => $query->where('clinicas.id', '!=', $excluding->id))
            ->orderByRaw('LOWER(clinicas.name)')
            ->first();
    }

    /**
     * Determine if the user has the given permission on the Clinica.
     */
    public function hasClinicaPermission(Clinica $clinica, string $permission): bool
    {
        return $this->clinicaRole($clinica)?->hasPermission($permission) ?? false;
    }
}
