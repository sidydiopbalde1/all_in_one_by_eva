<?php

namespace App\Http\Requests;

use App\Enums\ZoneSalon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class ServiceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'categorie_service_id' => ['required', 'exists:categories_services,id'],
            'nom' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'prix' => ['required', 'integer', 'min:0', 'max:10000000'],
            'duree_minutes' => ['required', 'integer', 'min:5', 'max:600'],
            'zone' => ['required', new Enum(ZoneSalon::class)],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
