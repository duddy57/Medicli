<?php

declare(strict_types = 1);

namespace App\Http\Requests\Clinicas;

use App\Enums\ClinicaRole;
use App\Rules\UniqueClinicaInvitation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateClinicaInvitationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', new UniqueClinicaInvitation($this->route('clinica'))],
            'role'  => ['required', 'string', Rule::enum(ClinicaRole::class)],
        ];
    }
}
