<?php

declare(strict_types = 1);

namespace App\Support;

readonly class ClinicaPermissions
{
    public function __construct(
        public bool $canUpdateClinica,
        public bool $canDeleteClinica,
        public bool $canAddEmployee,
        public bool $canUpdateEmployee,
        public bool $canRemoveEmployee,
        public bool $canAddRole,
        public bool $canUpdateRole,
        public bool $canRemoveRole,
        public bool $canAddSpeciality,
        public bool $canUpdateSpeciality,
        public bool $canRemoveSpeciality,
        public bool $canAddService,
        public bool $canUpdateService,
        public bool $canRemoveService,
        public bool $canAddAppointment,
        public bool $canUpdateAppointment,
        public bool $canRemoveAppointment,
        public bool $canCreateInvitation,
        public bool $canCancelInvitation,
    ) {
        //
    }
}
