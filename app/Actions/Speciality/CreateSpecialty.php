<?php

declare(strict_types = 1);

namespace App\Actions\Speciality;

use App\Models\Clinica;
use App\Models\Specialty;

class CreateSpecialty
{
    /**
     * Create a new class instance.
     */
    public function handle(Specialty $speciality, Clinica $clinica, array | string $data, bool $isPersonal = false): Specialty
    {
        if (is_string($data)) {
            $data = ['title' => $data];
        };

        $speciality = Specialty::create([
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'clinica_id'  => $clinica->id,
        ]);

        return $speciality;
    }
}
