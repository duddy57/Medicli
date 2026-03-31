<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\Clinica;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Clinica>
 */
class ClinicaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->company();

        return [
            'name'          => $name,
            'slug'          => Str::slug($name),
            'owner_id'      => User::factory(),
            'plan'          => $this->faker->randomElement(['FREE', 'PRO', 'ENTERPRISE']),
            'is_personal'   => false,
            'contact_email' => fake()->email(),
            'contact_phone' => fake()->phoneNumber(),
            'address'       => fake()->address(),
            'logo'          => fake()->imageUrl(),
        ];
    }

    /**
     * Indicate that the Clinica is a personal Clinica.
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_personal' => true,
        ]);
    }

    /**
     * Indicate that the Clinica has been deleted.
     */
    public function trashed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'deleted_at' => now(),
        ]);
    }
}
