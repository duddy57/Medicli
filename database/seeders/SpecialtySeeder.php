<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Clinica;
use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Clinica::all()->each(function (Clinica $clinica): void {
            Specialty::factory()
                ->count(5)
                ->create([
                    'clinica_id' => $clinica->id,

                ]);
        });
    }
}
