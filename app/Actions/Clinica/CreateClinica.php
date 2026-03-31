<?php

declare(strict_types = 1);

namespace App\Actions\Clinica;

use App\Enums\ClinicaRole;
use App\Models\Clinica;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateClinica
{
    /**
     * Create a new Clinica and add the user as owner.
     */
    public function handle(User $user, array | string $data, bool $isPersonal = false): Clinica
    {
        if (is_string($data)) {
            $data = ['name' => $data];
        }

        return DB::transaction(function () use ($user, $data, $isPersonal) {
            $clinica = Clinica::create([
                'name'          => $data['name'],
                'contact_email' => $data['contact_email'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'address'       => $data['address'] ?? null,
                'is_personal'   => $isPersonal,
                'owner_id'      => $user->id,
            ]);

            $membership = $clinica->memberships()->create([
                'user_id' => $user->id,
                'role'    => ClinicaRole::Owner,
            ]);

            $user->switchClinica($clinica);

            return $clinica;
        });
    }
}
