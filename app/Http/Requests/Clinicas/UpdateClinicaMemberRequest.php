<?php

declare(strict_types = 1);

namespace App\Http\Requests\Clinicas;

use App\Enums\ClinicaRole;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClinicaMemberRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', 'string', Rule::in(array_column(ClinicaRole::assignable(), 'value'))],
        ];
    }
}
