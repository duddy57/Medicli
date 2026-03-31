<?php

declare(strict_types = 1);

namespace Database\Seeders;

use App\Models\Clinica;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClinicaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::all()->each(function (User $user): void {
            Clinica::factory()
                ->count(5)
                ->create([
                    'owner_id' => $user->id,
                ]);
        });
    }
}
