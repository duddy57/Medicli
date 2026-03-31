<?php

declare(strict_types = 1);

namespace App\Actions\Fortify;

use App\Actions\Clinica\CreateClinica;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\ClinicaRole;
use App\Models\Clinica;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;
    use ProfileValidationRules;

    public function __construct(private CreateClinica $createClinica)
    {
        //
    }

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password'       => $this->passwordRules(),
            'clinica_option' => ['required', 'string', 'in:create,join'],
            'clinica_name'   => ['required_if:clinica_option,create', 'nullable', 'string', 'max:255'],
            'contact_email'  => ['nullable', 'email', 'max:255'],
            'contact_phone'  => ['nullable', 'string', 'max:255'],
            'address'        => ['nullable', 'string', 'max:500'],
            'clinica_code'   => [
                'required_if:clinica_option,join',
                'nullable',
                'string',
            ],
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name'     => $input['name'],
                'email'    => $input['email'],
                'password' => $input['password'],
            ]);

            if ($input['clinica_option'] === 'create') {
                $this->createClinica->handle($user, [
                    'name'          => $input['clinica_name'],
                    'contact_email' => $input['contact_email'] ?? null,
                    'contact_phone' => $input['contact_phone'] ?? null,
                    'address'       => $input['address'] ?? null,
                ]);
            } else {
                $clinica = $this->resolveClinicaFromCode($input['clinica_code']);

                $clinica->memberships()->create([
                    'user_id' => $user->id,
                    'role'    => ClinicaRole::Member,
                ]);

                $user->switchClinica($clinica);
            }

            return $user;
        });
    }

    /**
     * Resolve a Clinica from the provided code.
     *
     * Supports both invitation codes and direct public_id (UUID).
     */
    private function resolveClinicaFromCode(string $code): Clinica
    {
        // First, try to find an active invitation
        $invitation = \App\Models\ClinicaInvitation::where('code', $code)
            ->whereNull('accepted_at')
            ->where(function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();

        if ($invitation) {
            $invitation->update(['accepted_at' => now()]);

            return $invitation->clinica;
        }

        // Fall back to direct public_id lookup
        return Clinica::where('public_id', $code)->firstOrFail();
    }
}
