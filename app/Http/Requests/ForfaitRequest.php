<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForfaitRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'prix' => ['required', 'integer', 'min:0', 'max:10000000'],
            'services' => ['required', 'array', 'min:2'],
            'services.*' => ['integer', 'exists:services,id'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return ['services.min' => 'Un forfait regroupe au moins deux prestations.'];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
