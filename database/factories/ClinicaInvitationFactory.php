<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Enums\ClinicaRole;
use App\Models\Clinica;
use App\Models\ClinicaInvitation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClinicaInvitation>
 */
class ClinicaInvitationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'Clinica_id'  => Clinica::factory(),
            'email'       => fake()->unique()->safeEmail(),
            'role'        => ClinicaRole::Member,
            'invited_by'  => User::factory(),
            'expires_at'  => null,
            'accepted_at' => null,
        ];
    }

    /**
     * Indicate that the invitation has been accepted.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes): array => [
            'accepted_at' => now(),
        ]);
    }

    /**
     * Indicate that the invitation has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the invitation expires in the given time.
     */
    public function expiresIn(int $value, string $unit = 'days'): static
    {
        return $this->state(fn (array $attributes): array => [
            'expires_at' => now()->add($unit, $value),
        ]);
    }
}
