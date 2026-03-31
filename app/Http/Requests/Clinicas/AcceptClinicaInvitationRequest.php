<?php

declare(strict_types = 1);

namespace App\Http\Requests\Clinicas;

use App\Rules\ValidClinicaInvitation;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AcceptClinicaInvitationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invitation' => ['required', new ValidClinicaInvitation($this->user())],
        ];
    }

    /**
     * Get the validation data from the request.
     */
    #[\Override]
    public function validationData(): array
    {
        return array_merge(parent::validationData(), [
            'invitation' => $this->route('invitation'),
        ]);
    }
}
