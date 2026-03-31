<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\Clinica;
use App\Models\Specialty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Specialty>
 */
class SpecialtyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $fillable = ['title', 'description', 'clinica_id'];

    public function definition(): array
    {
        $specialities = [
            "Cardiología",
            "Dermatología",
            "Pediatría",
            "Neurología",
            "Traumatología",
            "Oftalmología",
            "Otorrinolaringología",
            "Ginecología",
            "Urología",
            "Psicología",
            "Psiquiatría",
            "Neurología",
            "Traumatología",
            "Oftalmología",
            "Otorrinolaringología",
            "Ginecología",
            "Urología",
            "Psicología",
            "Psiquiatría",
        ];

        return [
            'title'       => fake()->randomElement($specialities),
            'description' => fake()->text(),
            'clinica_id'  => Clinica::factory(),
        ];
    }
}
